<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[UniqueEntity('currentTimer')]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['session:read'])]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sessions')]
    #[Groups(['session:read'])]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['session:read'])]
    private ?Timer $currentTimer = null;

    /**
     * @var Collection<int, Timer>
     */
    #[ORM\OneToMany(targetEntity: Timer::class, mappedBy: 'session')]
    private Collection $timers;

    public function __construct(Timer $timer)
    {
        $this->currentTimer = $timer;
        $this->users = new ArrayCollection();
        $this->timers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

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

    /**
     * @return Collection<int, Timer>
     */
    public function getTimers(): Collection
    {
        return $this->timers;
    }

    public function addTimer(Timer $timer): static
    {
        if (!$this->timers->contains($timer)) {
            $this->timers->add($timer);
            $timer->setSession($this);
        }

        return $this;
    }

    public function removeTimer(Timer $timer): static
    {
        if ($this->timers->removeElement($timer)) {
            // set the owning side to null (unless already changed)
            if ($timer->getSession() === $this) {
                $timer->setSession(null);
            }
        }

        return $this;
    }
}
