<?php

declare(strict_types=1);

namespace Crunz\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\WrongTaskInstanceException;
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

    public function __construct(Configuration $configuration, Collection $taskCollection)
    {
        $this->configuration = $configuration;
        $this->taskCollection = $taskCollection;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
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

    /** {@inheritdoc}
     * @throws WrongTaskInstanceException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();
        $this->arguments = $input->getArguments();
        /** @var \SplFileInfo[] $tasks */
        $tasks = $this->taskCollection
            ->all($this->arguments['source']);

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

        foreach ($tasks as $taskFile) {
            $schedule = require $taskFile->getRealPath();
            if (!$schedule instanceof Schedule) {
                throw WrongTaskInstanceException::fromFilePath($taskFile, $schedule);
                continue;
            }

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
}
