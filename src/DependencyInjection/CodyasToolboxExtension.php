<?php

namespace Codyas\Toolbox\DependencyInjection;

use Codyas\Toolbox\Twig\AdminExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CodyasToolboxExtension extends Extension
{

	/**
	 * {@inheritdoc}
	 */
	public function load( array $configs, ContainerBuilder $container ): void
    {
		$configuration = new Configuration();
		$config        = $this->processConfiguration( $configuration, $configs );
		$container->setParameter('codyas_toolbox_config', $config);
		$loader        = new YamlFileLoader(
			$container,
			new FileLocator( __DIR__ . '/../Resources/config' )
		);
		$loader->load( 'services.yaml' );

		$this->addAnnotatedClassesToCompile( [
			'Codyas\\Toolbox\\Controller\\CrudController',
		] );
	}


}