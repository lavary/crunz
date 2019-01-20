<?php

namespace Crunz\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\TaskNumber;
use Crunz\Task\Timezone;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleRunCommand extends Command
{
    /**
     * Running tasks.
     *
     * @var array
     */
    protected $runningEvents = [];
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

    public function __construct(
        Collection $taskCollection,
        Configuration $configuration,
        EventRunner $eventRunner,
        Timezone $taskTimezone,
        Schedule\ScheduleFactory $scheduleFactory
    ) {
        $this->taskCollection = $taskCollection;
        $this->configuration = $configuration;
        $this->eventRunner = $eventRunner;
        $this->taskTimezone = $taskTimezone;
        $this->scheduleFactory = $scheduleFactory;

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
        $schedules = [];
        $tasksTimezone = $this->taskTimezone
            ->timezoneForComparisons()
        ;

        foreach ($files as $file) {
            $schedule = require $file->getRealPath();
            if (!$schedule instanceof Schedule) {
                continue;
            }

            if (\count($schedule->events())) {
                $schedules[] = $schedule;
            }
        }

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

    /** @param iterable|array $tasks */
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
