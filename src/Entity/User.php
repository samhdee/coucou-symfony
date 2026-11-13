<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['seance_attendance:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['seance_attendance:read'])]
    private ?string $discordId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['seance_attendance:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['seance_attendance:read'])]
    private ?string $avatar = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, SeanceAttendant>
     */
    #[ORM\OneToMany(targetEntity: SeanceAttendant::class, mappedBy: 'user')]
    private Collection $seanceAttendants;

    public function __construct()
    {
        $this->seanceAttendants = new ArrayCollection();
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
     * @return Collection<int, SeanceAttendant>
     */
    public function getSeanceAttendants(): Collection
    {
        return $this->seanceAttendants;
    }

    public function addSeanceAttendant(SeanceAttendant $seanceAttendant): static
    {
        if (!$this->seanceAttendants->contains($seanceAttendant)) {
            $this->seanceAttendants->add($seanceAttendant);
            $seanceAttendant->setUser($this);
        }

        return $this;
    }

    public function removeSeanceAttendant(SeanceAttendant $seanceAttendant): static
    {
        if ($this->seanceAttendants->removeElement($seanceAttendant)) {
            // set the owning side to null (unless already changed)
            if ($seanceAttendant->getUser() === $this) {
                $seanceAttendant->setUser(null);
            }
        }

        return $this;
    }
}
