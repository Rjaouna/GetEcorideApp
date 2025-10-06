<?php

namespace App\Entity;

use App\Entity\Base\AbstractEntity;
use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling.index'])]
    private ?string $plateNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $firstRegistrationAt = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling.index'])]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling.index'])]
    private ?string $model = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $seats = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isElectric = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, Carpooling>
     */
    #[ORM\OneToMany(targetEntity: Carpooling::class, mappedBy: 'vehicle')]
    private Collection $carpoolings;

    public function __construct()
    {
        $this->carpoolings = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    public function setPlateNumber(string $plateNumber): static
    {
        $this->plateNumber = $plateNumber;

        return $this;
    }

    public function getFirstRegistrationAt(): ?\DateTimeImmutable
    {
        return $this->firstRegistrationAt;
    }

    public function setFirstRegistrationAt(\DateTimeImmutable $firstRegistrationAt): static
    {
        $this->firstRegistrationAt = $firstRegistrationAt;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): static
    {
        $this->seats = $seats;

        return $this;
    }

    public function isElectric(): ?bool
    {
        return $this->isElectric;
    }

    public function setIsElectric(?bool $isElectric): static
    {
        $this->isElectric = $isElectric;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Carpooling>
     */
    public function getCarpoolings(): Collection
    {
        return $this->carpoolings;
    }

    public function addCarpooling(Carpooling $carpooling): static
    {
        if (!$this->carpoolings->contains($carpooling)) {
            $this->carpoolings->add($carpooling);
            $carpooling->setVehicle($this);
        }

        return $this;
    }

    public function removeCarpooling(Carpooling $carpooling): static
    {
        if ($this->carpoolings->removeElement($carpooling)) {
            // set the owning side to null (unless already changed)
            if ($carpooling->getVehicle() === $this) {
                $carpooling->setVehicle(null);
            }
        }

        return $this;
    }
}
