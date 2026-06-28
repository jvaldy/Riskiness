<?php

namespace App\Service;

final class PasswordVaultCrypto
{
    public function generateSalt(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function hashMasterPassword(string $masterPassword): string
    {
        return password_hash($masterPassword, PASSWORD_DEFAULT);
    }

    public function isMasterPasswordValid(string $masterPassword, string $hash): bool
    {
        return password_verify($masterPassword, $hash);
    }

    public function deriveKey(string $masterPassword, string $salt): string
    {
        $saltBinary = hex2bin($salt);
        if (false === $saltBinary) {
            throw new \RuntimeException('Invalid vault salt.');
        }

        $key = hash_pbkdf2('sha256', $masterPassword, $saltBinary, 150000, 32, true);
        if (false === $key) {
            throw new \RuntimeException('Unable to derive vault key.');
        }

        return $key;
    }

    public function encrypt(string $plaintext, string $key): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $ciphertext) {
            throw new \RuntimeException('Unable to encrypt vault content.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $payload, string $key): string
    {
        $binary = base64_decode($payload, true);
        if (false === $binary || strlen($binary) < 28) {
            throw new \RuntimeException('Invalid encrypted payload.');
        }

        $iv = substr($binary, 0, 12);
        $tag = substr($binary, 12, 16);
        $ciphertext = substr($binary, 28);
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $plaintext) {
            throw new \RuntimeException('Unable to decrypt vault content.');
        }

        return $plaintext;
    }

    public function fingerprint(string $title, string $username, string $url): string
    {
        $normalized = mb_strtolower(trim($title)) . "\n" . mb_strtolower(trim($username)) . "\n" . mb_strtolower(trim($url));

        return hash('sha256', $normalized);
    }
}
