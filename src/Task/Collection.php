<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Finder\FinderInterface;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Path\Path;

class Collection
{
    /** @var ConfigurationInterface */
    private $configuration;
    /** @var FinderInterface */
    private $finder;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    public function __construct(
        ConfigurationInterface $configuration,
        FinderInterface $finder,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->configuration = $configuration;
        $this->finder = $finder;
        $this->consoleLogger = $consoleLogger;
    }

    /**
     * @return \SplFileInfo[]
     */
    public function all(string $source): iterable
    {
        $this->consoleLogger
            ->debug("Task source path '<info>${source}</info>'");

        if (!\file_exists($source)) {
            return [];
        }

        $suffix = $this->configuration
            ->get('suffix')
        ;

        $this->consoleLogger
            ->debug("Task finder suffix: '<info>{$suffix}</info>'");

        $realPath = \realpath($source);
        if (false !== $realPath) {
            $this->consoleLogger
                ->verbose("Realpath for '<info>{$source}</info>' is '<info>{$realPath}</info>'");
        } else {
            $this->consoleLogger
                ->verbose("Realpath resolve for '<info>{$source}</info>' failed.");
        }

        $tasks = $this->finder
            ->find(Path::fromStrings($source), $suffix)
        ;
        $tasksCount = \count($tasks);

        $this->consoleLogger
            ->debug("Found <info>{$tasksCount}</info> task(s) at path '<info>{$source}</info>'");

        return $tasks;
    }
}
