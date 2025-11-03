<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Base\AbstractEntity;
use App\Repository\DriverPreferencesRepository;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: DriverPreferencesRepository::class)]
class DriverPreferences extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['preference:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'driverPreferences', cascade: ['persist', 'remove'])]
    #[Groups(['preference:read'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['preference:read', 'carpooling.index'])]
    private ?bool $smokingAllowed = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['preference:read', 'carpooling.index'])]
    private ?bool $petsAllowed = null;

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

    public function isSmokingAllowed(): ?bool
    {
        return $this->smokingAllowed;
    }

    public function setSmokingAllowed(?bool $smokingAllowed): static
    {
        $this->smokingAllowed = $smokingAllowed;

        return $this;
    }

    public function isPetsAllowed(): ?bool
    {
        return $this->petsAllowed;
    }

    public function setPetsAllowed(?bool $petsAllowed): static
    {
        $this->petsAllowed = $petsAllowed;

        return $this;
    }
}
