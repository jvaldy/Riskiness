<?php

namespace App\Entity;

use App\Repository\PasswordVaultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasswordVaultRepository::class)]
#[ORM\Table(name: 'risk_password_vault')]
#[ORM\UniqueConstraint(name: 'UNIQ_RISK_PASSWORD_VAULT_USER', columns: ['user_id'])]
class PasswordVault
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 255)]
    private string $masterPasswordHash = '';

    #[ORM\Column(length: 64)]
    private string $masterKeySalt = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user, string $masterPasswordHash, string $masterKeySalt)
    {
        $now = new \DateTimeImmutable();
        $this->user = $user;
        $this->masterPasswordHash = $masterPasswordHash;
        $this->masterKeySalt = $masterKeySalt;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getMasterPasswordHash(): string
    {
        return $this->masterPasswordHash;
    }

    public function setMasterPasswordHash(string $masterPasswordHash): self
    {
        $this->masterPasswordHash = $masterPasswordHash;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getMasterKeySalt(): string
    {
        return $this->masterKeySalt;
    }

    public function setMasterKeySalt(string $masterKeySalt): self
    {
        $this->masterKeySalt = $masterKeySalt;
        $this->updatedAt = new \DateTimeImmutable();

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
