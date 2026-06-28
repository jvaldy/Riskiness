<?php

namespace App\Controller;

use App\Entity\PasswordVault;
use App\Entity\PasswordVaultEntry;
use App\Entity\User;
use App\Repository\PasswordVaultEntryRepository;
use App\Repository\PasswordVaultRepository;
use App\Service\PasswordVaultCrypto;
use App\Service\PasswordVaultSessionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordManagerController extends AbstractController
{
    private const MIN_MASTER_PASSWORD_LENGTH = 8;

    #[Route('/password-manager', name: 'app_password_manager', methods: ['GET'])]
    public function index(
        Request $request,
        PasswordVaultRepository $vaults,
        PasswordVaultEntryRepository $entries,
        PasswordVaultCrypto $crypto,
        PasswordVaultSessionManager $sessionManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $vault = $vaults->findOneByUser($user);
        $masterKey = $vault ? $sessionManager->getVaultKey($user, $request->getSession()) : null;
        $query = trim((string) $request->query->get('q', ''));
        $editEntryId = (int) $request->query->get('edit', 0);

        $view = [
            'vaultExists' => null !== $vault,
            'isUnlocked' => null !== $masterKey,
            'lockSeconds' => 180,
            'query' => $query,
            'setupMode' => null === $vault,
            'setupValues' => [
                'masterPassword' => '',
                'masterPasswordConfirmation' => '',
            ],
            'unlockValues' => [
                'masterPassword' => '',
            ],
            'entryForm' => null,
            'importError' => null,
            'entries' => [],
            'audit' => [
                'weak' => [],
                'reused' => [],
                'summary' => [
                    'total' => 0,
                    'weak' => 0,
                    'reused' => 0,
                ],
            ],
            'exportUrl' => $this->generateUrl('app_password_manager_export'),
            'generatorUrl' => $this->generateUrl('app_password_generator'),
        ];

        if (null === $vault) {
            return $this->render('password_manager/index.html.twig', $view);
        }

        if (null === $masterKey) {
            return $this->render('password_manager/index.html.twig', $view);
        }

        $sessionManager->touch($user, $request->getSession());
        $rawEntries = $entries->findByVaultOrdered($vault);
        $decryptedEntries = [];
        foreach ($rawEntries as $entry) {
            $decrypted = $this->decryptEntry($entry, $masterKey, $crypto);
            if ('' !== $query && ! $this->entryMatchesQuery($decrypted, $query)) {
                continue;
            }
            $decryptedEntries[] = $decrypted;
        }

        $audit = $this->buildAudit($rawEntries, $masterKey, $crypto);

        $entryForm = [
            'id' => null,
            'title' => '',
            'username' => '',
            'password' => '',
            'url' => '',
            'notes' => '',
        ];

        if ($editEntryId > 0) {
            $candidate = $this->findEntryForEdit($rawEntries, $editEntryId);
            if ($candidate instanceof PasswordVaultEntry) {
                $entryForm = $this->decryptEntry($candidate, $masterKey, $crypto);
            }
        }

        $view['isUnlocked'] = true;
        $view['entries'] = $decryptedEntries;
        $view['audit'] = $audit;
        $view['entryForm'] = $entryForm;

        return $this->render('password_manager/index.html.twig', $view);
    }

    #[Route('/password-manager/setup', name: 'app_password_manager_setup', methods: ['POST'])]
    public function setup(
        Request $request,
        EntityManagerInterface $entityManager,
        PasswordVaultRepository $vaults,
        PasswordVaultCrypto $crypto,
        PasswordVaultSessionManager $sessionManager
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('password_vault_setup', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Le jeton CSRF est invalide. Recharge la page puis réessaie.');

            return $this->redirectToRoute('app_password_manager');
        }

        if (null !== $vaults->findOneByUser($user)) {
            $this->addFlash('success', 'Le coffre existe déjà. Tu peux simplement le déverrouiller.');

            return $this->redirectToRoute('app_password_manager');
        }

        $masterPassword = (string) $request->request->get('master_password', '');
        $confirmation = (string) $request->request->get('master_password_confirmation', '');

        if (mb_strlen($masterPassword) < self::MIN_MASTER_PASSWORD_LENGTH) {
            $this->addFlash('error', sprintf('Le mot de passe maître doit contenir au moins %d caractères.', self::MIN_MASTER_PASSWORD_LENGTH));

            return $this->redirectToRoute('app_password_manager');
        }

        if ($masterPassword !== $confirmation) {
            $this->addFlash('error', 'La confirmation du mot de passe maître ne correspond pas.');

            return $this->redirectToRoute('app_password_manager');
        }

        $vault = new PasswordVault(
            $user,
            $crypto->hashMasterPassword($masterPassword),
            $crypto->generateSalt()
        );

        $entityManager->persist($vault);
        $entityManager->flush();

        $sessionManager->unlock($user, $crypto->deriveKey($masterPassword, $vault->getMasterKeySalt()), $request->getSession());
        $this->addFlash('success', 'Le coffre a été initialisé et déverrouillé.');

        return $this->redirectToRoute('app_password_manager');
    }

    #[Route('/password-manager/unlock', name: 'app_password_manager_unlock', methods: ['POST'])]
    public function unlock(
        Request $request,
        PasswordVaultRepository $vaults,
        PasswordVaultCrypto $crypto,
        PasswordVaultSessionManager $sessionManager
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('password_vault_unlock', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Le jeton CSRF est invalide. Recharge la page puis réessaie.');

            return $this->redirectToRoute('app_password_manager');
        }

        $vault = $vaults->findOneByUser($user);
        if (null === $vault) {
            $this->addFlash('error', 'Aucun coffre n’est initialisé pour ce compte.');

            return $this->redirectToRoute('app_password_manager');
        }

        $masterPassword = (string) $request->request->get('master_password', '');
        if (!$crypto->isMasterPasswordValid($masterPassword, $vault->getMasterPasswordHash())) {
            $this->addFlash('error', 'Le mot de passe maître est incorrect.');

            return $this->redirectToRoute('app_password_manager');
        }

        $sessionManager->unlock($user, $crypto->deriveKey($masterPassword, $vault->getMasterKeySalt()), $request->getSession());
        $this->addFlash('success', 'Coffre déverrouillé.');

        return $this->redirectToRoute('app_password_manager');
    }

    #[Route('/password-manager/lock', name: 'app_password_manager_lock', methods: ['POST'])]
    public function lock(
        Request $request,
        PasswordVaultSessionManager $sessionManager
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('password_vault_lock', (string) $request->request->get('_csrf_token'))) {
            return $this->redirectToRoute('app_password_manager');
        }

        $sessionManager->lock($user, $request->getSession());
        $this->addFlash('success', 'Coffre verrouillé.');

        return $this->redirectToRoute('app_password_manager');
    }

    #[Route('/password-manager/entry/save', name: 'app_password_manager_entry_save', methods: ['POST'])]
    public function saveEntry(
        Request $request,
        EntityManagerInterface $entityManager,
        PasswordVaultRepository $vaults,
        PasswordVaultEntryRepository $entries,
        PasswordVaultCrypto $crypto,
        PasswordVaultSessionManager $sessionManager
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('password_vault_entry', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Le jeton CSRF est invalide. Recharge la page puis réessaie.');

            return $this->redirectToRoute('app_password_manager');
        }

        $vault = $vaults->findOneByUser($user);
        $masterKey = $vault ? $sessionManager->getVaultKey($user, $request->getSession()) : null;
        if (null === $vault || null === $masterKey) {
            $this->addFlash('error', 'Le coffre doit être déverrouillé pour modifier des entrées.');

            return $this->redirectToRoute('app_password_manager');
        }

        $title = trim((string) $request->request->get('title', ''));
        $username = trim((string) $request->request->get('username', ''));
        $password = (string) $request->request->get('password', '');
        $url = trim((string) $request->request->get('url', ''));
        $notes = trim((string) $request->request->get('notes', ''));
        $entryId = (int) $request->request->get('entry_id', 0);

        if ('' === $title || '' === $password) {
            $this->addFlash('error', 'Le titre et le mot de passe sont obligatoires.');

            return $this->redirectToRoute('app_password_manager');
        }

        $url = $this->normalizeUrl($url);
        $fingerprint = $crypto->fingerprint($title, $username, $url);
        $currentEntry = null;

        if ($entryId > 0) {
            $currentEntry = $entries->find($entryId);
            if (!$currentEntry instanceof PasswordVaultEntry || $currentEntry->getVault()->getId() !== $vault->getId()) {
                $this->addFlash('error', 'Entrée introuvable.');

                return $this->redirectToRoute('app_password_manager');
            }
        }

        $collision = $entries->findOneByVaultAndFingerprint($vault, $fingerprint);
        if (null !== $collision && null !== $currentEntry && $collision->getId() !== $currentEntry->getId()) {
            $this->addFlash('error', 'Une autre entrée utilise déjà cette combinaison titre / identifiant / URL.');

            return $this->redirectToRoute('app_password_manager', ['edit' => $currentEntry->getId()]);
        }

        $isNewEntry = null === $currentEntry && null === $collision;

        if (null !== $collision && null === $currentEntry) {
            $entry = $collision;
        } else {
            $entry = $currentEntry ?? new PasswordVaultEntry($vault);
        }
        $entry
            ->setEntryFingerprint($fingerprint)
            ->setTitleEncrypted($crypto->encrypt($title, $masterKey))
            ->setUsernameEncrypted($crypto->encrypt($username, $masterKey))
            ->setPasswordEncrypted($crypto->encrypt($password, $masterKey))
            ->setUrlEncrypted($crypto->encrypt($url, $masterKey))
            ->setNotesEncrypted($crypto->encrypt($notes, $masterKey))
            ->touch();

        $entityManager->persist($entry);
        $entityManager->flush();
        $sessionManager->touch($user, $request->getSession());

        $this->addFlash('success', $isNewEntry ? 'Entrée ajoutée.' : 'Entrée mise à jour.');

        return $this->redirectToRoute('app_password_manager');
    }

    #[Route('/password-manager/entry/{id}/delete', name: 'app_password_manager_entry_delete', methods: ['POST'])]
    public function deleteEntry(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        PasswordVaultRepository $vaults,
        PasswordVaultEntryRepository $entries,
        PasswordVaultSessionManager $sessionManager
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('password_vault_delete_' . $id, (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Le jeton CSRF est invalide. Recharge la page puis réessaie.');

            return $this->redirectToRoute('app_password_manager');
        }

        $vault = $vaults->findOneByUser($user);
        $masterKey = $vault ? $sessionManager->getVaultKey($user, $request->getSession()) : null;
        if (null === $vault || null === $masterKey) {
            $this->addFlash('error', 'Le coffre doit être déverrouillé pour supprimer une entrée.');

            return $this->redirectToRoute('app_password_manager');
        }

        $entry = $entries->find($id);
        if (!$entry instanceof PasswordVaultEntry || $entry->getVault()->getId() !== $vault->getId()) {
            $this->addFlash('error', 'Entrée introuvable.');

            return $this->redirectToRoute('app_password_manager');
        }

        $entityManager->remove($entry);
        $entityManager->flush();
        $sessionManager->touch($user, $request->getSession());

        $this->addFlash('success', 'Entrée supprimée.');

        return $this->redirectToRoute('app_password_manager');
    }

    #[Route('/password-manager/export', name: 'app_password_manager_export', methods: ['GET'])]
    public function export(
        Request $request,
        PasswordVaultRepository $vaults,
        PasswordVaultEntryRepository $entries,
        PasswordVaultCrypto $crypto,
        PasswordVaultSessionManager $sessionManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $vault = $vaults->findOneByUser($user);
        $masterKey = $vault ? $sessionManager->getVaultKey($user, $request->getSession()) : null;
        if (null === $vault || null === $masterKey) {
            $this->addFlash('error', 'Le coffre doit être déverrouillé pour exporter les données.');

            return $this->redirectToRoute('app_password_manager');
        }

        $payload = $this->buildExportPayload($entries->findByVaultOrdered($vault), $masterKey, $crypto);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (false === $json) {
            throw new \RuntimeException('Unable to encode export payload.');
        }

        return new Response(
            $json,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="riskiness-password-vault.json"',
            ]
        );
    }

    #[Route('/password-manager/import', name: 'app_password_manager_import', methods: ['POST'])]
    public function import(
        Request $request,
        EntityManagerInterface $entityManager,
        PasswordVaultRepository $vaults,
        PasswordVaultEntryRepository $entries,
        PasswordVaultCrypto $crypto,
        PasswordVaultSessionManager $sessionManager
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('password_vault_import', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Le jeton CSRF est invalide. Recharge la page puis réessaie.');

            return $this->redirectToRoute('app_password_manager');
        }

        $vault = $vaults->findOneByUser($user);
        $masterKey = $vault ? $sessionManager->getVaultKey($user, $request->getSession()) : null;
        if (null === $vault || null === $masterKey) {
            $this->addFlash('error', 'Le coffre doit être déverrouillé pour importer des données.');

            return $this->redirectToRoute('app_password_manager');
        }

        $upsertExisting = '1' === (string) $request->request->get('upsert_existing', '1');
        $rawJson = '';

        $uploadedFile = $request->files->get('import_file');
        if ($uploadedFile && method_exists($uploadedFile, 'isValid') && $uploadedFile->isValid()) {
            $rawJson = (string) file_get_contents($uploadedFile->getPathname());
        } else {
            $rawJson = (string) $request->request->get('import_json', '');
        }

        try {
            $items = $this->normalizeImportPayload($rawJson);
        } catch (\Throwable $exception) {
            $this->addFlash('error', 'Import invalide: ' . $exception->getMessage());

            return $this->redirectToRoute('app_password_manager');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            $username = trim((string) ($item['username'] ?? ''));
            $password = (string) ($item['password'] ?? '');
            $url = trim((string) ($item['url'] ?? ''));
            $notes = trim((string) ($item['notes'] ?? ''));

            if ('' === $title || '' === $password) {
                ++$skipped;
                continue;
            }

            $url = $this->normalizeUrl($url);
            $fingerprint = $crypto->fingerprint($title, $username, $url);
            $entry = $entries->findOneByVaultAndFingerprint($vault, $fingerprint);

            if (null === $entry) {
                $entry = new PasswordVaultEntry($vault);
                ++$created;
            } elseif (!$upsertExisting) {
                ++$skipped;
                continue;
            } else {
                ++$updated;
            }

            $entry
                ->setEntryFingerprint($fingerprint)
                ->setTitleEncrypted($crypto->encrypt($title, $masterKey))
                ->setUsernameEncrypted($crypto->encrypt($username, $masterKey))
                ->setPasswordEncrypted($crypto->encrypt($password, $masterKey))
                ->setUrlEncrypted($crypto->encrypt($url, $masterKey))
                ->setNotesEncrypted($crypto->encrypt($notes, $masterKey))
                ->touch();

            $entityManager->persist($entry);
        }

        $entityManager->flush();
        $sessionManager->touch($user, $request->getSession());

        $this->addFlash('success', sprintf('Import terminé: %d créées, %d mises à jour, %d ignorées.', $created, $updated, $skipped));

        return $this->redirectToRoute('app_password_manager');
    }

    private function decryptEntry(PasswordVaultEntry $entry, string $masterKey, PasswordVaultCrypto $crypto): array
    {
        $title = $crypto->decrypt($entry->getTitleEncrypted(), $masterKey);
        $username = $crypto->decrypt($entry->getUsernameEncrypted(), $masterKey);
        $password = $crypto->decrypt($entry->getPasswordEncrypted(), $masterKey);
        $url = $crypto->decrypt($entry->getUrlEncrypted(), $masterKey);
        $notes = $crypto->decrypt($entry->getNotesEncrypted(), $masterKey);

        return [
            'id' => $entry->getId(),
            'title' => $title,
            'username' => $username,
            'password' => $password,
            'url' => $url,
            'notes' => $notes,
            'fingerprint' => $entry->getEntryFingerprint(),
            'updatedAt' => $entry->getUpdatedAt(),
            'strength' => $this->passwordStrength($password),
        ];
    }

    private function buildAudit(array $entries, string $masterKey, PasswordVaultCrypto $crypto): array
    {
        $decrypted = array_map(fn (PasswordVaultEntry $entry) => $this->decryptEntry($entry, $masterKey, $crypto), $entries);
        $passwordGroups = [];

        foreach ($decrypted as $entry) {
            $passwordGroups[$entry['password']][] = $entry;
        }

        $weak = [];
        $reused = [];

        foreach ($decrypted as $entry) {
            if ($entry['strength']['weak']) {
                $weak[] = $entry;
            }
        }

        foreach ($passwordGroups as $group) {
            if (count($group) > 1) {
                foreach ($group as $entry) {
                    $reused[] = $entry;
                }
            }
        }

        return [
            'weak' => $weak,
            'reused' => $this->uniqueById($reused),
            'summary' => [
                'total' => count($decrypted),
                'weak' => count($weak),
                'reused' => count($this->uniqueById($reused)),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildExportPayload(array $entries, string $masterKey, PasswordVaultCrypto $crypto): array
    {
        return array_map(
            fn (PasswordVaultEntry $entry) => [
                'title' => $crypto->decrypt($entry->getTitleEncrypted(), $masterKey),
                'username' => $crypto->decrypt($entry->getUsernameEncrypted(), $masterKey),
                'password' => $crypto->decrypt($entry->getPasswordEncrypted(), $masterKey),
                'url' => $crypto->decrypt($entry->getUrlEncrypted(), $masterKey),
                'notes' => $crypto->decrypt($entry->getNotesEncrypted(), $masterKey),
                'entryKey' => $entry->getEntryFingerprint(),
                'updatedAt' => $entry->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $entries
        );
    }

    /**
     * @return array<int, array{title: string, username: string, password: string, url: string, notes: string}>
     */
    private function normalizeImportPayload(string $rawJson): array
    {
        $decoded = json_decode($rawJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Le contenu importé doit être un tableau JSON.');
        }

        if (isset($decoded['entries']) && is_array($decoded['entries'])) {
            $decoded = $decoded['entries'];
        }

        $items = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $items[] = [
                'title' => (string) ($item['title'] ?? ''),
                'username' => (string) ($item['username'] ?? ''),
                'password' => (string) ($item['password'] ?? ''),
                'url' => (string) ($item['url'] ?? ''),
                'notes' => (string) ($item['notes'] ?? ''),
            ];
        }

        return $items;
    }

    private function entryMatchesQuery(array $entry, string $query): bool
    {
        $haystack = mb_strtolower($entry['title'] . ' ' . $entry['username'] . ' ' . $entry['url'] . ' ' . $entry['notes']);
        $needle = mb_strtolower($query);

        return str_contains($haystack, $needle);
    }

    private function passwordStrength(string $password): array
    {
        $length = mb_strlen($password);
        $hasLower = (bool) preg_match('/[a-z]/', $password);
        $hasUpper = (bool) preg_match('/[A-Z]/', $password);
        $hasDigit = (bool) preg_match('/\d/', $password);
        $hasSymbol = (bool) preg_match('/[^a-zA-Z0-9]/', $password);
        $weak = $length < 12 || !$hasLower || !$hasUpper || !$hasDigit || !$hasSymbol;

        return [
            'weak' => $weak,
            'length' => $length,
            'score' => min(100, ($length * 6) + ($hasLower ? 8 : 0) + ($hasUpper ? 8 : 0) + ($hasDigit ? 10 : 0) + ($hasSymbol ? 14 : 0)),
            'label' => $weak ? 'Faible' : 'Solide',
        ];
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ('' === $url) {
            return '';
        }

        if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $url)) {
            return 'https://' . $url;
        }

        return $url;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @return array<int, array<string, mixed>>
     */
    private function uniqueById(array $entries): array
    {
        $seen = [];
        $unique = [];

        foreach ($entries as $entry) {
            $id = (int) ($entry['id'] ?? 0);
            if (0 === $id || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $unique[] = $entry;
        }

        return $unique;
    }

    /**
     * @param PasswordVaultEntry[] $entries
     */
    private function findEntryForEdit(array $entries, int $entryId): ?PasswordVaultEntry
    {
        foreach ($entries as $entry) {
            if ($entry->getId() === $entryId) {
                return $entry;
            }
        }

        return null;
    }
}
