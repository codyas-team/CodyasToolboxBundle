<?php


namespace Codyas\Toolbox\Event;


use Codyas\Toolbox\Model\CrudOperationable;
use Symfony\Contracts\EventDispatcher\Event;

abstract class CrudEntityEvent extends Event
{

	protected $entity;

	public function __construct(CrudOperationable $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * @return CrudOperationable
	 */
	public function getEntity(): CrudOperationable
	{
		return $this->entity;
	}

}