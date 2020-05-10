<?php

namespace Codyas\Toolbox\Model;


use Codyas\Toolbox\Traits\CrudCustomizableTrait;

interface CrudCustomizable
{

	public static function getFilterFormType();

	public static function getFilterFormTemplate();

	public static function hasCustomFetchMethod();

	public static function getPermission( $accessLevel );

	public function getFormTemplate();

	public function getDetailsTemplate();

	public function hasDependenciesForRemoval(): array;

	public function getActionButtons( \Twig\Environment $twig, CrudCustomizable $item ): array;

	public function getPersistenceCallbacks(): array;

	public function showTableIndex(): bool;
}