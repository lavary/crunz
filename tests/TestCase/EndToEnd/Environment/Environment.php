<?php

namespace Crunz\Tests\TestCase\EndToEnd\Environment;

use Crunz\EnvFlags\EnvFlags;
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
    /** @var EnvFlags */
    private $envFlags;

    /**
     * @param string   $name
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
     * @param string      $command
     * @param string|null $cwd
     *
     * @return Process
     */
    public function runCrunzCommand($command, $cwd = null)
    {
        $cwd = !empty($cwd)
            ? $cwd
            : $this->rootDirectory()
        ;
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $windowsEnvsHack = $isWindows && !\method_exists(Process::class, 'inheritEnvironmentVariables');
        // On Windows do not add php binary path
        $phpBinary = $isWindows
            ? ''
            : PHP_BINARY
        ;
        $fullCommand = Path::create(
            [
                "{$phpBinary} {$this->rootDirectory()}",
                'vendor',
                'bin',
                "crunz {$command}",
            ]
        );
        $deprecationHandlerEnabled = $this->envFlags
            ->isDeprecationHandlerEnabled();

        $process = $this->createProcess($fullCommand->toString(), $cwd);

        // @TODO Disable this hack in v2.
        if ($windowsEnvsHack && !$deprecationHandlerEnabled) {
            $this->envFlags
                ->enableDeprecationHandler();
        } else {
            $process->setEnv([EnvFlags::DEPRECATION_HANDLER_FLAG => '1']);
        }

        $process->start();
        $process->wait();

        if ($windowsEnvsHack && !$deprecationHandlerEnabled) {
            $this->envFlags
                ->disableDeprecationHandler();
        }

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
        $process = $this->createProcess('composer install -q --no-suggest', $this->rootDirectory());
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
     * @param string      $command
     * @param string|null $cwd
     *
     * @return Process
     */
    private function createProcess($command, $cwd = null)
    {
        if (\method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command, $cwd);
        } else {
            $process = new Process($command, $cwd);
        }

        if (\method_exists($process, 'inheritEnvironmentVariables')) {
            $process->inheritEnvironmentVariables(true);
        }

        return $process;
    }
}
