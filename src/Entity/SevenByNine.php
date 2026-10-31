<?php

namespace App\Entity;

use App\Enum\TimerType;
use App\Repository\SevenByNineRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SevenByNineRepository::class)]
class SevenByNine
{
    public const int TIMER_TASK_MAX = 7;
    public const int TIMER_PAUSE_MAX = 6;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['seven_by_nine:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['seven_by_nine:read'])]
    private ?int $countTasks = null;

    #[ORM\Column]
    #[Groups(['seven_by_nine:read'])]
    private ?int $countPauses = null;

    #[Groups(['seven_by_nine:read'])]
    #[ORM\ManyToOne(inversedBy: 'sevenByNines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Timer $currentTimer = null;

    #[ORM\Column(enumType: TimerType::class)]
    #[Groups(['seven_by_nine:read'])]
    private ?TimerType $lastTimerType = null;

    #[ORM\Column]
    #[Groups(['seven_by_nine:read'])]
    private array $drawnValues = [];

    #[ORM\ManyToOne(inversedBy: 'sevenByNines')]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(Timer $timer, int $firstDraw, ?User $user)
    {
        $this->countTasks = 1;
        $this->countPauses = 0;
        $this->currentTimer = $timer;
        $this->lastTimerType = TimerType::Task;
        $this->drawnValues = [$firstDraw];
        $this->createdBy = !empty($user) ? $user : null;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return bool
     */
    public function incrementTaskCount(): bool
    {
        if ($this->countTasks === $this::TIMER_TASK_MAX) {
            return false;
        }

        $this->countTasks += 1;
        return true;
    }

    /**
     * @return bool
     */
    public function incrementPauseCount(): bool
    {
        if ($this->countPauses === $this::TIMER_PAUSE_MAX) {
            return false;
        }

        $this->countPauses += 1;
        return true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountTasks(): ?int
    {
        return $this->countTasks;
    }

    public function setCountTasks(int $countTasks): static
    {
        $this->countTasks = $countTasks;

        return $this;
    }

    public function getCountPauses(): ?int
    {
        return $this->countPauses;
    }

    public function setCountPauses(int $countPauses): static
    {
        $this->countPauses = $countPauses;

        return $this;
    }

    public function getCurrentTimer(): ?Timer
    {
        return $this->currentTimer;
    }

    public function setCurrentTimer(?Timer $currentTimer): static
    {
        $this->currentTimer = $currentTimer;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

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

    public function getLastTimerType(): ?TimerType
    {
        return $this->lastTimerType;
    }

    public function setLastTimerType(TimerType $lastTimerType): static
    {
        $this->lastTimerType = $lastTimerType;

        return $this;
    }

    public function getDrawnValues(): array
    {
        return $this->drawnValues;
    }

    public function setDrawnValues(array $drawnValues): static
    {
        $this->drawnValues = $drawnValues;

        return $this;
    }
}
