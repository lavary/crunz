<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase\EndToEnd\Environment;

use Crunz\Console\Command\ConfigGeneratorCommand;
use Crunz\EnvFlags\EnvFlags;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;
use Crunz\Process\Process;
use Symfony\Component\Yaml\Yaml;

final class Environment
{
    /** @var string */
    private $rootDirectory = '';
    /** @var array */
    private $tasks;
    /** @var array */
    private $config;
    /** @var string */
    private $tasksDirectory;
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var EnvFlags */
    private $envFlags;

    /**
     * @param string[] $tasks
     *
     * @throws \Exception
     */
    public function __construct(
        FilesystemInterface $filesystem,
        Path $tasksDirectory,
        EnvFlags $envFlags,
        array $config = [],
        array $tasks = []
    ) {
        $this->filesystem = $filesystem;
        $this->tasks = $tasks;
        $this->config = $config;
        $this->tasksDirectory = $tasksDirectory->toString();
        $this->envFlags = $envFlags;

        $this->setUp();
    }

    public function __destruct()
    {
        $composerLock = Path::fromStrings('composer.lock');
        $composerJson = Path::fromStrings('composer.json');
        $baseCacheDir = Path::create(
            [
                \sys_get_temp_dir(),
                '.crunz',
            ]
        );

        $this->filesystem
            ->removeDirectory($this->rootDirectory(), [$composerLock, $composerJson]);
        $this->filesystem
            ->removeDirectory($baseCacheDir->toString());
    }

    private function setUp(): void
    {
        $this->createRootDirectory();
        $this->dumpComposerJson();
        $this->composerInstall();
        $this->copyTasks();
        $this->dumpConfig();
    }

    public function runCrunzCommand(string $command, string $cwd = null): Process
    {
        $cwd = !empty($cwd)
            ? $cwd
            : $this->rootDirectory()
        ;
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        // On Windows do not add php binary path
        $phpBinary = $isWindows
            ? ''
            : PHP_BINARY
        ;
        $crunzBinPath = Path::fromStrings(
            $this->rootDirectory(),
            'vendor',
            'bin',
            'crunz'
        );
        $commandParts = [
            $phpBinary,
            $crunzBinPath->toString(),
            $command,
            // Force no ANSI as this break AppVeyor CI builds
            '--no-ansi',
            // Force non-interaction
            '--no-interaction',
        ];
        $fullCommand = \implode(' ', $commandParts);
        $process = $this->createProcess($fullCommand, $cwd);

        $process->setEnv(
            [
                EnvFlags::DEPRECATION_HANDLER_FLAG => '1',
                EnvFlags::CONTAINER_DEBUG_FLAG => '0',
            ]
        );

        $process->start();
        $process->wait();

        return $process;
    }

    /** @return string */
    public function rootDirectory()
    {
        if ('' === $this->rootDirectory) {
            $tempDir = $this->filesystem
                ->tempDir();
            $rootDirectory = Path::fromStrings($tempDir, 'end2end-test-env');

            $this->rootDirectory = $rootDirectory->toString();
        }

        return $this->rootDirectory;
    }

    private function dumpConfig(): void
    {
        if (empty($this->config)) {
            return;
        }

        $configPath = Path::fromStrings(
            $this->rootDirectory,
            ConfigGeneratorCommand::CONFIG_FILE_NAME
        );

        $yamlConfig = Yaml::dump($this->config);

        $this->filesystem
            ->dumpFile($configPath->toString(), $yamlConfig);
    }

    private function copyTasks(): void
    {
        $projectRoot = $this->filesystem
            ->projectRootDirectory();
        $tasksSourceRoot = Path::fromStrings(
            $projectRoot,
            'tests',
            'resources',
            'tasks'
        );
        $destinationRoot = Path::fromStrings(
            $this->rootDirectory(),
            $this->tasksDirectory
        );

        $this->filesystem
            ->createDirectory($destinationRoot->toString());

        foreach ($this->tasks as $task) {
            $fileName = "{$task}.php";
            $sourceTaskPath = Path::fromStrings($tasksSourceRoot->toString(), $fileName);
            $destinationTaskPath = Path::fromStrings($destinationRoot->toString(), $fileName);

            $sourceTaskExists = $this->filesystem
                ->fileExists($sourceTaskPath->toString());

            if (!$sourceTaskExists) {
                throw new \RuntimeException("Task '{$task}' not found at path '{$sourceTaskPath->toString()}'.");
            }

            $this->filesystem
                ->copy($sourceTaskPath->toString(), $destinationTaskPath->toString());
        }
    }

    private function dumpComposerJson(): void
    {
        $composerJson = Path::fromStrings($this->rootDirectory(), 'composer.json');

        $projectDir = $this->filesystem
            ->projectRootDirectory();
        $content = \json_encode(
            [
                'repositories' => [
                    [
                        'type' => 'path',
                        'url' => $projectDir,
                        'options' => [
                            'symlink' => false,
                        ],
                    ],
                ],
                'require' => [
                    'lavary/crunz' => '*@dev',
                ],
            ],
            JSON_PRETTY_PRINT
        );

        if (false === $content) {
            throw new \RuntimeException("Unable to encode 'composer.json' content.");
        }

        $this->filesystem
            ->dumpFile($composerJson->toString(), $content);
    }

    private function composerInstall(): void
    {
        $process = $this->createProcess('composer install -q --no-suggest', $this->rootDirectory());
        $process->startAndWait();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Composer install failed');
        }
    }

    /** @throws \Exception */
    private function createRootDirectory(): void
    {
        $tempDirectory = $this->filesystem
            ->tempDir();

        if (!\is_writable($tempDirectory)) {
            throw new \Exception("Unable to setup environment in system's temp dir '{$tempDirectory}'.");
        }

        $this->filesystem
            ->createDirectory($this->rootDirectory());
    }

    /**
     * @param string      $command
     * @param string|null $cwd
     *
     * @return Process
     */
    private function createProcess($command, $cwd = null)
    {
        return Process::fromStringCommand($command, $cwd);
    }
}
