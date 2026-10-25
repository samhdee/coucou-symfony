<?php

namespace App\Entity;

use App\Enum\TimerPresetAdded;
use App\Repository\TimersPresetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimersPresetRepository::class)]
class TimersPreset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\Column(enumType: TimerPresetAdded::class)]
    private ?TimerPresetAdded $added_by = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getAddedBy(): ?TimerPresetAdded
    {
        return $this->added_by;
    }

    public function setAddedBy(TimerPresetAdded $added_by): static
    {
        $this->added_by = $added_by;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
