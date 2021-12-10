<?php

namespace Codyas\Toolbox\Model;


interface CrudCancelable {
	public function setStatus( string $status );
	public function delete( string $status ): void;
}