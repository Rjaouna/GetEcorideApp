<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
	#[ORM\Column(type: 'datetime_immutable')]
	private ?\DateTimeImmutable $createdAt = null;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $updatedAt = null;

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
}
