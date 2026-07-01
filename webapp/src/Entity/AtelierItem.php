<?php

namespace App\Entity;

use App\Repository\AtelierItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AtelierItemRepository::class)]
#[ORM\Table(name: 'risk_atelier_item')]
#[ORM\Index(name: 'IDX_RISK_ATELIER_ITEM_USER_UPDATED', columns: ['user_id', 'updated_at'])]
class AtelierItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(length: 32)]
    private string $type = 'recipe';

    #[ORM\Column(length: 80)]
    private string $category = 'Cuisine';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $estimatedDuration = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $targetQuantity = null;

    #[ORM\Column(length: 20)]
    private string $difficulty = 'easy';

    #[ORM\Column(length: 20)]
    private string $status = 'draft';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isFavorite = false;

    #[ORM\Column(type: 'json')]
    private array $resources = [];

    #[ORM\Column(type: 'json')]
    private array $steps = [];

    #[ORM\Column(type: 'json')]
    private array $variants = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user)
    {
        $now = new \DateTimeImmutable();
        $this->user = $user;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getTitle(): string { return $this->title; }
    public function getType(): string { return $this->type; }
    public function getCategory(): string { return $this->category; }
    public function getTags(): ?string { return $this->tags; }
    public function getEstimatedDuration(): ?int { return $this->estimatedDuration; }
    public function getTargetQuantity(): ?string { return $this->targetQuantity; }
    public function getDifficulty(): string { return $this->difficulty; }
    public function getStatus(): string { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function isFavorite(): bool { return $this->isFavorite; }
    public function getResources(): array { return $this->resources; }
    public function getSteps(): array { return $this->steps; }
    public function getVariants(): array { return $this->variants; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);
        return $this->touch();
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this->touch();
    }

    public function setCategory(string $category): self
    {
        $this->category = trim($category);
        return $this->touch();
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $this->blankToNull($tags);
        return $this->touch();
    }

    public function setEstimatedDuration(?int $estimatedDuration): self
    {
        $this->estimatedDuration = $estimatedDuration;
        return $this->touch();
    }

    public function setTargetQuantity(?string $targetQuantity): self
    {
        $this->targetQuantity = $this->blankToNull($targetQuantity);
        return $this->touch();
    }

    public function setDifficulty(string $difficulty): self
    {
        $this->difficulty = $difficulty;
        return $this->touch();
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this->touch();
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $this->blankToNull($notes);
        return $this->touch();
    }

    public function setIsFavorite(bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;
        return $this->touch();
    }

    public function setResources(array $resources): self
    {
        $this->resources = array_values($resources);
        return $this->touch();
    }

    public function setSteps(array $steps): self
    {
        $this->steps = array_values($steps);
        return $this->touch();
    }

    public function setVariants(array $variants): self
    {
        $this->variants = array_values($variants);
        return $this->touch();
    }

    private function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    private function blankToNull(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);
        return '' === $value ? null : $value;
    }
}
