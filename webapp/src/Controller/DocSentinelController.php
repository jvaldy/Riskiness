<?php

namespace App\Controller;

use App\Entity\DocSentinelDocument;
use App\Entity\User;
use App\Repository\DocSentinelDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocSentinelController extends AbstractController
{
    private const CATEGORIES = [
        'identity' => 'Identite',
        'health' => 'Sante',
        'insurance' => 'Assurance',
        'housing' => 'Logement',
        'work' => 'Travail',
        'vehicle' => 'Vehicule',
        'bank' => 'Banque',
        'contract' => 'Contrat',
        'warranty' => 'Garantie',
        'other' => 'Autre',
    ];

    private const STATUS_LABELS = [
        'current' => 'A jour',
        'soon' => 'Bientot expire',
        'expired' => 'Expire',
        'renew' => 'A renouveler',
        'archived' => 'Archive',
    ];

    private const SOON_DAYS = 30;

    #[Route('/doc-sentinel', name: 'app_doc_sentinel', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser() instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('doc_sentinel/index.html.twig', [
            'categories' => self::CATEGORIES,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    #[Route('/doc-sentinel/documents', name: 'app_doc_sentinel_documents', methods: ['GET'])]
    public function documents(DocSentinelDocumentRepository $documents): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $items = array_map(
            fn (DocSentinelDocument $document): array => $this->serializeDocument($document),
            $documents->findByUserOrdered($user)
        );

        return $this->json([
            'documents' => $items,
            'summary' => $this->summary($items),
        ]);
    }

    #[Route('/doc-sentinel/documents', name: 'app_doc_sentinel_document_save', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $entityManager,
        DocSentinelDocumentRepository $documents
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('doc_sentinel_document', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $name = $this->cleanText($payload['name'] ?? '', 180);
        if ('' === $name) {
            return $this->json(['error' => 'Le nom est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $expirationDate = $this->parseDate((string) ($payload['expirationDate'] ?? ''));
        if (!$expirationDate instanceof \DateTimeImmutable) {
            return $this->json(['error' => 'La date d expiration est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $documentId = (int) ($payload['id'] ?? 0);
        $document = $documentId > 0 ? $documents->findOneByUserAndId($user, $documentId) : null;
        if ($documentId > 0 && !$document instanceof DocSentinelDocument) {
            return $this->json(['error' => 'Document introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $document ??= new DocSentinelDocument($user);
        $document
            ->setName($name)
            ->setCategory($this->allowedCategory((string) ($payload['category'] ?? 'other')))
            ->setExpirationDate($expirationDate)
            ->setReminderDate($this->parseDate((string) ($payload['reminderDate'] ?? '')))
            ->setNote($this->cleanText($payload['note'] ?? '', 1200))
            ->setArchived(true === ($payload['archived'] ?? false));

        $entityManager->persist($document);
        $entityManager->flush();

        return $this->json(['document' => $this->serializeDocument($document)]);
    }

    #[Route('/doc-sentinel/documents/{id}', name: 'app_doc_sentinel_document_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DocSentinelDocumentRepository $documents
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('doc_sentinel_document', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $document = $documents->findOneByUserAndId($user, $id);
        if (!$document instanceof DocSentinelDocument) {
            return $this->json(['error' => 'Document introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($document);
        $entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    #[Route('/doc-sentinel/documents/{id}/archive', name: 'app_doc_sentinel_document_archive', methods: ['POST'])]
    public function archive(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DocSentinelDocumentRepository $documents
    ): JsonResponse {
        $document = $this->guardedDocument($id, $request, $documents);
        if ($document instanceof JsonResponse) {
            return $document;
        }

        $document->setArchived(!$document->isArchived());
        $entityManager->flush();

        return $this->json(['document' => $this->serializeDocument($document)]);
    }

    #[Route('/doc-sentinel/documents/{id}/renew', name: 'app_doc_sentinel_document_renew', methods: ['POST'])]
    public function renew(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DocSentinelDocumentRepository $documents
    ): JsonResponse {
        $document = $this->guardedDocument($id, $request, $documents);
        if ($document instanceof JsonResponse) {
            return $document;
        }

        $payload = json_decode($request->getContent(), true);
        $expirationDate = is_array($payload) ? $this->parseDate((string) ($payload['expirationDate'] ?? '')) : null;
        $reminderDate = is_array($payload) ? $this->parseDate((string) ($payload['reminderDate'] ?? '')) : null;
        $document
            ->setExpirationDate($expirationDate ?? new \DateTimeImmutable('+1 year'))
            ->setReminderDate($reminderDate)
            ->setArchived(false);

        $entityManager->flush();

        return $this->json(['document' => $this->serializeDocument($document)]);
    }

    private function guardedDocument(
        int $id,
        Request $request,
        DocSentinelDocumentRepository $documents
    ): DocSentinelDocument|JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('doc_sentinel_document', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $document = $documents->findOneByUserAndId($user, $id);
        if (!$document instanceof DocSentinelDocument) {
            return $this->json(['error' => 'Document introuvable.'], Response::HTTP_NOT_FOUND);
        }

        return $document;
    }

    private function allowedCategory(string $value): string
    {
        return array_key_exists($value, self::CATEGORIES) ? $value : 'other';
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

    private function cleanText(mixed $value, int $limit): string
    {
        return mb_substr(trim((string) $value), 0, $limit);
    }

    private function statusFor(DocSentinelDocument $document): string
    {
        if ($document->isArchived()) {
            return 'archived';
        }

        $today = new \DateTimeImmutable('today');
        $expirationDate = $document->getExpirationDate();
        if ($expirationDate < $today) {
            return 'expired';
        }

        $daysBeforeExpiration = (int) $today->diff($expirationDate)->format('%r%a');
        if ($daysBeforeExpiration <= self::SOON_DAYS) {
            return 'soon';
        }

        $reminderDate = $document->getReminderDate();
        if ($reminderDate instanceof \DateTimeImmutable && $reminderDate <= $today) {
            return 'renew';
        }

        return 'current';
    }

    /**
     * @param array<int, array<string, mixed>> $documents
     * @return array<string, int>
     */
    private function summary(array $documents): array
    {
        $summary = [
            'total' => 0,
            'current' => 0,
            'soon' => 0,
            'expired' => 0,
            'renew' => 0,
            'archived' => 0,
        ];

        foreach ($documents as $document) {
            $status = (string) $document['status'];
            ++$summary['total'];
            if (isset($summary[$status])) {
                ++$summary[$status];
            }
        }

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDocument(DocSentinelDocument $document): array
    {
        $status = $this->statusFor($document);

        return [
            'id' => $document->getId(),
            'name' => $document->getName(),
            'category' => $document->getCategory(),
            'categoryLabel' => self::CATEGORIES[$document->getCategory()] ?? self::CATEGORIES['other'],
            'expirationDate' => $document->getExpirationDate()->format('Y-m-d'),
            'reminderDate' => $document->getReminderDate()?->format('Y-m-d') ?? '',
            'status' => $status,
            'statusLabel' => self::STATUS_LABELS[$status],
            'note' => $document->getNote() ?? '',
            'archived' => $document->isArchived(),
            'createdAt' => $document->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $document->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
