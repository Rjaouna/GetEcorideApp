<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Base\AbstractEntity;
use App\Repository\CarpoolingRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CarpoolingRepository::class)]
class Carpooling extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['carpooling.index'])]
    private ?User $driver = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(length: 50)]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?string $deparatureCity = null;

    #[ORM\Column(length: 50)]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?string $arrivalCity = null;

    #[ORM\Column]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?\DateTimeImmutable $deparatureAt = null;

    #[ORM\Column]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?\DateTimeImmutable $arrivalAt = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?int $seatsTotal = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?int $seatsAvaible = null;

    #[ORM\Column]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?float $price = null;

    #[ORM\Column(length: 50)]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['profile:read', 'carpooling.index'])]
    private ?bool $ecoTag = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'covoiturages')]
    #[Groups(['carpooling.index'])]
    private Collection $participants;

    /**
     * @var Collection<int, DriverReview>
     */
    #[ORM\OneToMany(targetEntity: DriverReview::class, mappedBy: 'trip')]
    #[Groups(['carpooling.index'])]
    private Collection $driverReviews;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'trip')]
    private Collection $bookings;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->driverReviews = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getDeparatureCity(): ?string
    {
        return $this->deparatureCity;
    }

    public function setDeparatureCity(string $deparatureCity): static
    {
        $this->deparatureCity = $deparatureCity;

        return $this;
    }

    public function getArrivalCity(): ?string
    {
        return $this->arrivalCity;
    }

    public function setArrivalCity(string $arrivalCity): static
    {
        $this->arrivalCity = $arrivalCity;

        return $this;
    }

    public function getDeparatureAt(): ?\DateTimeImmutable
    {
        return $this->deparatureAt;
    }

    public function setDeparatureAt(\DateTimeImmutable $deparatureAt): static
    {
        $this->deparatureAt = $deparatureAt;

        return $this;
    }

    public function getArrivalAt(): ?\DateTimeImmutable
    {
        return $this->arrivalAt;
    }

    public function setArrivalAt(\DateTimeImmutable $arrivalAt): static
    {
        $this->arrivalAt = $arrivalAt;

        return $this;
    }

    public function getSeatsTotal(): ?int
    {
        return $this->seatsTotal;
    }

    public function setSeatsTotal(int $seatsTotal): static
    {
        $this->seatsTotal = $seatsTotal;

        return $this;
    }

    public function getSeatsAvaible(): ?int
    {
        return $this->seatsAvaible;
    }

    public function setSeatsAvaible(?int $seatsAvaible): static
    {
        $this->seatsAvaible = $seatsAvaible;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isEcoTag(): ?bool
    {
        return $this->ecoTag;
    }

    public function setEcoTag(bool $ecoTag): static
    {
        $this->ecoTag = $ecoTag;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    /**
     * @return Collection<int, DriverReview>
     */
    public function getDriverReviews(): Collection
    {
        return $this->driverReviews;
    }

    public function addDriverReview(DriverReview $driverReview): static
    {
        if (!$this->driverReviews->contains($driverReview)) {
            $this->driverReviews->add($driverReview);
            $driverReview->setTrip($this);
        }

        return $this;
    }

    public function removeDriverReview(DriverReview $driverReview): static
    {
        if ($this->driverReviews->removeElement($driverReview)) {
            // set the owning side to null (unless already changed)
            if ($driverReview->getTrip() === $this) {
                $driverReview->setTrip(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setTrip($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getTrip() === $this) {
                $booking->setTrip(null);
            }
        }

        return $this;
    }
}
