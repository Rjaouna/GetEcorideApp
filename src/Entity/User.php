<?php

namespace App\Entity;

use App\Entity\Carpooling;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Entity\Traits\TimestampableTrait;
use Symfony\Component\Serializer\Annotation\Groups;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['profile:read'])]
    private ?int $id = null;

    #[Groups(['profile:read'])]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    /** @var string|null The hashed password */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    // ---------- Vich Uploader ----------
    // Fichier uploadé (non mappé en DB)
    #[Groups(['profile:read'])]
    #[Vich\UploadableField(mapping: 'user_avatar', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['profile:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['profile:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['profile:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['profile:read'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['profile:read'])]
    private ?string $address = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    #[Groups(['profile:read'])]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[ORM\Column(length: 20)]
    #[Groups(['profile:read'])]
    private ?string $pseudo = null;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'owner')]
    #[Groups(['profile:read'])]
    private Collection $vehicles;

    #[ORM\Column(nullable: true)]
    #[Groups(['profile:read'])]
    private ?bool $isLocked = null;

    /**
     * @var Collection<int, Carpooling>
     */
    #[ORM\OneToMany(targetEntity: Carpooling::class, mappedBy: 'driver')]
    #[Groups(['profile:read'])]
    private Collection $carpoolings;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?DriverPreferences $driverPreferences = null;



    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->carpoolings = new ArrayCollection();
    }
    // -----------------------------------

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    // Symfony 7.3 note
    public function __serialize(): array
    {
        $data = (array) $this;
        if ($this->password !== null) {
            $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        }
        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    // ---------- Getters/Setters Vich ----------
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }
    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }
    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeImmutable $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setOwner($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getOwner() === $this) {
                $vehicle->setOwner(null);
            }
        }

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(?bool $isLocked): static
    {
        $this->isLocked = $isLocked;

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
            $carpooling->setDriver($this);
        }

        return $this;
    }

    public function removeCarpooling(Carpooling $carpooling): static
    {
        if ($this->carpoolings->removeElement($carpooling)) {
            // set the owning side to null (unless already changed)
            if ($carpooling->getDriver() === $this) {
                $carpooling->setDriver(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getDriverPreferences(): ?DriverPreferences
    {
        return $this->driverPreferences;
    }

    public function setDriverPreferences(?DriverPreferences $driverPreferences): static
    {
        // unset the owning side of the relation if necessary
        if ($driverPreferences === null && $this->driverPreferences !== null) {
            $this->driverPreferences->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($driverPreferences !== null && $driverPreferences->getUser() !== $this) {
            $driverPreferences->setUser($this);
        }

        $this->driverPreferences = $driverPreferences;

        return $this;
    }
}
