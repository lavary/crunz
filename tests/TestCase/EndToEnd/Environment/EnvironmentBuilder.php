<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase\EndToEnd\Environment;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;

final class EnvironmentBuilder
{
    /** @var array<string> */
    private $tasks = [];
    /** @var array<string,mixed> */
    private $config = [];
    /** @var Path */
    private $taskDirectory;
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->taskDirectory = Path::fromStrings('tasks');
        $this->filesystem = $filesystem;
    }

    public function addTask(string $taskName): self
    {
        $this->tasks[] = $taskName;

        return $this;
    }

    public function changeTaskDirectory(Path $path): self
    {
        $this->taskDirectory = $path;

        return $this;
    }

    /** @param array<string,mixed> $config */
    public function withConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function createEnvironment(): Environment
    {
        return new Environment(
            $this->filesystem,
            $this->taskDirectory,
            $this->config,
            $this->tasks
        );
    }
}
