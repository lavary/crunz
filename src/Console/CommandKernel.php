<?php

namespace Crunz\Console;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class CommandKernel extends SymfonyApplication
{
    /**
     * List of commands to register
     *
     * @var array
     */
    const COMMANDS = [
        // This command starts the event runner (vendor/bin/crunz schedule:run)
        // It takes an optional argument which is the source directory for tasks
        // If the argument is not provided, the default in the configuratrion file
        // will be considered as the source path
        \Crunz\Console\Command\ScheduleRunCommand::class,

        // This command (vendor/bin/schedule:list) lists the scheduled events in different task files
        // Just like schedule:run it gets the :source argument
        \Crunz\Console\Command\ScheduleListCommand::class,

        // This command generates a task from the command-line
        // This is often useful when you want to create a task file and start
        // adding tasks to it.
        \Crunz\Console\Command\TaskGeneratorCommand::class,

        // The modify the configuration, the user's own copy should be modified
        // This command creates a configuration file in Crunz installation directory
        \Crunz\Console\Command\ConfigGeneratorCommand::class,
        
        // This command is used by Crunz itself for running serialized closures
        // It accepts an argument which is the serialized form of the closure to run.
        \Crunz\Console\Command\ClosureRunCommand::class,
    ];

    /** @var Container */
    private $container;

    /**
     * Instantiate the class
     *
     */
    public function __construct($appName, $appVersion)
    {
        parent::__construct($appName, $appVersion);

        $this->initializeContainer();
        
        foreach(self::COMMANDS as $commandClass) {
            $command = $this->container
                ->get($commandClass)
            ;

            $this->add($command);
        }
    }

    private function initializeContainer()
    {
        $class = 'CrunzContainer';
        $baseClass = 'Container';
        $cache = new ConfigCache(
            implode(
                DIRECTORY_SEPARATOR,
                [
                    $this->getCacheDir(),
                    "{$class}.php"
                ]
            ),
            true
        );

        if (!$cache->isFresh()) {
            $containerBuilder = $this->buildContainer();
            $containerBuilder->compile();

            $this->dumpContainer(
                $cache,
                $containerBuilder,
                $class,
                $baseClass
            );
        }

        require_once $cache->getPath();

        $this->container = new $class();
    }

    /**
     * @return ContainerBuilder
     * @throws \Exception
     */
    private function buildContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $loader = new XmlFileLoader($containerBuilder, new FileLocator(CRUNZ_ROOT . '/config'));
        $loader->load('services.xml');

        return $containerBuilder;
    }

    private function dumpContainer(
        ConfigCache $cache,
        ContainerBuilder $container,
        $class,
        $baseClass
    ) {
        $dumper = new PhpDumper($container);

        $content = $dumper->dump(
            [
                'class' => $class,
                'base_class' => $baseClass,
                'file' => $cache->getPath()
            ]
        );

        $cache->write($content, $container->getResources());
    }

    private function getCacheDir()
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                \sys_get_temp_dir(),
                'crunz'
            ]
        );
    }
}
