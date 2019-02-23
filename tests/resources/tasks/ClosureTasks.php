<?php

declare(strict_types=1);

use Crunz\Schedule;

$x = 153;

$scheduler = new Schedule();
$scheduler
    ->run(
        function () use ($x): void {
            echo 'Closure output' . PHP_EOL;
            echo "Var: {$x}" . PHP_EOL;
        }
    )
    ->description('Closure with output')
    ->everyMinute()
;

return $scheduler;
