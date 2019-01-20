<?php

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Crunz\Finder\FinderInterface;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Path\Path;

class Collection
{
    /** @var Configuration */
    private $configuration;
    /** @var FinderInterface */
    private $finder;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    public function __construct(
        Configuration $configuration,
        FinderInterface $finder,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->configuration = $configuration;
        $this->finder = $finder;
        $this->consoleLogger = $consoleLogger;
    }

    /**
     * @param string $source
     *
     * @return \SplFileInfo[]
     */
    public function all($source)
    {
        $this->consoleLogger
            ->debug("Task source path '<info>$source</info>'");

        if (!\file_exists($source)) {
            return [];
        }

        $suffix = $this->configuration
            ->get('suffix')
        ;
        $sourcePath = Path::create(
            [
                $source,
                "*{$suffix}",
            ]
        );

        $realPath = \realpath($source);
        if (false !== $realPath) {
            $this->consoleLogger
                ->verbose("Realpath for '<info>{$source}</info>' is '<info>{$realPath}</info>'");
        } else {
            $this->consoleLogger
                ->verbose("Realpath resolve for '<info>{$source}</info>' failed.");
        }

        $this->consoleLogger
            ->debug("Task finder pattern '<info>{$sourcePath->toString()}</info>'");

        $tasks = $this->finder
            ->find($sourcePath)
        ;
        $tasksCount = \count($tasks);

        $this->consoleLogger
            ->debug("Found <info>{$tasksCount}</info> task(s) at path '<info>{$source}</info>'");

        return $tasks;
    }

    /**
     * @return \SplFileInfo[]
     *
     * @todo Remove in v2
     */
    public function allLegacyPaths()
    {
        $binRelativePaths = $this->configuration
            ->binRelativeSourcePaths();
        $foundInLegacyPath = false;
        $tasks = [];

        foreach ($binRelativePaths as $binRelativePath) {
            $tasks = $this->all($binRelativePath);

            if (0 === \count($tasks)) {
                continue;
            }

            $foundInLegacyPath = true;
            break;
        }

        if ($foundInLegacyPath) {
            @\trigger_error(
                "Probably you are relying on legacy tasks source recognition which is deprecated. Currently default source for tasks is relative to current working directory, make sure you changed it accordingly in your Cron task. Run your command with '-vvv' to check resolved paths. Legacy behavior will be removed in v2.",
                E_USER_DEPRECATED
            );
        }

        return $tasks;
    }
}
