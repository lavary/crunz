<?php

declare(strict_types=1);

namespace Crunz\Console\Command;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\LoaderInterface;
use Crunz\Task\TaskNumber;
use Crunz\Task\Timezone;
use Crunz\Task\WrongTaskInstanceException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleRunCommand extends Command
{
    /** @var Collection */
    private $taskCollection;
    /** @var ConfigurationInterface */
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
        ConfigurationInterface $configuration,
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
    protected function configure(): void
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
     *
     * @throws WrongTaskInstanceException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->arguments = $input->getArguments();
        $this->options = $input->getOptions();
        $task = $this->options['task'];
        /** @var \SplFileInfo[] $files */
        $files = $this->taskCollection
            ->all($this->arguments['source']);

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

        $forceRun = \filter_var($this->options['force'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $schedules = \array_map(
            static function (Schedule $schedule) use ($tasksTimezone, $forceRun) {
                if (false === $forceRun) {
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
            static function (Schedule $schedule): bool {
                return \count($schedule->events()) > 0;
            }
        );

        if (!\count($schedules)) {
            $output->writeln('<comment>No event is due!</comment>');

            return 0;
        }

        // Running the events
        $this->eventRunner
            ->handle($output, $schedules)
        ;

        return 0;
    }
}
