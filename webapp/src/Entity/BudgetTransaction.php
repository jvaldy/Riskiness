<?php

namespace App\Entity;

use App\Repository\BudgetTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetTransactionRepository::class)]
#[ORM\Table(name: 'risk_budget_transaction')]
#[ORM\Index(name: 'IDX_RISK_BUDGET_TRANSACTION_USER_DATE', columns: ['user_id', 'occurred_at'])]
class BudgetTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 12)]
    private string $type = 'expense';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $amount = '0.00';

    #[ORM\Column(length: 80)]
    private string $category = 'Autre';

    #[ORM\Column(length: 180)]
    private string $label = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $occurredAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isRecurring = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $recurrenceFrequency = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user)
    {
        $now = new \DateTimeImmutable();
        $this->user = $user;
        $this->occurredAt = $now;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getType(): string { return $this->type; }

    public function setType(string $type): self
    {
        $this->type = $type;
        $this->touch();

        return $this;
    }

    public function getAmount(): string { return $this->amount; }

    public function setAmount(string $amount): self
    {
        $this->amount = number_format(max(0, (float) str_replace(',', '.', $amount)), 2, '.', '');
        $this->touch();

        return $this;
    }

    public function getCategory(): string { return $this->category; }

    public function setCategory(string $category): self
    {
        $this->category = trim($category);
        $this->touch();

        return $this;
    }

    public function getLabel(): string { return $this->label; }

    public function setLabel(string $label): self
    {
        $this->label = trim($label);
        $this->touch();

        return $this;
    }

    public function getNote(): ?string { return $this->note; }

    public function setNote(?string $note): self
    {
        $note = null === $note ? null : trim($note);
        $this->note = '' === $note ? null : $note;
        $this->touch();

        return $this;
    }

    public function getOccurredAt(): \DateTimeImmutable { return $this->occurredAt; }

    public function setOccurredAt(\DateTimeImmutable $occurredAt): self
    {
        $this->occurredAt = $occurredAt;
        $this->touch();

        return $this;
    }

    public function isRecurring(): bool { return $this->isRecurring; }

    public function setIsRecurring(bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;
        $this->touch();

        return $this;
    }

    public function getRecurrenceFrequency(): ?string { return $this->recurrenceFrequency; }

    public function setRecurrenceFrequency(?string $recurrenceFrequency): self
    {
        $recurrenceFrequency = null === $recurrenceFrequency ? null : trim($recurrenceFrequency);
        $this->recurrenceFrequency = '' === $recurrenceFrequency ? null : $recurrenceFrequency;
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
