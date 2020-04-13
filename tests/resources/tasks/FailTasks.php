<?php

declare(strict_types=1);

use Crunz\Schedule;

$scheduler = new Schedule();
$scheduler
    ->run(
        function (): void {
            throw new RuntimeException('Task failed.');
        }
    )
    ->description('Task that will fail')
    ->everyMinute()
;

return $scheduler;
