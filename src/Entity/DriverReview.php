<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Base\AbstractEntity;
use App\Repository\DriverReviewRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DriverReviewRepository::class)]
class DriverReview extends AbstractEntity 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'driverReviews')]
    private ?Carpooling $trip = null;

    #[ORM\ManyToOne(inversedBy: 'driverReviews')]
    private ?User $rater = null;

    #[ORM\Column(length: 60, nullable: true)]
    #[Groups(['carpooling.index'])]
    private ?string $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['carpooling.index'])]
    private ?string $comment = null;

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

    public function getRater(): ?User
    {
        return $this->rater;
    }

    public function setRater(?User $rater): static
    {
        $this->rater = $rater;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
