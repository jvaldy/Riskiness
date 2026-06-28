<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

#[ORM\Entity(repositoryClass: \App\Repository\ResetPasswordRequestRepository::class)]
#[ORM\Table(name: 'reset_password_request')]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(length: 20, unique: true)]
    private string $selector;

    #[ORM\Column(length: 255)]
    private string $hashedToken;

    public function __construct(
        User $user,
        \DateTimeInterface $requestedAt,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ) {
        $this->user = $user;
        $this->requestedAt = \DateTimeImmutable::createFromInterface($requestedAt);
        $this->expiresAt = \DateTimeImmutable::createFromInterface($expiresAt);
        $this->selector = $selector;
        $this->hashedToken = $hashedToken;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() >= $this->expiresAt;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getUser(): object
    {
        return $this->user;
    }
}
