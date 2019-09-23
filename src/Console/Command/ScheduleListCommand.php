<?php

namespace Crunz\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\Task\Collection;
use Crunz\Task\LoaderInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleListCommand extends Command
{
    /** @var Configuration */
    private $configuration;
    /** @var Collection */
    private $taskCollection;
    /** @var LoaderInterface */
    private $taskLoader;

    public function __construct(
        Configuration $configuration,
        Collection $taskCollection,
        LoaderInterface $taskLoader
    ) {
        $this->configuration = $configuration;
        $this->taskCollection = $taskCollection;
        $this->taskLoader = $taskLoader;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('schedule:list')
            ->setDescription('Displays the list of scheduled tasks.')
            ->setDefinition(
                [
                    new InputArgument(
                        'source',
                        InputArgument::OPTIONAL,
                        'The source directory for collecting the tasks.',
                        $this->configuration
                            ->getSourcePath()
                    ),
                ]
            )
            ->setHelp('This command displays the scheduled tasks in a tabular format.');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();
        $this->arguments = $input->getArguments();
        $tasks = $this->fallbackTaskSource(
            $this->taskCollection
                ->all($this->arguments['source'])
        );

        if (!\count($tasks)) {
            $output->writeln('<comment>No task found!</comment>');

            return 0;
        }

        $table = new Table($output);
        $table->setHeaders(
            [
                '#',
                'Task',
                'Expression',
                'Command to Run',
            ]
        );
        $row = 0;

        $schedules = $this->taskLoader
            ->load(...\array_values($tasks))
        ;

        foreach ($schedules as $schedule) {
            $events = $schedule->events();
            foreach ($events as $event) {
                $table->addRow(
                    [
                        ++$row,
                        $event->description,
                        $event->getExpression(),
                        $event->getCommandForDisplay(),
                    ]
                );
            }
        }

        $table->render();

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
