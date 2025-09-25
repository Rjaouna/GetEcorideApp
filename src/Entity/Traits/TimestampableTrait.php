<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

trait TimestampableTrait
{
	#[ORM\Column(type: 'datetime_immutable')]
	private ?\DateTimeImmutable $createdAt = null;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $updatedAt = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(onDelete: 'SET NULL', nullable: true)]
	private ?User $createdBy = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(onDelete: 'SET NULL', nullable: true)]
	private ?User $updatedBy = null;

	#[ORM\PrePersist]
	public function initTimestamps(): void
	{
		$now = new \DateTimeImmutable();
		if ($this->createdAt === null) {
			$this->createdAt = $now;
		}
		$this->updatedAt = $now;
	}

	#[ORM\PreUpdate]
	public function touchUpdatedAt(): void
	{
		$this->updatedAt = new \DateTimeImmutable();
	}

	public function getCreatedAt(): ?\DateTimeImmutable
	{
		return $this->createdAt;
	}

	public function getUpdatedAt(): ?\DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(?\DateTimeImmutable $d): self
	{
		$this->updatedAt = $d;
		return $this;
	}

	public function getCreatedBy(): ?User
	{
		return $this->createdBy;
	}

	public function setCreatedBy(?User $user): self
	{
		$this->createdBy = $user;
		return $this;
	}

	public function getUpdatedBy(): ?User
	{
		return $this->updatedBy;
	}

	public function setUpdatedBy(?User $user): self
	{
		$this->updatedBy = $user;
		return $this;
	}
}
