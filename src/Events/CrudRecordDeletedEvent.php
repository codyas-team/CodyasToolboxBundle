<?php


namespace Codyas\Toolbox\Events;


use Codyas\Toolbox\Model\CrudOperationable;
use Symfony\Contracts\EventDispatcher\Event;

class CrudRecordDeletedEvent extends Event
{

	private $record;

	public function __construct( CrudOperationable $record )
	{
		$this->record = $record;
	}

	public function getRecord(): CrudOperationable
	{
		return $this->record;
	}


}