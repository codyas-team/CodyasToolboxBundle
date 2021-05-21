<?php

namespace Codyas\Toolbox\Traits;

use Codyas\Toolbox\Model\CrudCustomizable;
use Symfony\Component\Templating\EngineInterface;

trait CrudCustomizableTrait
{

	public function getFormTemplate(  ){
		return '@CodyasToolbox/crud/form.html.twig';
	}

	public function getDetailsTemplate(  ){
		return '@CodyasToolbox/crud/details.html.twig';
	}

	public static function permissions() {
		return [
			'view'      => 'ROLE_ADMIN',
			'create'    => 'ROLE_ADMIN',
			'edit'      => 'ROLE_ADMIN',
			'remove'    => 'ROLE_ADMIN',
		];
	}

	public static function getPermission($accessLevel) {
		if(is_array($accessLevel) && count($accessLevel) === 1){
			$accessLevel = $accessLevel[0];
		}
		$permissions = self::permissions();
		if(!array_key_exists($accessLevel, $permissions)){
			throw new \RuntimeException('Access level not configured!');
		}
		return $permissions[$accessLevel];
	}

	public static function getFilterFormType() {
		return null;
	}

	public static function getFilterFormTemplate() {
		return '@CodyasToolbox/crud/_filter_form.html.twig';
	}

	public static function hasCustomFetchMethod() {
		return false;
	}

	public function hasDependenciesForRemoval(): array{
		return [];
	}

	public function getActionButtons(\Twig\Environment $twig, CrudCustomizable $item) : array {
		return [
			$twig->render('@CodyasToolbox/crud/_table_action_buttons.html.twig',[
				'record' => $item,
				'entity' => get_class($item),
			])
		];
	}

	public function getPersistenceCallbacks() : array {
		return [];
	}

	public function showTableIndex() : bool
	{
		return true;
	}

}