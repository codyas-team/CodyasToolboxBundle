<?php

namespace Codyas\Toolbox\Model;


interface CrudOperationable {

	public function renderDataTableRow( array $dependencies ) : array ;
	public function getFormType(  ) : string ;

	public static function getPermission($accessLevel)  ;

}