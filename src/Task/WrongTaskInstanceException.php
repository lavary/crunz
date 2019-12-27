<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Schedule;

final class WrongTaskInstanceException extends TaskException
{
    /** @param mixed $schedule */
    public static function fromFilePath(\SplFileInfo $filePath, $schedule): self
    {
        $expectedInstance = Schedule::class;
        $type = \is_object($schedule)
            ? \get_class($schedule)
            : \gettype($schedule)
        ;
        $path = $filePath->getRealPath();

        return new self(
            "Task at path '{$path}' returned '{$type}', but '{$expectedInstance}' instance is required."
        );
    }
}
