<?php

namespace App\Entity;

use App\Repository\CycleCareEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CycleCareEventRepository::class)]
#[ORM\Table(name: 'risk_cycle_care_event')]
#[ORM\Index(name: 'IDX_RISK_CYCLE_CARE_EVENT_USER_DATE', columns: ['user_id', 'event_date'])]
class CycleCareEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $eventDate;

    #[ORM\Column(length: 40)]
    private string $type = 'note';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user)
    {
        $now = new \DateTimeImmutable();
        $this->user = $user;
        $this->eventDate = $now;
        $this->createdAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEventDate(): \DateTimeImmutable
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTimeImmutable $eventDate): self
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
