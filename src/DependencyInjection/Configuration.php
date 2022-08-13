<?php

namespace Codyas\Toolbox\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder( 'codyas_toolbox' );

		$treeBuilder->getRootNode()
            ->children()
	            ->arrayNode( 'templating' )
		            ->children()
		                ->scalarNode( 'app_name' )->end()
		                ->scalarNode( 'app_description' )->end()
		                ->scalarNode( 'app_keywords' )->end()
		                ->scalarNode( 'base_template' )->isRequired()->end()
		                ->scalarNode( 'home_path' )->end()
		                ->scalarNode( 'theme' )->defaultValue('dark')->end()
		                ->scalarNode( 'banner' )->defaultValue('bundles/codyastoolbox/templates/mt2/media/header-bg-dark.jpg')->end()
		                ->scalarNode( 'logo' )->end()
						->arrayNode('modules')
							->children()
								->arrayNode('notifications')
									->children()
										->scalarNode( 'enabled' )->end()
										->scalarNode( 'template' )->end()
									->end()
								->end()
							->end()
						->end()

	                ->end()
	            ->end()
	            ->arrayNode( 'menu' )
		            ->children()
		                ->arrayNode( 'items' )
							->arrayPrototype()
								->children()
									->scalarNode('type')->end()
									->scalarNode('text')->end()
									->scalarNode('icon')->end()
									->scalarNode('path')->end()
									->arrayNode('roles')
										->scalarPrototype()->end()
									->end()
									->arrayNode('children')
										->arrayPrototype()
											->children()
												->scalarNode('type')->end()
                                                ->scalarNode('text')->end()
                                                ->scalarNode('icon')->end()
                                                ->scalarNode('path')->end()
												->arrayNode('roles')
													->scalarPrototype()->end()
												->end()
											->end()
										->end()
									->end()
								->end()
							->end()
		                ->end()
	                ->end()
	            ->end()
            ->end();

		return $treeBuilder;
	}

}