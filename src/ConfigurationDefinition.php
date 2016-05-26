<?php

namespace Crunz;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationDefinition implements ConfigurationInterface {
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('tasks');

        $rootNode            
            
            ->children()
                
                ->scalarNode('source')
                    ->cannotBeEmpty()
                    ->info('path to the tasks directory' . PHP_EOL)
                ->end()
                
                ->scalarNode('suffix')
                    ->defaultValue('Tasks.php')
                    ->info('The suffix for filenames' .    PHP_EOL)
                ->end()

                ->booleanNode('log_errors')
                    ->defaultFalse()
                    ->info('Flag for logging errors' .     PHP_EOL)
                ->end()

                ->scalarNode('errors_log_file')
                    ->defaultValue('/dev/null')
                    ->info('Path to errors log' .          PHP_EOL)
                ->end()

                ->booleanNode('log_output')
                    ->defaultFalse()
                    ->info('Flag for logging output' .     PHP_EOL)
                ->end()

                ->scalarNode('output_log_file')
                    ->defaultValue('/dev/null')
                    ->info('Path to output logs' .         PHP_EOL)
                ->end()

            ->end()
        ; 

        return $treeBuilder;
    }

}