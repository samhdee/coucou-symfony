<?php

namespace App\Entity;

use App\Enum\TimerStatus;
use App\Enum\TimerType;
use App\Repository\TimerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TimerRepository::class)]
class Timer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private ?int $value = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private ?int $remainingAtPause = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(enumType: TimerType::class)]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private ?TimerType $type = null;

    #[ORM\Column(enumType: TimerStatus::class)]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private ?TimerStatus $status = null;

    /**
     * @var Collection<int, SevenByNine>
     */
    #[ORM\OneToMany(targetEntity: SevenByNine::class, mappedBy: 'currentTimer')]
    private Collection $sevenByNines;

    #[ORM\Column(nullable: true)]
    #[Groups(['seven_by_nine:read', 'timer:read'])]
    private ?int $position = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'attendedTimers')]
    #[Groups(['seven_by_nine:read', 'timer:read', 'session:read'])]
    private Collection $attendants;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'currentTimer')]
    private Collection $sessions;

    #[ORM\ManyToOne(inversedBy: 'timers')]
    private ?Session $session = null;

    public function __construct(TimerType $type, int $value, ?TimerStatus $status = null, ?int $position = null)
    {
        $now = new \DateTimeImmutable();
        $this->value = $value;
        $this->remainingAtPause = $value;
        $this->type = $type;
        $this->status = $status ?? TimerStatus::Running;
        $this->endsAt = $status === TimerStatus::Running ? $now->add(new \DateInterval("PT{$value}S")) : null;
        $this->position = $position;
        $this->createdAt = new \DateTimeImmutable();
        $this->sevenByNines = new ArrayCollection();
        $this->attendants = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    public function getRemainingSeconds(): int
    {
        $now = new \DateTimeImmutable();

        return match ($this->status) {
            TimerStatus::Running => max(0, $this->endsAt->getTimestamp() - $now->getTimestamp()),
            TimerStatus::Paused => $this->remainingAtPause,
            default => 0,
        };
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getRemainingAtPause(): ?int
    {
        return $this->remainingAtPause;
    }

    public function setRemainingAtPause(int $remainingAtPause): static
    {
        $this->remainingAtPause = $remainingAtPause;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getStatus(): ?TimerStatus
    {
        return $this->status;
    }

    public function setStatus(TimerStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getType(): ?TimerType
    {
        return $this->type;
    }

    public function setType(TimerType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    /**
     * @return Collection<int, SevenByNine>
     */
    public function getSevenByNines(): Collection
    {
        return $this->sevenByNines;
    }

    public function addSevenByNine(SevenByNine $sevenByNine): static
    {
        if (!$this->sevenByNines->contains($sevenByNine)) {
            $this->sevenByNines->add($sevenByNine);
            $sevenByNine->setCurrentTimer($this);
        }

        return $this;
    }

    public function removeSevenByNine(SevenByNine $sevenByNine): static
    {
        if ($this->sevenByNines->removeElement($sevenByNine)) {
            // set the owning side to null (unless already changed)
            if ($sevenByNine->getCurrentTimer() === $this) {
                $sevenByNine->setCurrentTimer(null);
            }
        }

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAttendants(): Collection
    {
        return $this->attendants;
    }

    public function addAttendant(User $attendant): static
    {
        if (!$this->attendants->contains($attendant)) {
            $this->attendants->add($attendant);
            $attendant->addAttendedTimer($this);
        }

        return $this;
    }

    public function removeAttendant(User $attendant): static
    {
        if ($this->attendants->removeElement($attendant)) {
            $attendant->removeAttendedTimer($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCurrentTimer($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getCurrentTimer() === $this) {
                $session->setCurrentTimer(null);
            }
        }

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
}
