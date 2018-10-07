<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\Console\Command\ScheduleRunCommand;
use Crunz\Event;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\TemporaryFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ScheduleRunCommandTest extends TestCase
{
    /** @test */
    public function forceRunAllTasks()
    {
        $tempFile = new TemporaryFile();
        $filename = $this->createTaskFile($this->taskContent(), $tempFile);

        $mockInput = $this->mockInput(['force' => true, 'task' => null]);
        $mockOutput = $this->createMock(OutputInterface::class);
        $mockTaskCollection = $this->mockTaskCollection($filename);
        $mockEventRunner = $this->mockEventRunner($mockOutput);

        $command = new ScheduleRunCommand(
            $mockTaskCollection,
            $this->mockConfiguration(),
            $mockEventRunner,
            $this->createMock(Timezone::class),
            $this->createMock(Schedule\ScheduleFactory::class)
        );

        $command->run(
            $mockInput,
            $mockOutput
        );
    }

    /** @test */
    public function runSpecificTask()
    {
        $tempFile1 = new TemporaryFile();
        $tempFile2 = new TemporaryFile();
        $filename1 = $this->createTaskFile($this->phpVersionTaskContent(), $tempFile1);
        $filename2 = $this->createTaskFile($this->phpVersionTaskContent(), $tempFile2);

        $mockInput = $this->mockInput(['force' => false, 'task' => '1']);
        $mockOutput = $this->createMock(OutputInterface::class);
        $mockTaskCollection = $this->mockTaskCollection($filename1, $filename2);
        $mockEventRunner = $this->mockEventRunner($mockOutput);

        $command = new ScheduleRunCommand(
            $mockTaskCollection,
            $this->mockConfiguration(),
            $mockEventRunner,
            $this->mockTimezoneProvider(),
            $this->mockScheduleFactory()
        );

        $command->run(
            $mockInput,
            $mockOutput
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Schedule\ScheduleFactory
     */
    private function mockScheduleFactory()
    {
        $mockEvent = $this->createMock(Event::class);
        $mockSchedule = $this->createConfiguredMock(Schedule::class, ['events' => [$mockEvent]]);
        $mockScheduleFactory = $this->createMock(Schedule\ScheduleFactory::class);
        $mockScheduleFactory
            ->expects($this->once())
            ->method('singleTaskSchedule')
            ->willReturn([$mockSchedule])
        ;

        return $mockScheduleFactory;
    }

    private function mockConfiguration(): Configuration
    {
        $mockConfiguration = $this->createMock(Configuration::class);
        $mockConfiguration
            ->method('get')
            ->with('source')
            ->willReturn('')
        ;

        return $mockConfiguration;
    }

    private function mockEventRunner(OutputInterface $output)
    {
        $mockEventRunner = $this->createMock(EventRunner::class);
        $mockEventRunner
            ->expects($this->once())
            ->method('handle')
            ->with(
                $output,
                $this->callback(
                    function ($schedules) {
                        $isArray = \is_array($schedules);
                        $count = \count($schedules);
                        $schedule = \reset($schedules);

                        return $isArray
                            && 1 === $count
                            && $schedule instanceof Schedule
                        ;
                    }
                )
            )
        ;

        return $mockEventRunner;
    }

    private function mockInput(array $options)
    {
        $mockInput = $this->createMock(InputInterface::class);
        $mockInput
            ->method('getOptions')
            ->willReturn($options)
        ;

        return $mockInput;
    }

    /**
     * @return Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTimezoneProvider()
    {
        $timeZone = new \DateTimeZone('UTC');

        return $this->createConfiguredMock(Timezone::class, ['timezoneForComparisons' => $timeZone]);
    }

    private function mockTaskCollection(...$taskFiles)
    {
        $mockTaskCollection = $this->createMock(Collection::class);

        $mocksFileInfo = \array_map(
            function ($taskFile) {
                return $this->createConfiguredMock(\SplFileInfo::class, ['getRealPath' => $taskFile]);
            },
            $taskFiles
        );

        $mockTaskCollection
            ->method('all')
            ->willReturn($mocksFileInfo)
        ;

        return $mockTaskCollection;
    }

    private function createTaskFile($taskContent, TemporaryFile $file)
    {
        $filesystem = new Filesystem();

        $filename = $file->filePath();
        $filesystem->touch($filename);
        $filesystem->dumpFile($filename, $taskContent);

        return $filename;
    }

    private function taskContent()
    {
        return <<<'PHP'
<?php

use Crunz\Schedule;

$schedule = new Schedule();

$schedule->run('php -v')
    ->description('Show PHP version')
    // Always skip
    ->skip(function () {return true;})
;

return $schedule;
PHP;
    }

    private function phpVersionTaskContent()
    {
        return <<<'PHP'
<?php

use Crunz\Schedule;

$schedule = new Schedule();

$schedule->run('php -v')
    ->everyMinute()
    ->description('Show PHP version')
;

return $schedule;
PHP;
    }
}
