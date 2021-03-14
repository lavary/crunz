<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Application\Query\TaskInformation;

use Crunz\Application\Query\TaskInformation\TaskInformation;
use Crunz\Application\Query\TaskInformation\TaskInformationHandler;
use Crunz\Event;
use Crunz\Infrastructure\Dragonmantank\CronExpression\DragonmantankCronExpressionFactory;
use Crunz\Schedule\ScheduleFactory;
use Crunz\Task\Collection;
use Crunz\Task\LoaderInterface;
use Crunz\Task\TaskNumber;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\FakeConfiguration;
use PHPUnit\Framework\TestCase;

final class TaskInformationHandlerTest extends TestCase
{
    /**
     * @test
     * @dataProvider taskInformationProvider
     */
    public function handle_returns_task_information(
        Event $event,
        string $expectedCommand,
        string $expectedDescription = '',
        bool $expectedPreventOverlapping = false,
        string $expectedCronExpression = '* * * * *',
        ?\DateTimeZone $expectedEventTimeZone = null
    ): void {
        $comparisonsTimeZone = new \DateTimeZone('UTC');
        $taskInformationHandler = $this->createHandler($event, $comparisonsTimeZone);
        $taskInformation = $taskInformationHandler->handle(
            new TaskInformation(
                TaskNumber::fromString('1')
            )
        );

        $this->assertSame($expectedCommand, $taskInformation->command());
        $this->assertSame($expectedDescription, $taskInformation->description());
        $this->assertSame($expectedPreventOverlapping, $taskInformation->preventOverlapping());
        $this->assertSame($expectedCronExpression, $taskInformation->cronExpression());
        $this->assertSame($comparisonsTimeZone, $taskInformation->configTimeZone());
        $this->assertEquals($expectedEventTimeZone, $taskInformation->timeZone());
    }

    /** @return iterable<string, array> */
    public function taskInformationProvider(): iterable
    {
        $id = (string) \random_int(1, 9999);
        yield 'simple task' => [
            new Event($id, 'php -v'),
            'php -v',
        ];

        $event = new Event($id, 'php -i');
        $event->description('Some description');
        yield 'with description' => [
            $event,
            'php -i',
            'Some description',
        ];

        $event = new Event($id, 'php -i');
        $event->preventOverlapping();
        yield 'with prevent overlapping' => [
            $event,
            'php -i',
            '',
            true,
        ];

        $event = new Event($id, 'php -i');
        $event
            ->everyFiveMinutes()
            ->weekdays()
        ;
        yield 'with cron expression' => [
            $event,
            'php -i',
            '',
            false,
            '*/5 * * * 1-5',
        ];

        $timeZone = new \DateTimeZone('Europe/Warsaw');
        $event = new Event($id, 'php -i');
        $event->timezone($timeZone);
        yield 'with custom comparisons timezone' => [
            $event,
            'php -i',
            '',
            false,
            '* * * * *',
            $timeZone,
        ];

        $event = new Event($id, 'php -i');
        $event->timezone('Europe/Warsaw');
        yield 'with string custom comparisons timezone' => [
            $event,
            'php -i',
            '',
            false,
            '* * * * *',
            new \DateTimeZone('Europe/Warsaw'),
        ];
    }

    private function createHandler(Event $event, \DateTimeZone $comparisonsTimeZone): TaskInformationHandler
    {
        $taskCollectionMock = $this->createMock(Collection::class);
        $taskCollectionMock
            ->method('all')
            ->willReturn([])
        ;
        $scheduleFactoryMock = $this->createMock(ScheduleFactory::class);
        $scheduleFactoryMock
            ->method('singleTask')
            ->willReturn($event)
        ;
        $timezoneProviderMock = $this->createMock(Timezone::class);
        $timezoneProviderMock
            ->method('timezoneForComparisons')
            ->willReturn($comparisonsTimeZone)
        ;

        return new TaskInformationHandler(
            $timezoneProviderMock,
            new FakeConfiguration(),
            $taskCollectionMock,
            $this->createMock(LoaderInterface::class),
            $scheduleFactoryMock,
            new DragonmantankCronExpressionFactory()
        );
    }
}
