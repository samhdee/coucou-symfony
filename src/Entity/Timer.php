<?php

namespace App\Entity;

use App\Enum\TimerStatus;
use App\Enum\TimerType;
use App\Repository\TimerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimerRepository::class)]
class Timer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $value = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $remainingAtPause = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(enumType: TimerStatus::class)]
    private ?TimerStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(enumType: TimerType::class)]
    private ?TimerType $type = null;

    #[ORM\ManyToOne(inversedBy: 'timers')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $User = null;

    public function __construct(TimerType $type, int $value, ?TimerStatus $status = null, ?User $user = null)
    {
        $now = new \DateTimeImmutable();
        $this->value = $value;
        $this->remainingAtPause = $value;
        $this->type = $type;
        $this->status = $status ?? TimerStatus::Running;
        $this->endsAt = $status === TimerStatus::Running ? $now->add(new \DateInterval("PT{$value}S")) : null;
        $this->createdAt = new \DateTimeImmutable();
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
}
