<?php

namespace App\Controller;

use App\Config\BudgetPulseCategories;
use App\Entity\BudgetTransaction;
use App\Entity\User;
use App\Repository\BudgetTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BudgetPulseController extends AbstractController
{
    private const TYPES = ['income', 'expense'];
    private const FREQUENCIES = ['monthly', 'weekly', 'yearly'];

    #[Route('/budget-pulse', name: 'app_budget_pulse', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('budget_pulse/index.html.twig');
    }

    #[Route('/budget-pulse/categories', name: 'app_budget_pulse_categories', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        return $this->json(['categories' => BudgetPulseCategories::all()]);
    }

    #[Route('/budget-pulse/transactions', name: 'app_budget_pulse_transactions', methods: ['GET'])]
    public function transactions(BudgetTransactionRepository $transactions): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'categories' => BudgetPulseCategories::all(),
            'transactions' => array_map(
                fn (BudgetTransaction $transaction) => $this->serializeTransaction($transaction),
                $transactions->findByUserOrdered($user)
            ),
        ]);
    }

    #[Route('/budget-pulse/transactions', name: 'app_budget_pulse_transaction_save', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $entityManager,
        BudgetTransactionRepository $transactions
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('budget_pulse_transaction', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $type = $this->allowedValue((string) ($payload['type'] ?? 'expense'), self::TYPES, 'expense');
        $amount = (float) str_replace(',', '.', (string) ($payload['amount'] ?? '0'));
        $label = trim((string) ($payload['label'] ?? ''));
        $date = $this->parseDate((string) ($payload['occurredAt'] ?? ''));
        $category = trim((string) ($payload['category'] ?? BudgetPulseCategories::defaultForType($type)));
        $isRecurring = true === ($payload['isRecurring'] ?? false);
        $frequency = $this->allowedValue((string) ($payload['recurrenceFrequency'] ?? 'monthly'), self::FREQUENCIES, 'monthly');

        if ($amount <= 0) {
            return $this->json(['error' => 'Le montant doit etre superieur a 0.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ('' === $label) {
            return $this->json(['error' => 'Le libelle est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$date instanceof \DateTimeImmutable) {
            return $this->json(['error' => 'La date est invalide.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!BudgetPulseCategories::exists($category)) {
            $category = BudgetPulseCategories::defaultForType($type);
        }

        $transactionId = (int) ($payload['id'] ?? 0);
        $transaction = $transactionId > 0 ? $transactions->findOneByUserAndId($user, $transactionId) : null;
        if ($transactionId > 0 && !$transaction instanceof BudgetTransaction) {
            return $this->json(['error' => 'Transaction introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $transaction ??= new BudgetTransaction($user);
        $transaction
            ->setType($type)
            ->setAmount((string) $amount)
            ->setCategory($category)
            ->setLabel($label)
            ->setNote((string) ($payload['note'] ?? ''))
            ->setOccurredAt($date)
            ->setIsRecurring($isRecurring)
            ->setRecurrenceFrequency($isRecurring ? $frequency : null);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->json(['transaction' => $this->serializeTransaction($transaction)]);
    }

    #[Route('/budget-pulse/transactions/{id}', name: 'app_budget_pulse_transaction_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        BudgetTransactionRepository $transactions
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('budget_pulse_transaction', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $transaction = $transactions->findOneByUserAndId($user, $id);
        if (!$transaction instanceof BudgetTransaction) {
            return $this->json(['error' => 'Transaction introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($transaction);
        $entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    #[Route('/budget-pulse/export', name: 'app_budget_pulse_export', methods: ['GET'])]
    public function export(Request $request, BudgetTransactionRepository $transactions): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $items = array_map(
            fn (BudgetTransaction $transaction) => $this->serializeTransaction($transaction),
            $transactions->findByUserOrdered($user)
        );

        $exportedAt = (new \DateTimeImmutable())->format('Ymd-His');

        if ('csv' === strtolower((string) $request->query->get('format'))) {
            $csv = $this->buildCsv($items);

            return new Response($csv, Response::HTTP_OK, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => sprintf('attachment; filename="riskiness-budget-pulse-%s.csv"', $exportedAt),
            ]);
        }

        return $this->json([
            'transactions' => array_map(fn (array $item): array => $this->formatExportItem($item), $items),
        ], Response::HTTP_OK, [
            'Content-Disposition' => sprintf('attachment; filename="riskiness-budget-pulse-%s.json"', $exportedAt),
        ]);
    }

    #[Route('/budget-pulse/import', name: 'app_budget_pulse_import', methods: ['POST'])]
    public function import(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('budget_pulse_import', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Import invalide. Recharge la page puis reessaie.');

            return $this->redirectToRoute('app_budget_pulse');
        }

        $file = $request->files->get('import_file');
        if (null === $file || !method_exists($file, 'isValid') || !$file->isValid()) {
            $this->addFlash('error', 'Choisis un fichier JSON ou CSV valide.');

            return $this->redirectToRoute('app_budget_pulse');
        }

        $raw = (string) file_get_contents($file->getPathname());
        $items = $this->parseImport($raw, strtolower((string) $file->getClientOriginalExtension()));
        $created = 0;

        foreach ($items as $item) {
            $type = $this->normalizeImportType((string) $this->importValue($item, ['type']));
            $amount = (float) str_replace(',', '.', (string) $this->importValue($item, ['amount', 'montant']));
            $label = trim((string) $this->importValue($item, ['label', 'libelle']));
            $date = $this->parseDate((string) $this->importValue($item, ['occurredAt', 'date']));
            $category = trim((string) $this->importValue($item, ['category', 'categorie'], BudgetPulseCategories::defaultForType($type)));

            if ($amount <= 0 || '' === $label || !$date instanceof \DateTimeImmutable) {
                continue;
            }

            if (!BudgetPulseCategories::exists($category)) {
                $category = BudgetPulseCategories::defaultForType($type);
            }

            $transaction = new BudgetTransaction($user);
            $isRecurring = $this->normalizeImportBoolean($this->importValue($item, ['isRecurring', 'recurrent']));
            $transaction
                ->setType($type)
                ->setAmount((string) $amount)
                ->setCategory($category)
                ->setLabel($label)
                ->setNote((string) $this->importValue($item, ['note']))
                ->setOccurredAt($date)
                ->setIsRecurring($isRecurring)
                ->setRecurrenceFrequency($isRecurring ? $this->normalizeImportFrequency((string) $this->importValue($item, ['recurrenceFrequency', 'frequence'], 'monthly')) : null);

            $entityManager->persist($transaction);
            ++$created;
        }

        $entityManager->flush();
        $this->addFlash('success', sprintf('Import Budget Pulse termine: %d transaction(s) ajoutee(s).', $created));

        return $this->redirectToRoute('app_budget_pulse');
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

    /**
     * @param string[] $allowed
     */
    private function allowedValue(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTransaction(BudgetTransaction $transaction): array
    {
        return [
            'id' => $transaction->getId(),
            'type' => $transaction->getType(),
            'amount' => (float) $transaction->getAmount(),
            'category' => $transaction->getCategory(),
            'label' => $transaction->getLabel(),
            'note' => $transaction->getNote() ?? '',
            'occurredAt' => $transaction->getOccurredAt()->format('Y-m-d'),
            'isRecurring' => $transaction->isRecurring(),
            'recurrenceFrequency' => $transaction->getRecurrenceFrequency() ?? '',
            'createdAt' => $transaction->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $transaction->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function buildCsv(array $items): string
    {
        $handle = fopen('php://temp', 'r+');
        if (false === $handle) {
            return '';
        }

        fputs($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Type', 'Montant', 'Categorie', 'Libelle', 'Note', 'Date', 'Recurrent', 'Frequence'], ';');

        foreach ($items as $item) {
            $exportItem = $this->formatExportItem($item);
            fputcsv($handle, [
                $exportItem['type'],
                (string) $exportItem['montant'],
                $exportItem['categorie'],
                $exportItem['libelle'],
                $exportItem['note'],
                $exportItem['date'],
                $exportItem['recurrent'],
                $exportItem['frequence'],
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return false === $csv ? '' : $csv;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseImport(string $raw, string $extension): array
    {
        if ('csv' === $extension) {
            $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $raw) ?: []));
            if (count($lines) < 2) {
                return [];
            }

            $delimiter = str_contains($lines[0], ';') ? ';' : ',';
            $headers = array_map(fn (string $header): string => trim($header, "\xEF\xBB\xBF \t\n\r\0\x0B"), str_getcsv(array_shift($lines), $delimiter));
            $items = [];
            foreach ($lines as $line) {
                $values = str_getcsv($line, $delimiter);
                $item = [];
                foreach ($headers as $index => $header) {
                    $item[$this->normalizeImportKey($header)] = $values[$index] ?? '';
                }
                $items[] = $item;
            }

            return $items;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return isset($decoded['transactions']) && is_array($decoded['transactions']) ? $decoded['transactions'] : $decoded;
    }

    /**
     * @param array<string, mixed> $item
     * @return array{type: string, montant: float, categorie: string, libelle: string, note: string, date: string, recurrent: string, frequence: string}
     */
    private function formatExportItem(array $item): array
    {
        return [
            'type' => 'income' === $item['type'] ? 'Revenu' : 'Depense',
            'montant' => (float) $item['amount'],
            'categorie' => (string) $item['category'],
            'libelle' => (string) $item['label'],
            'note' => (string) $item['note'],
            'date' => (string) $item['occurredAt'],
            'recurrent' => $item['isRecurring'] ? 'Oui' : 'Non',
            'frequence' => $this->formatExportFrequency((string) $item['recurrenceFrequency']),
        ];
    }

    private function formatExportFrequency(string $frequency): string
    {
        return match ($frequency) {
            'weekly' => 'Hebdomadaire',
            'yearly' => 'Annuelle',
            'monthly' => 'Mensuelle',
            default => '',
        };
    }

    /**
     * @param array<string, mixed> $item
     * @param string[] $keys
     */
    private function importValue(array $item, array $keys, mixed $fallback = ''): mixed
    {
        foreach ($keys as $key) {
            $normalizedKey = $this->normalizeImportKey($key);
            if (array_key_exists($normalizedKey, $item)) {
                return $item[$normalizedKey];
            }

            if (array_key_exists($key, $item)) {
                return $item[$key];
            }
        }

        return $fallback;
    }

    private function normalizeImportType(string $type): string
    {
        return match ($this->normalizeImportText($type)) {
            'revenu', 'recette', 'income' => 'income',
            'depense', 'dépense', 'expense' => 'expense',
            default => 'expense',
        };
    }

    private function normalizeImportFrequency(string $frequency): string
    {
        return match ($this->normalizeImportText($frequency)) {
            'hebdomadaire', 'weekly' => 'weekly',
            'annuelle', 'annuel', 'yearly' => 'yearly',
            'mensuelle', 'mensuel', 'monthly' => 'monthly',
            default => 'monthly',
        };
    }

    private function normalizeImportBoolean(mixed $value): bool
    {
        if (true === $value) {
            return true;
        }

        return in_array($this->normalizeImportText((string) $value), ['1', 'true', 'yes', 'oui', 'o'], true);
    }

    private function normalizeImportKey(string $key): string
    {
        return match ($this->normalizeImportText($key)) {
            'type' => 'type',
            'amount' => 'amount',
            'montant' => 'amount',
            'category' => 'category',
            'categorie' => 'category',
            'label' => 'label',
            'libelle' => 'label',
            'note' => 'note',
            'date' => 'occurredAt',
            'occurredat' => 'occurredAt',
            'recurrent' => 'isRecurring',
            'isrecurring' => 'isRecurring',
            'frequence' => 'recurrenceFrequency',
            'recurrencefrequency' => 'recurrenceFrequency',
            default => $this->normalizeImportText($key),
        };
    }

    private function normalizeImportText(string $value): string
    {
        $value = trim($value, "\xEF\xBB\xBF \t\n\r\0\x0B");
        $value = strtr($value, [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
        ]);

        return strtolower($value);
    }
}
