<?php

declare(strict_types=1);

namespace Crunz\Application\Query\TaskInformation;

use Crunz\Task\TaskNumber;

final class TaskInformation
{
    /** @var TaskNumber */
    private $taskNumber;

    public function __construct(TaskNumber $taskNumber)
    {
        $this->taskNumber = $taskNumber;
    }

    public function taskNumber(): TaskNumber
    {
        return $this->taskNumber;
    }
}
