<?php

namespace Crunz\Tests\TestCase\EndToEnd\Environment;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;
use Symfony\Component\Process\Process;
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

    /**
     * @param string   $name
     * @param string[] $tasks
     *
     * @throws \Exception
     */
    public function __construct(
        FilesystemInterface $filesystem,
        Path $tasksDirectory,
        array $config = [],
        array $tasks = []
    ) {
        $this->filesystem = $filesystem;
        $this->tasks = $tasks;
        $this->config = $config;
        $this->tasksDirectory = $tasksDirectory->toString();

        $this->setUp();
    }

    public function __destruct()
    {
        $composerLock = Path::fromStrings('composer.lock');
        $composerJson = Path::fromStrings('composer.json');

        $this->filesystem
            ->removeDirectory($this->rootDirectory(), [$composerLock, $composerJson]);
    }

    private function setUp()
    {
        $this->createRootDirectory();
        $this->dumpComposerJson();
        $this->composerInstall();
        $this->copyTasks();
        $this->dumpConfig();
    }

    /**
     * @param string $command
     * @param string $cwd
     *
     * @return Process
     */
    public function runCrunzCommand($command, $cwd = '')
    {
        $cwd = '' !== $cwd
            ? $cwd
            : $this->rootDirectory()
        ;
        $originalCwd = \getcwd();
        $fullCommand = Path::create(
            [
                PHP_BINARY . ' ' . $this->rootDirectory(),
                'vendor',
                'bin',
                "crunz {$command}",
            ]
        );

        \chdir($cwd);

        $process = $this->createProcess($fullCommand->toString());
        $process->setEnv(['CRUNZ_DEPRECATION_HANDLER' => '1']);
        $process->start();
        $process->wait();

        \chdir($originalCwd);

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

    private function dumpConfig()
    {
        if (empty($this->config)) {
            return;
        }

        $configPath = Path::fromStrings(
            $this->rootDirectory,
            'crunz.yml'
        );

        $yamlConfig = Yaml::dump($this->config);

        $this->filesystem
            ->dumpFile($configPath->toString(), $yamlConfig);
    }

    private function copyTasks()
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

    private function dumpComposerJson()
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

        $this->filesystem
            ->dumpFile($composerJson->toString(), $content);
    }

    private function composerInstall()
    {
        $process = new Process("cd {$this->rootDirectory()}; composer install -q --no-suggest");
        $process->start();
        $process->wait();

        if (0 !== $process->getExitCode()) {
            throw new \RuntimeException('Composer install failed');
        }
    }

    /** @throws \Exception */
    private function createRootDirectory()
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
     * @param string $command
     *
     * @return Process
     */
    private function createProcess($command)
    {
        if (\method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command);
        } else {
            $process = new Process($command);
        }

        if (\method_exists($process, 'inheritEnvironmentVariables')) {
            $process->inheritEnvironmentVariables(true);
        }

        return $process;
    }
}
