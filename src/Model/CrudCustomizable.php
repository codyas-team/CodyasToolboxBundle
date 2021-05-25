<?php

namespace Codyas\Toolbox\Model;


use Codyas\Toolbox\Traits\CrudCustomizableTrait;
use Twig\Environment;

interface CrudCustomizable
{

	public static function getFilterFormType();

	public static function getFilterFormTemplate();

	public static function hasCustomFetchMethod();

	public static function getPermission( $accessLevel );

	public static function isReadOnly(): bool;

	public function getFormTemplate();

	public function getDetailsTemplate();

	public function hasDependenciesForRemoval(): array;

	public function getActionButtons( Environment $twig, CrudCustomizable $item ): array;

	public function getPersistenceCallbacks(): array;

	public function showTableIndex(): bool;

	public static function displayRowNumber(): bool;

	public static function displayActionButtons(): bool;
}