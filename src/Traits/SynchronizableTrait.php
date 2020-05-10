<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 8/15/2018
 * Time: 7:44 PM
 */

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

trait SynchronizableTrait {

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Gedmo\Timestampable(on="create")
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Gedmo\Timestampable(on="update")
	 */
	private $updatedAt;

	public function getCreatedAt(): ?\DateTime {
		return $this->createdAt;
	}

	public function setCreatedAt( \DateTime $createdAt ) {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getUpdatedAt(): ?\DateTime {
		return $this->updatedAt;
	}

	public function setUpdatedAt( \DateTime $updatedAt ) {
		$this->updatedAt = $updatedAt;

		return $this;
	}


}