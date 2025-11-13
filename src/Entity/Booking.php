<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?Carpooling $trip = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?User $passager = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrip(): ?Carpooling
    {
        return $this->trip;
    }

    public function setTrip(?Carpooling $trip): static
    {
        $this->trip = $trip;

        return $this;
    }

    public function getPassager(): ?User
    {
        return $this->passager;
    }

    public function setPassager(?User $passager): static
    {
        $this->passager = $passager;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCancelAt(): ?\DateTimeImmutable
    {
        return $this->cancelAt;
    }

    public function setCancelAt(?\DateTimeImmutable $cancelAt): static
    {
        $this->cancelAt = $cancelAt;

        return $this;
    }
}
