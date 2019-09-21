<?php

namespace Crunz\Task;

use Crunz\Schedule;

final class Loader implements LoaderInterface
{
    /** @return Schedule[] */
    public function load(\SplFileInfo ...$files)
    {
        $schedules = [];
        foreach ($files as $file) {
            /**
             * Actual "require" is in separated method to make sure
             * local variables are not overwritten by required file
             * See: https://github.com/lavary/crunz/issues/242 for more information.
             */
            $schedule = $this->loadSchedule($file);
            if (!$schedule instanceof Schedule) {
                // @TODO throw exception in v2
                @\trigger_error(
                    "File '{$file->getRealPath()}' didn't return '\Crunz\Schedule' instance, this behavior is deprecated since v1.12 and will result in exception in v2.0+",
                    E_USER_DEPRECATED
                );

                continue;
            }

            $schedules[] = $schedule;
        }

        return $schedules;
    }

    /** @return Schedule */
    private function loadSchedule(\SplFileInfo $file)
    {
        return require $file->getRealPath();
    }
}
