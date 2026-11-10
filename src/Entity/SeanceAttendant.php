<?php

namespace App\Entity;

use App\Repository\SeanceAttendantRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceAttendantRepository::class)]
class SeanceAttendant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'seanceAttendants')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $guestId = null;

    #[ORM\Column]
    private ?DateTimeImmutable $joinedAt = null;

    #[ORM\ManyToOne(inversedBy: 'seanceAttendants')]
    private ?Session $session = null;

    #[ORM\ManyToOne(inversedBy: 'seanceAttendants')]
    private ?SevenByNine $sevenByNine = null;

    public function __construct()
    {
        $this->joinedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGuestId(): ?string
    {
        return $this->guestId;
    }

    public function setGuestId(?string $guestId): static
    {
        $this->guestId = $guestId;

        return $this;
    }

    public function getJoinedAt(): ?DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getSevenByNine(): ?SevenByNine
    {
        return $this->sevenByNine;
    }

    public function setSevenByNine(?SevenByNine $sevenByNine): static
    {
        $this->sevenByNine = $sevenByNine;

        return $this;
    }
}
