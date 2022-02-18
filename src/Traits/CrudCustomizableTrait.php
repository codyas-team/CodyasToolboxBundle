<?php

namespace Codyas\Toolbox\Traits;

use Codyas\Toolbox\Event\ConfirmationMessageConfig;
use Codyas\Toolbox\Model\CrudCustomizable;
use Codyas\Toolbox\Model\OperationMessageConfig;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Templating\EngineInterface;

trait CrudCustomizableTrait
{
	/**
	 * @Ignore()
	 */
	public function getFormTemplate()
	{
		return '@CodyasToolbox/crud/form.html.twig';
	}

	/**
	 * @Ignore()
	 */
	public function getDetailsTemplate()
	{
		return '@CodyasToolbox/crud/details.html.twig';
	}

	/**
	 * @Ignore()
	 */
	public static function permissions()
	{
		return [
			'view'   => 'ROLE_ADMIN',
			'create' => 'ROLE_ADMIN',
			'edit'   => 'ROLE_ADMIN',
			'remove' => 'ROLE_ADMIN',
		];
	}

	/**
	 * @Ignore()
	 */
	public static function getPermission( $accessLevel )
	{
		if ( is_array( $accessLevel ) && count( $accessLevel ) === 1 )
		{
			$accessLevel = $accessLevel[0];
		}
		$permissions = self::permissions();
		if ( ! array_key_exists( $accessLevel, $permissions ) )
		{
			throw new \RuntimeException( 'Access level not configured!' );
		}

		return $permissions[ $accessLevel ];
	}

	/**
	 * @Ignore()
	 */
	public static function getFilterFormType()
	{
		return null;
	}

	/**
	 * @Ignore()
	 */
	public static function getFilterFormTemplate()
	{
		return '@CodyasToolbox/crud/_filter_form.html.twig';
	}

	/**
	 * @Ignore()
	 */
	public static function hasCustomFetchMethod()
	{
		return false;
	}

	/**
	 * @Ignore()
	 */
	public function hasDependenciesForRemoval(): array
	{
		return [];
	}

	/**
	 * @Ignore()
	 */
	public function getActionButtons( \Twig\Environment $twig, CrudCustomizable $item ): array
	{
		return [
			$twig->render( '@CodyasToolbox/crud/_table_action_buttons.html.twig', [
				'record' => $item,
				'entity' => get_class( $item ),
			] )
		];
	}

	/**
	 * @Ignore()
	 */
	public function getPersistenceCallbacks(): array
	{
		return [];
	}

	/**
	 * @Ignore()
	 */
	public function showTableIndex(): bool
	{
		return true;
	}

	/**
	 * @Ignore()
	 */
	public static function isReadOnly(): bool
	{
		return false;
	}

	/**
	 * @Ignore()
	 */
	public static function displayRowNumber(): bool
	{
		return true;
	}

	/**
	 * @Ignore()
	 */
	public static function displayActionButtons(): bool
	{
		return true;
	}

	/**
	 * @Ignore()
	 */
	public static function getConfirmationMsgConfig(): OperationMessageConfig
	{
		return ( new OperationMessageConfig() )
			->setOnRegistrationTitle( 'Success' )
			->setOnRegistrationBody( 'Record was processed' );
	}

	public function isTurboEnabled(): bool
	{
		return false;
	}

	public function turboNextActionUrl( UrlGeneratorInterface $urlGenerator ): ?string
	{
		return null;
	}

	public static function getEntityIdentifier(): string
	{
		return self::class;
	}

	public static function hasCustomIdentifier(): bool
	{
		return false;
	}

	public function delete() : void
	{
		$this->deletedAt = new \DateTime( 'now' );
	}

}