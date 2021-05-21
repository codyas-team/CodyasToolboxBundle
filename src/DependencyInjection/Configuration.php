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
		                ->scalarNode( 'home_path' )->end()
		                ->scalarNode( 'logo' )->end()
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