<?php

declare(strict_types=1);

use Crunz\Schedule;

$scheduler = new Schedule();
$scheduler
    ->run(
        function (): stdClass {
            \usleep(150 * 1000); // wait 150ms

            echo 'Done', PHP_EOL;

            return new stdClass();
        }
    )
    ->description('Closure with sleep')
    ->preventOverlapping()
    ->everyMinute()
;

return $scheduler;
