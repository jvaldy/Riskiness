<?php

namespace App\Controller;

use App\Entity\MovieTrackerEntry;
use App\Entity\User;
use App\Repository\MovieTrackerEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MovieTrackerController extends AbstractController
{
    private const TYPES = ['film', 'series', 'book', 'anime', 'video'];
    private const STATES = ['planned', 'progress', 'paused', 'waiting', 'done', 'dropped'];
    private const ANIME_FORMATS = ['series', 'movie'];

    #[Route('/movie-tracker', name: 'app_movie_tracker', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('movie_tracker/index.html.twig');
    }

    #[Route('/movie-tracker/entries', name: 'app_movie_tracker_entries', methods: ['GET'])]
    public function entries(MovieTrackerEntryRepository $entries): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'entries' => array_map(
                fn (MovieTrackerEntry $entry) => $this->serializeEntry($entry),
                $entries->findByUserOrdered($user)
            ),
        ]);
    }

    #[Route('/movie-tracker/entries', name: 'app_movie_tracker_entry_save', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $entityManager,
        MovieTrackerEntryRepository $entries
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('movie_tracker_entry', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $title = trim((string) ($payload['title'] ?? ''));
        if ('' === $title) {
            return $this->json(['error' => 'Le titre est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entryId = (int) ($payload['id'] ?? 0);
        $entry = $entryId > 0 ? $entries->findOneByUserAndId($user, $entryId) : null;
        if ($entryId > 0 && !$entry instanceof MovieTrackerEntry) {
            return $this->json(['error' => 'Entree introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entry ??= new MovieTrackerEntry($user);
        $type = $this->allowedValue((string) ($payload['type'] ?? 'film'), self::TYPES, 'film');
        $state = $this->allowedValue((string) ($payload['state'] ?? 'planned'), self::STATES, 'planned');
        $animeFormat = $this->allowedValue((string) ($payload['animeFormat'] ?? 'series'), self::ANIME_FORMATS, 'series');
        $isSeriesLike = 'series' === $type || ('anime' === $type && 'series' === $animeFormat);

        $entry
            ->setType($type)
            ->setTitle($title)
            ->setState($state)
            ->setWaitingDate('waiting' === $state ? $this->parseDate((string) ($payload['waitingDate'] ?? '')) : null)
            ->setAnimeFormat('anime' === $type ? $animeFormat : null)
            ->setSeason($isSeriesLike ? $this->nullableInt($payload['season'] ?? null) : null)
            ->setEpisode($isSeriesLike ? $this->nullableInt($payload['episode'] ?? null) : null)
            ->setTimeCode($this->supportsTimeCode($type, $animeFormat) ? (string) ($payload['timeCode'] ?? '') : null)
            ->setSource('video' === $type ? (string) ($payload['source'] ?? '') : null)
            ->setBookPoint('book' === $type ? (string) ($payload['bookPoint'] ?? '') : null)
            ->setNotes((string) ($payload['notes'] ?? ''))
            ->setTags((string) ($payload['tags'] ?? ''));

        $entityManager->persist($entry);
        $entityManager->flush();

        return $this->json(['entry' => $this->serializeEntry($entry)]);
    }

    #[Route('/movie-tracker/entries/{id}', name: 'app_movie_tracker_entry_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MovieTrackerEntryRepository $entries
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('movie_tracker_entry', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $entry = $entries->findOneByUserAndId($user, $id);
        if (!$entry instanceof MovieTrackerEntry) {
            return $this->json(['error' => 'Entree introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($entry);
        $entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    /**
     * @param string[] $allowed
     */
    private function allowedValue(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);
        if ('' === $value) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof \DateTimeImmutable ? $date : null;
    }

    private function nullableInt(mixed $value): ?int
    {
        if (null === $value || '' === trim((string) $value)) {
            return null;
        }

        return max(0, (int) $value);
    }

    private function supportsTimeCode(string $type, string $animeFormat): bool
    {
        return 'film' === $type || 'series' === $type || 'anime' === $type || 'video' === $type;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEntry(MovieTrackerEntry $entry): array
    {
        return [
            'id' => $entry->getId(),
            'type' => $entry->getType(),
            'title' => $entry->getTitle(),
            'state' => $entry->getState(),
            'waitingDate' => $entry->getWaitingDate()?->format('Y-m-d') ?? '',
            'animeFormat' => $entry->getAnimeFormat() ?? 'series',
            'season' => null !== $entry->getSeason() ? (string) $entry->getSeason() : '',
            'episode' => null !== $entry->getEpisode() ? (string) $entry->getEpisode() : '',
            'timeCode' => $entry->getTimeCode() ?? '',
            'source' => $entry->getSource() ?? '',
            'bookPoint' => $entry->getBookPoint() ?? '',
            'notes' => $entry->getNotes() ?? '',
            'tags' => $entry->getTags() ?? '',
            'createdAt' => $entry->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $entry->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
