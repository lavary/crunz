<?php

declare(strict_types=1);

namespace Crunz\UserInterface\Cli;

use Crunz\Application\Query\TaskInformation\TaskInformation;
use Crunz\Application\Query\TaskInformation\TaskInformationHandler;
use Crunz\Application\Query\TaskInformation\TaskInformationView;
use Crunz\Task\TaskNumber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DebugTaskCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'task:debug';

    /** @var TaskInformationHandler */
    private $taskInformationHandler;

    public function __construct(TaskInformationHandler $taskInformationHandler)
    {
        $this->taskInformationHandler = $taskInformationHandler;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Shows all information about task')
            ->addArgument(
                'taskNumber',
                InputArgument::REQUIRED,
                'Task number from schedule:list command'
            )
            ->setName(self::$defaultName)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $rawTaskNumber */
        $rawTaskNumber = $input->getArgument('taskNumber');
        $taskNumber = TaskNumber::fromString((string) $rawTaskNumber);
        $taskInformationView = $this->taskInformationHandler
            ->handle(new TaskInformation($taskNumber))
        ;

        $table = $this->createTable($taskInformationView, $output, $taskNumber);
        $table->render();

        return 0;
    }

    private function createTable(
        TaskInformationView $taskInformation,
        OutputInterface $output,
        TaskNumber $taskNumber
    ): Table {
        $command = $taskInformation->command();
        $timeZone = $taskInformation->timeZone();
        $configTimeZone = $taskInformation->configTimeZone();
        $runDates = \array_map(
            static function (\DateTimeImmutable $netRunDate): string {
                return $netRunDate->format('Y-m-d H:i:s e');
            },
            $taskInformation->nextRuns()
        );

        $table = new Table($output);
        $table->setHeaders(
            [
                new TableCell(
                    "Debug information for task '{$taskNumber->asInt()}'",
                    ['colspan' => 2]
                ),
            ]
        );
        $table->addRows(
            [
                [
                    'Command to run',
                    \is_object($command)
                        ? \get_class($command)
                        : $command,
                ],
                [
                    'Description',
                    $taskInformation->description(),
                ],
                [
                    'Prevent overlapping',
                    $taskInformation->preventOverlapping()
                        ? 'Yes'
                        : 'No',
                ],
                new TableSeparator(),
                [
                    'Cron expression',
                    $taskInformation->cronExpression(),
                ],
                [
                    'Comparisons timezone',
                    null !== $timeZone
                        ? "{$timeZone->getName()} (from task)"
                        : "{$configTimeZone->getName()} (from config)",
                ],
                new TableSeparator(),
                [new TableCell('Example run dates', ['colspan' => 2])],
            ]
        );

        $i = 1;
        foreach ($runDates as $date) {
            $table->addRow(
                [
                    "#{$i}",
                    $date,
                ]
            );
            ++$i;
        }

        return $table;
    }
}
