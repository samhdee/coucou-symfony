<?php

namespace App\Entity;

use App\Enum\TimerStatus;
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
    private ?\DateTime $endsAt = null;

    #[ORM\Column(enumType: TimerStatus::class)]
    private ?TimerStatus $status = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(string $label, int $value)
    {
        $this->label = $label;
        $this->value = $value;
        $this->remainingAtPause = $value;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function start(\DateTime $now): void
    {
        if ($this->status !== TimerStatus::Paused) {
            throw new \LogicException('Le timer ne peut être démarré que depuis l\'état pause.');
        }

        $this->endsAt = $now->modify(sprintf('+%d seconds', $this->remainingAtPause));
        $this->status = TimerStatus::Running;
    }

    public function pause(\DateTimeImmutable $now): void
    {
        if ($this->status !== TimerStatus::Running) {
            throw new \LogicException('Seul un timer en cours peut être mis en pause.');
        }

        $this->remainingAtPause = max(0, $this->endsAt->getTimestamp() - $now->getTimestamp());
        $this->endsAt = null;
        $this->status = TimerStatus::Paused;
    }

    public function getRemainingSeconds(\DateTimeImmutable $now): int
    {
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

    public function getEndsAt(): ?\DateTime
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTime $endsAt): static
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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
}
