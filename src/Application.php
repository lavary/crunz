<?php

declare(strict_types=1);

namespace Crunz;

use Crunz\EnvFlags\EnvFlags;
use Crunz\Path\Path;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class Application extends SymfonyApplication
{
    /**
     * List of commands to register.
     *
     * @var class-string[]
     */
    private const COMMANDS = [
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
        UserInterface\Cli\ClosureRunCommand::class,

        // Debug task command
        \Crunz\UserInterface\Cli\DebugTaskCommand::class,
    ];

    /** @var Container */
    private $container;
    /** @var EnvFlags */
    private $envFlags;

    public function __construct(string $appName, string $appVersion)
    {
        parent::__construct($appName, $appVersion);

        $this->envFlags = new EnvFlags();

        $this->initializeContainer();
        $this->registerDeprecationHandler();

        foreach (self::COMMANDS as $commandClass) {
            /** @var Command $command */
            $command = $this->container
                ->get($commandClass)
            ;

            $this->add($command);
        }
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $output) {
            /** @var OutputInterface $outputObject */
            $outputObject = $this->container
                ->get(OutputInterface::class);

            $output = $outputObject;
        }

        if (null === $input) {
            /** @var InputInterface $inputObject */
            $inputObject = $this->container
                ->get(InputInterface::class);

            $input = $inputObject;
        }

        return parent::run($input, $output);
    }

    private function initializeContainer(): void
    {
        $containerCacheDirWritable = $this->createBaseCacheDirectory();
        $isContainerDebugEnabled = $this->envFlags
            ->isContainerDebugEnabled();

        if ($containerCacheDirWritable) {
            $class = 'CrunzContainer';
            $baseClass = 'Container';
            $cachePath = Path::create(
                [
                    $this->getContainerCacheDir(),
                    "{$class}.php",
                ]
            );
            $cache = new ConfigCache($cachePath->toString(), $isContainerDebugEnabled);

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

            return;
        }

        $containerBuilder = $this->buildContainer();
        $containerBuilder->compile();

        $this->container = $containerBuilder;
    }

    /**
     * @return ContainerBuilder
     *
     * @throws \Exception
     */
    private function buildContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $configDir = Path::create(
            [
                __DIR__,
                '..',
                'config',
            ]
        );

        $phpLoader = new PhpFileLoader($containerBuilder, new FileLocator($configDir->toString()));
        $phpLoader->load('services.php');

        return $containerBuilder;
    }

    private function dumpContainer(
        ConfigCache $cache,
        ContainerBuilder $container,
        string $class,
        string $baseClass
    ): void {
        $dumper = new PhpDumper($container);

        /** @var string $content */
        $content = $dumper->dump(
            [
                'class' => $class,
                'base_class' => $baseClass,
                'file' => $cache->getPath(),
            ]
        );

        $cache->write($content, $container->getResources());
    }

    /**
     * @return bool
     */
    private function createBaseCacheDirectory()
    {
        $baseCacheDir = $this->getBaseCacheDir();

        if (!\is_dir($baseCacheDir)) {
            $makeDirResult = \mkdir(
                $this->getBaseCacheDir(),
                0777,
                true
            );

            return $makeDirResult
                && \is_dir($baseCacheDir)
                && \is_writable($baseCacheDir)
            ;
        }

        return \is_writable($baseCacheDir);
    }

    /**
     * @return string
     */
    private function getBaseCacheDir()
    {
        $baseCacheDir = Path::create(
            [
                \sys_get_temp_dir(),
                '.crunz',
            ]
        );

        return $baseCacheDir->toString();
    }

    /**
     * @return string
     */
    private function getContainerCacheDir()
    {
        $containerCacheDir = Path::create(
            [
                $this->getBaseCacheDir(),
                \get_current_user(),
                $this->getVersion(),
            ]
        );

        return $containerCacheDir->toString();
    }

    private function registerDeprecationHandler(): void
    {
        $isDeprecationHandlerEnabled = $this->envFlags
            ->isDeprecationHandlerEnabled();

        if (!$isDeprecationHandlerEnabled) {
            return;
        }

        /** @var SymfonyStyle $io */
        $io = $this->container
            ->get(SymfonyStyle::class);

        \set_error_handler(
            static function (
                int $errorNumber,
                string $errorString,
                string $file,
                int $line
            ) use ($io): bool {
                $io->block(
                    "{$errorString} File {$file}, line {$line}",
                    'Deprecation',
                    'bg=yellow;fg=black',
                    ' ',
                    true
                );

                return true;
            },
            E_USER_DEPRECATED
        );
    }
}
