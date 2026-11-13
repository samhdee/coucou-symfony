<?php

namespace App\Entity;

use App\Repository\SeanceAttendantRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SeanceAttendantRepository::class)]
class SeanceAttendant
{
    private const int LEFT_AT_FUTURE = 35;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'seanceAttendants')]
    #[Groups(['seance_attendance:read'])]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['seance_attendance:read'])]
    private ?string $guestId = null;

    #[ORM\ManyToOne(inversedBy: 'seanceAttendants')]
    private ?Session $session = null;

    #[ORM\ManyToOne(inversedBy: 'seanceAttendants')]
    private ?SevenByNine $sevenByNine = null;

    #[ORM\Column]
    private ?DateTimeImmutable $joinedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['seance_attendance:read'])]
    private ?DateTimeImmutable $leftAt = null;

    /**
     * @throws DateMalformedStringException
     */
    public function __construct(
        ?User $user = null,
        ?string $guestId = null,
        ?Session $session = null,
        ?SevenByNine $sevenByNine = null,
    )
    {
        $now = new DateTimeImmutable();
        $this->joinedAt = $now;
        $this->leftAt = $now->modify('+' . self::LEFT_AT_FUTURE . ' seconds');
        $this->user = $user;
        $this->guestId = $guestId;
        $this->session = $session;
        $this->sevenByNine = $sevenByNine;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function moveBackLeftAt(): void
    {
        $this->leftAt = new DateTimeImmutable()->modify('+' . self::LEFT_AT_FUTURE . ' seconds');
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

    public function getLeftAt(): ?DateTimeImmutable
    {
        return $this->leftAt;
    }

    public function setLeftAt(?DateTimeImmutable $leftAt): static
    {
        $this->leftAt = $leftAt;

        return $this;
    }
}
