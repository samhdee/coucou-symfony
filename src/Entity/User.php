<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $discordId = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Timer>
     */
    #[ORM\OneToMany(targetEntity: Timer::class, mappedBy: 'User')]
    private Collection $timers;

    /**
     * @var Collection<int, SevenByNine>
     */
    #[ORM\OneToMany(targetEntity: SevenByNine::class, mappedBy: 'createdBy')]
    private Collection $sevenByNines;

    public function __construct()
    {
        $this->timers = new ArrayCollection();
        $this->sevenByNines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): static
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // garantit au moins ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->discordId;
    }

    public function eraseCredentials(): void
    {
        // Rien à effacer : pas de mot de passe stocké
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
            $timer->setUser($this);
        }

        return $this;
    }

    public function removeTimer(Timer $timer): static
    {
        if ($this->timers->removeElement($timer)) {
            // set the owning side to null (unless already changed)
            if ($timer->getUser() === $this) {
                $timer->setUser(null);
            }
        }

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
            $sevenByNine->setCreatedBy($this);
        }

        return $this;
    }

    public function removeSevenByNine(SevenByNine $sevenByNine): static
    {
        if ($this->sevenByNines->removeElement($sevenByNine)) {
            // set the owning side to null (unless already changed)
            if ($sevenByNine->getCreatedBy() === $this) {
                $sevenByNine->setCreatedBy(null);
            }
        }

        return $this;
    }
}
