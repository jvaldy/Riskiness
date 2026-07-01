<?php

namespace App\Controller;

use App\Config\RecipeAtelierConfig;
use App\Entity\AtelierItem;
use App\Entity\User;
use App\Repository\AtelierItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeAtelierController extends AbstractController
{
    #[Route('/recipe-atelier', name: 'app_recipe_atelier', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser() instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('recipe_atelier/index.html.twig', [
            'atelierConfig' => RecipeAtelierConfig::payload(),
        ]);
    }

    #[Route('/recipe-atelier/items', name: 'app_recipe_atelier_items', methods: ['GET'])]
    public function items(AtelierItemRepository $items): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'config' => RecipeAtelierConfig::payload(),
            'items' => array_map(
                fn (AtelierItem $item) => $this->serializeItem($item),
                $items->findByUserOrdered($user)
            ),
        ]);
    }

    #[Route('/recipe-atelier/items/{id}', name: 'app_recipe_atelier_item_get', methods: ['GET'])]
    public function item(int $id, AtelierItemRepository $items): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $item = $items->findOneByUserAndId($user, $id);
        if (!$item instanceof AtelierItem) {
            return $this->json(['error' => 'Fiche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['item' => $this->serializeItem($item)]);
    }

    #[Route('/recipe-atelier/items/{id}/pdf', name: 'app_recipe_atelier_item_pdf', methods: ['GET'])]
    public function pdf(int $id, AtelierItemRepository $items): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $item = $items->findOneByUserAndId($user, $id);
        if (!$item instanceof AtelierItem) {
            throw $this->createNotFoundException('Fiche introuvable.');
        }

        $filename = $this->pdfFilename($item);
        $pdf = $this->buildRecipePdf($item);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            'Content-Length' => (string) strlen($pdf),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    #[Route('/recipe-atelier/items', name: 'app_recipe_atelier_item_create', methods: ['POST'])]
    #[Route('/recipe-atelier/items/{id}', name: 'app_recipe_atelier_item_update', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $entityManager,
        AtelierItemRepository $items,
        ?int $id = null
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('recipe_atelier_item', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $itemId = $id ?? (int) ($payload['id'] ?? 0);
        $item = $itemId > 0 ? $items->findOneByUserAndId($user, $itemId) : null;
        if ($itemId > 0 && !$item instanceof AtelierItem) {
            return $this->json(['error' => 'Fiche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $title = trim((string) ($payload['title'] ?? ''));
        if ('' === $title) {
            return $this->json(['error' => 'Le titre est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item ??= new AtelierItem($user);
        $type = $this->allowedKey((string) ($payload['type'] ?? 'recipe'), RecipeAtelierConfig::TYPES, 'recipe');
        $category = $this->allowedValue((string) ($payload['category'] ?? 'Cuisine'), RecipeAtelierConfig::CATEGORIES, 'Cuisine');
        $difficulty = $this->allowedKey((string) ($payload['difficulty'] ?? 'easy'), RecipeAtelierConfig::DIFFICULTIES, 'easy');
        $status = $this->allowedKey((string) ($payload['status'] ?? 'draft'), RecipeAtelierConfig::STATUSES, 'draft');

        $item
            ->setTitle($title)
            ->setType($type)
            ->setCategory($category)
            ->setTags((string) ($payload['tags'] ?? ''))
            ->setEstimatedDuration($this->nullablePositiveInt($payload['estimatedDuration'] ?? null))
            ->setTargetQuantity((string) ($payload['targetQuantity'] ?? ''))
            ->setDifficulty($difficulty)
            ->setStatus($status)
            ->setNotes((string) ($payload['notes'] ?? ''))
            ->setIsFavorite(true === ($payload['isFavorite'] ?? false))
            ->setResources($this->normalizeResources($payload['resources'] ?? []))
            ->setSteps($this->normalizeSteps($payload['steps'] ?? []))
            ->setVariants([]);

        $entityManager->persist($item);
        $entityManager->flush();

        return $this->json(['item' => $this->serializeItem($item)]);
    }

    #[Route('/recipe-atelier/items/{id}', name: 'app_recipe_atelier_item_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        AtelierItemRepository $items
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('recipe_atelier_item', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $item = $items->findOneByUserAndId($user, $id);
        if (!$item instanceof AtelierItem) {
            return $this->json(['error' => 'Fiche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($item);
        $entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    #[Route('/recipe-atelier/items/{id}/favorite', name: 'app_recipe_atelier_item_favorite', methods: ['POST'])]
    public function favorite(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        AtelierItemRepository $items
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('recipe_atelier_item', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $item = $items->findOneByUserAndId($user, $id);
        if (!$item instanceof AtelierItem) {
            return $this->json(['error' => 'Fiche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $item->setIsFavorite(!$item->isFavorite());
        $entityManager->flush();

        return $this->json(['item' => $this->serializeItem($item)]);
    }

    #[Route('/recipe-atelier/items/{id}/duplicate', name: 'app_recipe_atelier_item_duplicate', methods: ['POST'])]
    public function duplicate(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        AtelierItemRepository $items
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('recipe_atelier_item', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $source = $items->findOneByUserAndId($user, $id);
        if (!$source instanceof AtelierItem) {
            return $this->json(['error' => 'Fiche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $copy = new AtelierItem($user);
        $copy
            ->setTitle($source->getTitle() . ' - copie')
            ->setType($source->getType())
            ->setCategory($source->getCategory())
            ->setTags($source->getTags() ?? '')
            ->setEstimatedDuration($source->getEstimatedDuration())
            ->setTargetQuantity($source->getTargetQuantity() ?? '')
            ->setDifficulty($source->getDifficulty())
            ->setStatus('draft')
            ->setNotes($source->getNotes() ?? '')
            ->setIsFavorite(false)
            ->setResources($source->getResources())
            ->setSteps(array_map(fn (array $step): array => [...$step, 'isDone' => false], $source->getSteps()))
            ->setVariants([]);

        $entityManager->persist($copy);
        $entityManager->flush();

        return $this->json(['item' => $this->serializeItem($copy)]);
    }

    /**
     * @param array<string, string> $allowed
     */
    private function allowedKey(string $value, array $allowed, string $fallback): string
    {
        return array_key_exists($value, $allowed) ? $value : $fallback;
    }

    /**
     * @param string[] $allowed
     */
    private function allowedValue(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        $value = trim((string) $value);
        if ('' === $value) {
            return null;
        }

        return max(0, (int) $value);
    }

    private function cleanText(mixed $value, int $limit): string
    {
        return mb_substr(trim((string) $value), 0, $limit);
    }

    private function pdfFilename(AtelierItem $item): string
    {
        $slug = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $item->getTitle()) ?: 'fiche');
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: 'fiche';
        $slug = trim($slug, '-');

        return sprintf('riskiness-recette-%s.pdf', '' !== $slug ? $slug : 'fiche');
    }

    private function buildRecipePdf(AtelierItem $item): string
    {
        $config = RecipeAtelierConfig::payload();
        $typeLabel = $config['types'][$item->getType()] ?? $item->getType();
        $difficultyLabel = $config['difficulties'][$item->getDifficulty()] ?? $item->getDifficulty();
        $statusLabel = $config['statuses'][$item->getStatus()] ?? $item->getStatus();

        $pages = [];
        $ops = [];
        $y = 792;

        $newPage = function () use (&$pages, &$ops, &$y): void {
            if ([] !== $ops) {
                $pages[] = implode("\n", $ops);
            }

            $ops = [];
            $y = 792;
            $ops[] = '1 1 1 rg 0 0 595 842 re f';
        };

        $text = function (float $x, float $lineY, string $value, int $size = 10, string $font = 'F1', array $color = [0.14, 0.11, 0.18]) use (&$ops): void {
            $ops[] = sprintf(
                'BT %.3F %.3F %.3F rg /%s %d Tf %.2F %.2F Td (%s) Tj ET',
                $color[0],
                $color[1],
                $color[2],
                $font,
                $size,
                $x,
                $lineY,
                $this->pdfEscape($value)
            );
        };

        $line = function (float $x1, float $y1, float $x2, float $y2, array $color = [0.91, 0.89, 0.93]) use (&$ops): void {
            $ops[] = sprintf('%.3F %.3F %.3F RG %.2F %.2F m %.2F %.2F l S', $color[0], $color[1], $color[2], $x1, $y1, $x2, $y2);
        };

        $section = function (string $title) use (&$y, $newPage, $text, $line): void {
            if ($y < 115) {
                $newPage();
            }

            $y -= 26;
            $text(48, $y, $title, 14, 'F2');
            $line(48, $y - 8, 547, $y - 8);
            $y -= 25;
        };

        $paragraph = function (string $value, int $fontSize = 10, int $maxChars = 92, string $prefix = '') use (&$y, $newPage, $text): void {
            $lines = $this->wrapPdfText(('' !== $prefix ? $prefix . $value : $value), $maxChars);
            foreach ($lines as $lineText) {
                if ($y < 56) {
                    $newPage();
                }

                $text(58, $y, $lineText, $fontSize);
                $y -= 15;
            }
        };

        $newPage();

        $text(48, $y, 'RISKINESS RECIPE ATELIER', 10, 'F2', [0.37, 0.34, 0.41]);
        $text(438, $y, 'Export ' . (new \DateTimeImmutable())->format('d/m/Y H:i'), 8, 'F1', [0.56, 0.53, 0.59]);
        $y -= 44;

        foreach ($this->wrapPdfText($item->getTitle(), 35) as $index => $titleLine) {
            $text(48, $y, $titleLine, 26, 'F2', 0 === $index ? [0.96, 0.14, 0.37] : [0.14, 0.11, 0.18]);
            $y -= 31;
        }

        $meta = array_filter([
            $typeLabel,
            $item->getCategory(),
            $difficultyLabel,
            $statusLabel,
            $item->getEstimatedDuration() ? $item->getEstimatedDuration() . ' min' : '',
            $item->getTargetQuantity() ?: '',
            $item->getTags() ?: '',
        ]);
        $paragraph(implode('  |  ', $meta), 9, 100);

        $section('Ressources');
        if ([] === $item->getResources()) {
            $paragraph('Aucune ressource renseignee.', 10);
        } else {
            foreach ($item->getResources() as $resource) {
                $quantity = trim((string) ($resource['quantity'] ?? '') . ' ' . (string) ($resource['unit'] ?? ''));
                $type = isset($resource['resourceType']) ? ($config['resourceTypes'][$resource['resourceType']] ?? $resource['resourceType']) : '';
                $optional = true === ($resource['isOptional'] ?? false) ? ' optionnel' : '';
                $details = trim(implode(' - ', array_filter([$quantity, $type, $optional])));
                $paragraph(sprintf('- %s%s', (string) ($resource['name'] ?? 'Ressource'), '' !== $details ? ' : ' . $details : ''), 10);
            }
        }

        $section('Etapes');
        if ([] === $item->getSteps()) {
            $paragraph('Aucune etape renseignee.', 10);
        } else {
            foreach ($item->getSteps() as $index => $step) {
                $details = trim(implode(' - ', array_filter([(string) ($step['duration'] ?? ''), (string) ($step['condition'] ?? '')])));
                $paragraph(sprintf('%d. %s%s', $index + 1, (string) ($step['action'] ?? 'Etape'), '' !== $details ? ' (' . $details . ')' : ''), 10);
                if ('' !== trim((string) ($step['note'] ?? ''))) {
                    $paragraph('Note: ' . (string) $step['note'], 9, 96, '   ');
                }
            }
        }

        $section('Notes');
        $paragraph($item->getNotes() ?: 'Aucune note renseignee.', 10, 94);

        if ([] !== $ops) {
            $pages[] = implode("\n", $ops);
        }

        return $this->renderPdfDocument($pages);
    }

    /**
     * @return string[]
     */
    private function wrapPdfText(string $value, int $maxChars): array
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';
        if ('' === $value) {
            return [''];
        }

        $words = explode(' ', $value);
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = '' === $line ? $word : $line . ' ' . $word;
            if (mb_strlen($candidate) > $maxChars && '' !== $line) {
                $lines[] = $line;
                $line = $word;
                continue;
            }

            $line = $candidate;
        }

        if ('' !== $line) {
            $lines[] = $line;
        }

        return $lines;
    }

    private function pdfEscape(string $value): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        $encoded = false === $encoded ? $value : $encoded;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
    }

    /**
     * @param string[] $pages
     */
    private function renderPdfDocument(array $pages): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];

        $kids = [];
        foreach ($pages as $index => $content) {
            $pageObject = 5 + ($index * 2);
            $contentObject = $pageObject + 1;
            $kids[] = $pageObject . ' 0 R';
            $objects[$contentObject] = sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($content), $content);
            $objects[$pageObject] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                $contentObject
            );
        }

        $objects[2] = sprintf('<< /Type /Pages /Kids [%s] /Count %d >>', implode(' ', $kids), count($pages));
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); ++$i) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    /**
     * @return array<int, array{name: string, quantity: string, unit: string, resourceType: string, isOptional: bool, sortOrder: int}>
     */
    private function normalizeResources(mixed $resources): array
    {
        if (!is_array($resources)) {
            return [];
        }

        $normalized = [];
        foreach ($resources as $index => $resource) {
            if (!is_array($resource)) {
                continue;
            }

            $name = $this->cleanText($resource['name'] ?? '', 120);
            if ('' === $name) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'quantity' => $this->cleanText($resource['quantity'] ?? '', 40),
                'unit' => $this->allowedValue((string) ($resource['unit'] ?? 'unite'), RecipeAtelierConfig::UNITS, 'unite'),
                'resourceType' => $this->allowedKey((string) ($resource['resourceType'] ?? 'ingredient'), RecipeAtelierConfig::RESOURCE_TYPES, 'ingredient'),
                'isOptional' => true === ($resource['isOptional'] ?? false),
                'sortOrder' => (int) ($resource['sortOrder'] ?? $index),
            ];
        }

        usort($normalized, fn (array $a, array $b): int => $a['sortOrder'] <=> $b['sortOrder']);

        return array_values($normalized);
    }

    /**
     * @return array<int, array{action: string, duration: string, condition: string, note: string, isDone: bool, sortOrder: int}>
     */
    private function normalizeSteps(mixed $steps): array
    {
        if (!is_array($steps)) {
            return [];
        }

        $normalized = [];
        foreach ($steps as $index => $step) {
            if (!is_array($step)) {
                continue;
            }

            $action = $this->cleanText($step['action'] ?? '', 240);
            if ('' === $action) {
                continue;
            }

            $normalized[] = [
                'action' => $action,
                'duration' => $this->cleanText($step['duration'] ?? '', 40),
                'condition' => $this->cleanText($step['condition'] ?? '', 160),
                'note' => $this->cleanText($step['note'] ?? '', 220),
                'isDone' => true === ($step['isDone'] ?? false),
                'sortOrder' => (int) ($step['sortOrder'] ?? $index),
            ];
        }

        usort($normalized, fn (array $a, array $b): int => $a['sortOrder'] <=> $b['sortOrder']);

        return array_values($normalized);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeItem(AtelierItem $item): array
    {
        return [
            'id' => $item->getId(),
            'title' => $item->getTitle(),
            'type' => $item->getType(),
            'category' => $item->getCategory(),
            'tags' => $item->getTags() ?? '',
            'estimatedDuration' => $item->getEstimatedDuration(),
            'targetQuantity' => $item->getTargetQuantity() ?? '',
            'difficulty' => $item->getDifficulty(),
            'status' => $item->getStatus(),
            'notes' => $item->getNotes() ?? '',
            'isFavorite' => $item->isFavorite(),
            'resources' => $item->getResources(),
            'steps' => $item->getSteps(),
            'createdAt' => $item->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $item->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

}
