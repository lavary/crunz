<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Schedule;

use Crunz\Event;
use Crunz\Exception\TaskNotExistException;
use Crunz\Schedule;
use Crunz\Schedule\ScheduleFactory;
use Crunz\Task\TaskNumber;
use PHPUnit\Framework\TestCase;

class ScheduleFactoryTest extends TestCase
{
    /** @test */
    public function singleTaskSchedule()
    {
        $factory = new ScheduleFactory();

        $event1 = new Event(1, 'php -v');
        $event2 = new Event(2, 'php -v');
        $schedule = new Schedule();
        $schedule->events([$event1, $event2]);

        $schedules = $factory->singleTaskSchedule(TaskNumber::fromString('1'), $schedule);
        $firstSchedule = \reset($schedules);

        $this->assertSame([$event1], $firstSchedule->events());
    }

    /** @test */
    public function singleTaskScheduleThrowsExceptionOnWrongTaskNumber()
    {
        $factory = new ScheduleFactory();

        $event1 = new Event(1, 'php -v');
        $schedule = new Schedule();
        $schedule->events([$event1]);

        $this->expectException(TaskNotExistException::class);
        $this->expectExceptionMessage("Task with id '2' not found. Last task id is '1'.");

        $factory->singleTaskSchedule(TaskNumber::fromString('2'), $schedule);
    }
}
