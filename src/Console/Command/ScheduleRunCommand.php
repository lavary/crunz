<?php

namespace Crunz\Console\Command;

use Crunz\Configuration\NonSingletonConfiguration;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ScheduleRunCommand extends Command
{
    /**
     * Running tasks.
     *
     * @var array
     */
    protected $runningEvents = [];
    /** @var Finder */
    private $finder;
    /** @var Collection */
    private $taskCollection;
    /** @var NonSingletonConfiguration */
    private $configuration;

    public function __construct(
        Finder $finder,
        Collection $taskCollection,
        NonSingletonConfiguration $configuration
    ) {
        $this->finder = $finder;
        $this->taskCollection = $taskCollection;
        $this->configuration = $configuration;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $sourcePath = $this->configuration
            ->get('source')
        ;

        $this->setName('schedule:run')
            ->setDescription('Starts the event runner.')
            ->setDefinition(
                [
                    new InputArgument(
                        'source',
                        InputArgument::OPTIONAL,
                        'The source directory for collecting the task files.',
                        generate_path($sourcePath)
                    ),
                ]
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
        $files = $this->taskCollection
            ->all($this->arguments['source'])
        ;

        if (!count($files)) {
            $output->writeln('<comment>No task found! Please check your source path.</comment>');

            return 0;
        }

        // List of schedules
        $schedules = [];

        foreach ($files as $file) {
            $schedule = require $file->getRealPath();
            if (!$schedule instanceof Schedule) {
                continue;
            }

            // We keep the events which are due and dismiss the rest.
            $schedule->events($schedule->dueEvents());

            if (count($schedule->events())) {
                $schedules[] = $schedule;
            }
        }

        if (!count($schedules)) {
            $output->writeln('<comment>No event is due!</comment>');

            return 0;
        }

        // Running the events
        (new EventRunner())
            ->handle($output, $schedules);
    }
}
