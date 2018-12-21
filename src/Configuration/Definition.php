<?php

namespace Crunz\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Definition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('crunz');

        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('crunz');
        }

        $rootNode

            ->children()

                ->scalarNode('source')
                    ->cannotBeEmpty()
                    ->info('path to the tasks directory' . PHP_EOL)
                ->end()

                ->scalarNode('suffix')
                    ->defaultValue('Tasks.php')
                    ->info('The suffix for filenames' . PHP_EOL)
                ->end()

                ->scalarNode('timezone')
                    ->info('Timezone used to calculate task run date')
                ->end()

                ->booleanNode('log_errors')
                    ->defaultFalse()
                    ->info('Flag for logging errors' . PHP_EOL)
                ->end()

                ->scalarNode('errors_log_file')
                    ->defaultValue('/dev/null')
                    ->info('Path to errors log' . PHP_EOL)
                ->end()

                ->booleanNode('log_output')
                    ->defaultFalse()
                    ->info('Flag for logging output' . PHP_EOL)
                ->end()

                ->scalarNode('output_log_file')
                    ->defaultValue('/dev/null')
                    ->info('Path to output logs' . PHP_EOL)
                ->end()

                ->scalarNode('log_allow_line_breaks')
                    ->defaultFalse()
                    ->info('Flag for line breaks in logs' . PHP_EOL)
                ->end()

                ->scalarNode('email_output')
                    ->defaultFalse()
                    ->info('Email the event\'s output' . PHP_EOL)
                ->end()

                ->scalarNode('email_errors')
                    ->defaultFalse()
                    ->info('Notify by email in case of an error' . PHP_EOL)
                ->end()

                ->arrayNode('mailer')

                    ->children()

                        ->scalarNode('transport')
                        ->info('The type the Swift transporter' . PHP_EOL)
                        ->end()

                        ->arrayNode('recipients')
                        ->prototype('scalar')->end()
                        ->info('List of the email recipients' . PHP_EOL)
                        ->end()

                        ->scalarNode('sender_name')
                        ->info('The sender name' . PHP_EOL)
                        ->end()

                        ->scalarNode('sender_email')
                        ->info('The sender email' . PHP_EOL)
                        ->end()

                    ->end()

                ->end()

                ->arrayNode('smtp')

                    ->children()

                        ->scalarNode('host')
                        ->info('SMTP host' . PHP_EOL)
                        ->end()

                        ->scalarNode('port')
                        ->info('SMTP port' . PHP_EOL)
                        ->end()

                        ->scalarNode('username')
                        ->info('SMTP username' . PHP_EOL)
                        ->end()

                        ->scalarNode('password')
                        ->info('SMTP password' . PHP_EOL)
                        ->end()

                        ->scalarNode('encryption')
                        ->info('SMTP encryption' . PHP_EOL)
                        ->end()

                    ->end()

                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
