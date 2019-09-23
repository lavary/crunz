<?php

namespace Crunz\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\LoaderInterface;
use Crunz\Task\TaskNumber;
use Crunz\Task\Timezone;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleRunCommand extends Command
{
    /** @var Collection */
    private $taskCollection;
    /** @var Configuration */
    private $configuration;
    /** @var EventRunner */
    private $eventRunner;
    /** @var Timezone */
    private $taskTimezone;
    /** @var Schedule\ScheduleFactory */
    private $scheduleFactory;
    /** @var LoaderInterface */
    private $taskLoader;

    public function __construct(
        Collection $taskCollection,
        Configuration $configuration,
        EventRunner $eventRunner,
        Timezone $taskTimezone,
        Schedule\ScheduleFactory $scheduleFactory,
        LoaderInterface $taskLoader
    ) {
        $this->taskCollection = $taskCollection;
        $this->configuration = $configuration;
        $this->eventRunner = $eventRunner;
        $this->taskTimezone = $taskTimezone;
        $this->scheduleFactory = $scheduleFactory;
        $this->taskLoader = $taskLoader;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('schedule:run')
            ->setDescription('Starts the event runner.')
            ->setDefinition(
                [
                    new InputArgument(
                        'source',
                        InputArgument::OPTIONAL,
                        'The source directory for collecting the task files.',
                        $this->configuration
                            ->getSourcePath()
                    ),
                ]
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Run all tasks regardless of configured run time.'
            )
            ->addOption(
                'task',
                't',
                InputOption::VALUE_REQUIRED,
                'Which task to run. Provide task number from <info>schedule:list</info> command.',
                null
            )
           ->setHelp('This command starts the Crunz event runner.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->arguments = $input->getArguments();
        $this->options = $input->getOptions();
        $task = $this->options['task'];
        $files = $this->fallbackTaskSource(
            $this->taskCollection
                ->all($this->arguments['source'])
        );

        if (!\count($files)) {
            $output->writeln('<comment>No task found! Please check your source path.</comment>');

            return 0;
        }

        // List of schedules
        $schedules = $this->taskLoader
            ->load(...\array_values($files))
        ;
        $tasksTimezone = $this->taskTimezone
            ->timezoneForComparisons()
        ;

        // Is specified task should be invoked?
        if (\is_string($task)) {
            $schedules = $this->scheduleFactory
                ->singleTaskSchedule(TaskNumber::fromString($task), ...$schedules);
        }

        $schedules = \array_map(
            function (Schedule $schedule) use ($tasksTimezone) {
                if (false === $this->options['force']) {
                    // We keep the events which are due and dismiss the rest.
                    $schedule->events(
                        $schedule->dueEvents(
                            $tasksTimezone
                        )
                    );
                }

                return $schedule;
            },
            $schedules
        );
        $schedules = \array_filter(
            $schedules,
            function (Schedule $schedule) {
                return \count($schedule->events());
            }
        );

        if (!count($schedules)) {
            $output->writeln('<comment>No event is due!</comment>');

            return 0;
        }

        // Running the events
        $this->eventRunner
            ->handle($output, $schedules)
        ;

        return 0;
    }

    /** @param \SplFileInfo[] $tasks */
    private function fallbackTaskSource($tasks)
    {
        $tasksCount = \count($tasks);
        if (0 !== $tasksCount) {
            return $tasks;
        }

        return $this->taskCollection
            ->allLegacyPaths();
    }
}
