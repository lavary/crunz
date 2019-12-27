<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\Console\Command\ScheduleRunCommand;
use Crunz\Event;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\Loader;
use Crunz\Task\LoaderInterface;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\TemporaryFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ScheduleRunCommandTest extends TestCase
{
    /** @test */
    public function forceRunAllTasks(): void
    {
        $tempFile = new TemporaryFile();
        $filename = $this->createTaskFile($this->taskContent(), $tempFile);

        $mockInput = $this->mockInput(
            [
                'force' => true,
                'task' => null,
            ],
            ['source' => '']
        );
        $mockOutput = $this->createMock(OutputInterface::class);
        $mockTaskCollection = $this->mockTaskCollection($filename);
        $mockEventRunner = $this->mockEventRunner($mockOutput);

        $command = new ScheduleRunCommand(
            $mockTaskCollection,
            $this->mockConfiguration(),
            $mockEventRunner,
            $this->createMock(Timezone::class),
            $this->createMock(Schedule\ScheduleFactory::class),
            $this->createTaskLoader()
        );

        $command->run(
            $mockInput,
            $mockOutput
        );
    }

    /** @test */
    public function runSpecificTask(): void
    {
        $tempFile1 = new TemporaryFile();
        $tempFile2 = new TemporaryFile();
        $filename1 = $this->createTaskFile($this->phpVersionTaskContent(), $tempFile1);
        $filename2 = $this->createTaskFile($this->phpVersionTaskContent(), $tempFile2);

        $mockInput = $this->mockInput(
            [
                'force' => false,
                'task' => '1',
            ],
            ['source' => '']
        );
        $mockOutput = $this->createMock(OutputInterface::class);
        $mockTaskCollection = $this->mockTaskCollection($filename1, $filename2);
        $mockEventRunner = $this->mockEventRunner($mockOutput);

        $command = new ScheduleRunCommand(
            $mockTaskCollection,
            $this->mockConfiguration(),
            $mockEventRunner,
            $this->mockTimezoneProvider(),
            $this->mockScheduleFactory(),
            $this->createTaskLoader()
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

    /** @return EventRunner|MockObject */
    private function mockEventRunner(OutputInterface $output): EventRunner
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

    /**
     * @param array<string,bool|string|null> $options
     * @param array<string,bool|string|null> $arguments
     *
     * @return MockObject|InputInterface
     */
    private function mockInput(array $options, array $arguments = []): InputInterface
    {
        $mockInput = $this->createMock(InputInterface::class);
        $mockInput
            ->method('getOptions')
            ->willReturn($options)
        ;
        $mockInput
            ->method('getArguments')
            ->willReturn($arguments)
        ;

        return $mockInput;
    }

    /** @return Timezone|MockObject */
    private function mockTimezoneProvider(): Timezone
    {
        $timeZone = new \DateTimeZone('UTC');

        return $this->createConfiguredMock(Timezone::class, ['timezoneForComparisons' => $timeZone]);
    }

    /** @return Collection|MockObject */
    private function mockTaskCollection(string ...$taskFiles): Collection
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

    private function createTaskFile(string $taskContent, TemporaryFile $file): string
    {
        $filesystem = new Filesystem();

        $filename = $file->filePath();
        $filesystem->touch($filename);
        $filesystem->dumpFile($filename, $taskContent);

        return $filename;
    }

    private function taskContent(): string
    {
        return <<<'PHP'
<?php

use Crunz\Schedule;

$schedule = new Schedule();

$schedule->run('php -v')
    ->description('Show PHP version')
    // Always skip
    ->skip(static function () {return true;})
;

return $schedule;
PHP;
    }

    private function phpVersionTaskContent(): string
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

    private function createTaskLoader(): LoaderInterface
    {
        return new Loader();
    }
}
