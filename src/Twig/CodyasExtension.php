<?php

namespace Codyas\Toolbox\Twig;

use App\Entity\Promotion;
use Codyas\Toolbox\Exception\ConfigurationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CodyasExtension extends AbstractExtension
{
	private $requestStack, $kernel, $bundleConfig;

	public function __construct( RequestStack $requestStack, KernelInterface $kernel, $bundleConfig )
	{
		$this->requestStack = $requestStack;
		$this->kernel       = $kernel;
		$this->bundleConfig = $bundleConfig;
	}

	public function getFilters(): array
	{
		return [
			new TwigFilter( 'web_path', [ $this, 'getWebDirectoryPath' ] ),
			new TwigFilter( 'canonicalize', [ $this, 'canonicalize' ] ),
			new TwigFilter( 'base64_encode', [ $this, 'base64Encode' ] ),
			new TwigFilter( 'get_url', [ $this, 'getAppDomain' ], [ 'is_safe' => [ 'html' ] ] ),
		];
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction( 'get_menu', [ $this, 'getMenu' ] ),
			new TwigFunction( 'is_menu_active', [ $this, 'isMenuActive' ] ),
			new TwigFunction( 'is_menu_parent_active', [ $this, 'isMenuParentActive' ] ),
			new TwigFunction( 'instanceof', [ $this, 'isInstanceOf' ] ),
			new TwigFunction( 'is_array', [ $this, 'isArray' ] ),
			new TwigFunction( '_call', [ $this, 'callStaticFunction' ] ),
			new TwigFunction( 'codyas_tb_config', [ $this, 'getConfig' ] ),
			new TwigFunction( 'codyas_mt2_modules', [ $this, 'getMT2Modules' ] ),

		];
	}

	public function getMenu()
	{
		try
		{
			return $this->bundleConfig['menu'];
		} catch ( \ErrorException $errorException )
		{
			throw new ConfigurationException( "There is no configured system menu." );
		}


	}

	public function isMenuActive( $menu ): bool
    {
		$request = $this->requestStack->getCurrentRequest();

		$currentRoute = $request->attributes->get( '_route' );

		$isActive = array_key_exists( 'path', $menu ) && $menu['path'] && $menu['path'] === $currentRoute;

		if ( array_key_exists( 'children', $menu ) && $menu['children'] )
		{
			foreach ( $menu['children'] as $child )
			{
				if ( $child['path'] === $currentRoute )
				{
					return true;
				}
			}
		}

		return $isActive;
	}

	public function isMenuParentActive( $children ): bool
    {
		foreach ( $children as $child )
		{
			if ( array_key_exists( 'children', $child ) )
			{
				$isActive = $this->isMenuParentActive( $child['children'] );
				if ( $isActive )
				{
					return $isActive;
				};
			}
			if (
				array_key_exists( 'path', $child )
				&& $this->isMenuActive( $child )
			)
			{
				return true;
			}
		}

		return false;
	}

	public function callStaticFunction( $class, $function, $arguments )
	{
		return call_user_func( array( $class, $function ), $arguments );
	}

	public function isInstanceOf( $object, $class )
	{
		return $object instanceof $class;
	}

	public function isArray( $test )
	{
		return is_array( $test );
	}

	public function getWebDirectoryPath( $path )
	{
		return $this->kernel->getProjectDir() . '/public/' . $path;
	}

	public function canonicalize( string $string )
	{
		return strtolower( str_replace( ' ', '-', $string ) );
	}

	public function getAppDomain( $resource )
	{
		return sprintf( 'https://recargas.cubaimagen.net%s', $resource );
	}

	public function getConfig( $configKey, $silent = false )
	{
		if ( ! array_key_exists( 'templating', $this->bundleConfig ) || ! $this->bundleConfig['templating'] )
		{
			if ( $silent )
			{
				return null;
			}
			throw new ConfigurationException( "Templating options are not defined." );
		}

		$templatingOptions = $this->bundleConfig['templating'];
		if ( array_key_exists( $configKey, $templatingOptions ) && $templatingOptions[ $configKey ] )
		{
			return $templatingOptions[ $configKey ];
		}

		if ( $silent )
		{
			return null;
		}
		throw new ConfigurationException( "Unable to get a valid configuration for request key {$configKey}" );
	}

	public function getMT2Modules() : array
	{
		return [
			'activities' => [
				'enabled' => false,
                'header_template' => ''
			],
			'notifications' => [
				'enabled' => false,
                'header_template' => ''
			],
			'chat' => [
				'enabled' => false,
                'header_template' => ''
			],
			'profile' => [
				'enabled' => true,
				'header_template' => '@CodyasToolbox/templates/mt2/partials/_profile_dropdown.html.twig'
			],
			'quick_links' => [
				'enabled' => false,
                'header_template' => ''
			],
		];
	}

	public function base64Encode( $unencoded )
	{
		return base64_encode($unencoded);
	}
}
