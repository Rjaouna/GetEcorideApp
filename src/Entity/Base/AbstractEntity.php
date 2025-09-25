<?php
// src/Entity/Base/AbstractEntity.php
namespace App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\TimestampableTrait;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractEntity
{
	use TimestampableTrait;
}
