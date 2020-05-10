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

trait StatusAwareTrait {

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @Groups({"reservations"})
	 */
	private $status;

	/**
	 * @return mixed
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param mixed $status
	 *
	 * @return StatusAwareTrait
	 */
	public function setStatus( $status ) {
		$this->status = $status;

		return $this;
	}



}