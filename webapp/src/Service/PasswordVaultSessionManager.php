<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PasswordVaultSessionManager
{
    private const TTL_SECONDS = 180;
    private const KEY_PREFIX = 'risk_password_manager';

    public function unlock(User $user, string $vaultKey, SessionInterface $session): void
    {
        $session->set($this->key($user, 'key'), base64_encode($vaultKey));
        $session->set($this->key($user, 'unlocked_at'), time());
    }

    public function isUnlocked(User $user, SessionInterface $session): bool
    {
        $unlockedAt = (int) $session->get($this->key($user, 'unlocked_at'), 0);

        if ($unlockedAt <= 0 || (time() - $unlockedAt) > self::TTL_SECONDS) {
            $this->lock($user, $session);

            return false;
        }

        return true;
    }

    public function touch(User $user, SessionInterface $session): void
    {
        if ($this->hasSessionKey($user, $session)) {
            $session->set($this->key($user, 'unlocked_at'), time());
        }
    }

    public function lock(User $user, SessionInterface $session): void
    {
        $session->remove($this->key($user, 'key'));
        $session->remove($this->key($user, 'unlocked_at'));
    }

    public function getVaultKey(User $user, SessionInterface $session): ?string
    {
        if (!$this->isUnlocked($user, $session)) {
            return null;
        }

        $encodedKey = (string) $session->get($this->key($user, 'key'), '');
        $decoded = base64_decode($encodedKey, true);

        return false === $decoded ? null : $decoded;
    }

    private function hasSessionKey(User $user, SessionInterface $session): bool
    {
        return '' !== (string) $session->get($this->key($user, 'key'), '');
    }

    private function key(User $user, string $suffix): string
    {
        return sprintf('%s.%d.%s', self::KEY_PREFIX, (int) $user->getId(), $suffix);
    }
}
