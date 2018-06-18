<?php

namespace Crunz\Tests\Unit\Console\Command;

use Crunz\Configuration\Configuration;
use Crunz\Console\Command\ScheduleRunCommand;
use Crunz\EventRunner;
use Crunz\Schedule;
use Crunz\Task\Collection;
use Crunz\Task\Timezone;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class ScheduleRunCommandTest extends TestCase
{
    /** @test */
    public function forceRunAllTasks()
    {
        $filename = $this->createTaskFile($this->taskContent());

        $mockInput = $this->mockInput(['force' => true]);
        $mockOutput = $this->createMock(OutputInterface::class);
        $mockTaskCollection = $this->mockTaskCollection($filename);
        $mockEventRunner = $this->mockEventRunner($mockOutput);

        $command = new ScheduleRunCommand(
            $mockTaskCollection,
            $this->createMock(Configuration::class),
            $mockEventRunner,
            $this->createMock(Timezone::class)
        );

        $command->run(
            $mockInput,
            $mockOutput
        );
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
            ->willReturn(
                [
                    'force' => true,
                ]
            )
        ;

        return $mockInput;
    }

    private function mockTaskCollection($taskFile)
    {
        $mockFileInfo = $this->createMock(SplFileInfo::class);
        $mockTaskCollection = $this->createMock(Collection::class);

        $mockTaskCollection
            ->method('all')
            ->willReturn([$mockFileInfo])
        ;
        $mockFileInfo
            ->method('getRealPath')
            ->willReturn($taskFile)
        ;

        return $mockTaskCollection;
    }

    private function createTaskFile($taskContent)
    {
        $filesystem = new Filesystem();

        $filename = $filesystem->tempnam(\sys_get_temp_dir(), 'crunz');
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
}
