<?php

namespace App\Entity;

use App\Repository\MovieTrackerEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieTrackerEntryRepository::class)]
#[ORM\Table(name: 'risk_movie_tracker_entry')]
#[ORM\Index(name: 'IDX_RISK_MOVIE_TRACKER_ENTRY_USER', columns: ['user_id'])]
class MovieTrackerEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 20)]
    private string $type = 'film';

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(length: 20)]
    private string $state = 'planned';

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $waitingDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $animeFormat = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $season = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $episode = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $timeCode = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $bookPoint = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        $this->touch();

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);
        $this->touch();

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        $this->touch();

        return $this;
    }

    public function getWaitingDate(): ?\DateTimeImmutable
    {
        return $this->waitingDate;
    }

    public function setWaitingDate(?\DateTimeImmutable $waitingDate): self
    {
        $this->waitingDate = $waitingDate;
        $this->touch();

        return $this;
    }

    public function getAnimeFormat(): ?string
    {
        return $this->animeFormat;
    }

    public function setAnimeFormat(?string $animeFormat): self
    {
        $this->animeFormat = $this->blankToNull($animeFormat);
        $this->touch();

        return $this;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(?int $season): self
    {
        $this->season = $season;
        $this->touch();

        return $this;
    }

    public function getEpisode(): ?int
    {
        return $this->episode;
    }

    public function setEpisode(?int $episode): self
    {
        $this->episode = $episode;
        $this->touch();

        return $this;
    }

    public function getTimeCode(): ?string
    {
        return $this->timeCode;
    }

    public function setTimeCode(?string $timeCode): self
    {
        $this->timeCode = $this->blankToNull($timeCode);
        $this->touch();

        return $this;
    }

    public function getBookPoint(): ?string
    {
        return $this->bookPoint;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $this->blankToNull($source);
        $this->touch();

        return $this;
    }

    public function setBookPoint(?string $bookPoint): self
    {
        $this->bookPoint = $this->blankToNull($bookPoint);
        $this->touch();

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $this->blankToNull($notes);
        $this->touch();

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $this->blankToNull($tags);
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

    private function blankToNull(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}
