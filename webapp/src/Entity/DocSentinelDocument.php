<?php

namespace App\Entity;

use App\Repository\DocSentinelDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocSentinelDocumentRepository::class)]
#[ORM\Table(name: 'risk_doc_sentinel_document')]
#[ORM\Index(name: 'IDX_RISK_DOC_SENTINEL_USER_EXPIRATION', columns: ['user_id', 'expiration_date'])]
class DocSentinelDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 180)]
    private string $name = '';

    #[ORM\Column(length: 60)]
    private string $category = 'other';

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $expirationDate;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $reminderDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'boolean')]
    private bool $archived = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user)
    {
        $now = new \DateTimeImmutable();
        $this->user = $user;
        $this->expirationDate = $now;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);
        $this->touch();

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        $this->touch();

        return $this;
    }

    public function getExpirationDate(): \DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeImmutable $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
        $this->touch();

        return $this;
    }

    public function getReminderDate(): ?\DateTimeImmutable
    {
        return $this->reminderDate;
    }

    public function setReminderDate(?\DateTimeImmutable $reminderDate): self
    {
        $this->reminderDate = $reminderDate;
        $this->touch();

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $note = null !== $note ? trim($note) : null;
        $this->note = '' === $note ? null : $note;
        $this->touch();

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;
        $this->touch();

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
