<?php

namespace Crunz\Tests\TestCase\EndToEnd\Environment;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;

final class EnvironmentBuilder
{
    /** @var array */
    private $tasks = [];
    /** @var array */
    private $config = [];
    /** @var Path */
    private $taskDirectory;
    /** @var FilesystemInterface */
    private $filesystem;

    /** @param string $name */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->taskDirectory = Path::fromStrings('tasks');
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $taskName
     *
     * @return $this
     */
    public function addTask($taskName)
    {
        $this->tasks[] = $taskName;

        return $this;
    }

    public function changeTaskDirectory(Path $path)
    {
        $this->taskDirectory = $path;

        return $this;
    }

    public function withConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /** @return Environment */
    public function createEnvironment()
    {
        return new Environment(
            $this->filesystem,
            $this->taskDirectory,
            $this->config,
            $this->tasks
        );
    }
}
