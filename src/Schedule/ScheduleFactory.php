<?php

declare(strict_types=1);

namespace Crunz\Schedule;

use Crunz\Event;
use Crunz\Exception\TaskNotExistException;
use Crunz\Schedule;
use Crunz\Task\TaskNumber;

class ScheduleFactory
{
    /**
     * @return Schedule[]
     *
     * @throws TaskNotExistException
     */
    public function singleTaskSchedule(TaskNumber $taskNumber, Schedule ...$schedules): array
    {
        $event = $this->singleTask($taskNumber, ...$schedules);

        $schedule = new Schedule();
        $schedule->events([$event]);

        return [$schedule];
    }

    /** @throws TaskNotExistException */
    public function singleTask(TaskNumber $taskNumber, Schedule ...$schedules): Event
    {
        $events = \array_map(
            static function (Schedule $schedule) {
                return $schedule->events();
            },
            $schedules
        );

        $flattenEvents = \array_merge(...$events);

        if (!isset($flattenEvents[$taskNumber->asArrayIndex()])) {
            $tasksCount = \count($flattenEvents);
            throw new TaskNotExistException(
                "Task with id '{$taskNumber->asInt()}' was not found. Last task id is '{$tasksCount}'."
            );
        }

        return $flattenEvents[$taskNumber->asArrayIndex()];
    }
}
