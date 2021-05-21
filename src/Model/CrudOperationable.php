<?php

namespace Codyas\Toolbox\Model;



interface CrudOperationable {

	public function renderDataTableRow( RowRendererArguments $arguments ) : array ;
	public function getFormType(  ) : string ;

	public static function getPermission($accessLevel)  ;

}