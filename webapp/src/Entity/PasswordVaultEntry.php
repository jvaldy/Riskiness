<?php

namespace App\Entity;

use App\Repository\PasswordVaultEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasswordVaultEntryRepository::class)]
#[ORM\Table(name: 'risk_password_vault_entry')]
#[ORM\UniqueConstraint(name: 'UNIQ_RISK_PASSWORD_VAULT_ENTRY', columns: ['vault_id', 'entry_fingerprint'])]
class PasswordVaultEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PasswordVault::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PasswordVault $vault;

    #[ORM\Column(length: 64)]
    private string $entryFingerprint = '';

    #[ORM\Column(type: 'text')]
    private string $titleEncrypted = '';

    #[ORM\Column(type: 'text')]
    private string $usernameEncrypted = '';

    #[ORM\Column(type: 'text')]
    private string $passwordEncrypted = '';

    #[ORM\Column(type: 'text')]
    private string $urlEncrypted = '';

    #[ORM\Column(type: 'text')]
    private string $notesEncrypted = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(PasswordVault $vault)
    {
        $now = new \DateTimeImmutable();
        $this->vault = $vault;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVault(): PasswordVault
    {
        return $this->vault;
    }

    public function getEntryFingerprint(): string
    {
        return $this->entryFingerprint;
    }

    public function setEntryFingerprint(string $entryFingerprint): self
    {
        $this->entryFingerprint = $entryFingerprint;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getTitleEncrypted(): string
    {
        return $this->titleEncrypted;
    }

    public function setTitleEncrypted(string $titleEncrypted): self
    {
        $this->titleEncrypted = $titleEncrypted;

        return $this;
    }

    public function getUsernameEncrypted(): string
    {
        return $this->usernameEncrypted;
    }

    public function setUsernameEncrypted(string $usernameEncrypted): self
    {
        $this->usernameEncrypted = $usernameEncrypted;

        return $this;
    }

    public function getPasswordEncrypted(): string
    {
        return $this->passwordEncrypted;
    }

    public function setPasswordEncrypted(string $passwordEncrypted): self
    {
        $this->passwordEncrypted = $passwordEncrypted;

        return $this;
    }

    public function getUrlEncrypted(): string
    {
        return $this->urlEncrypted;
    }

    public function setUrlEncrypted(string $urlEncrypted): self
    {
        $this->urlEncrypted = $urlEncrypted;

        return $this;
    }

    public function getNotesEncrypted(): string
    {
        return $this->notesEncrypted;
    }

    public function setNotesEncrypted(string $notesEncrypted): self
    {
        $this->notesEncrypted = $notesEncrypted;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
