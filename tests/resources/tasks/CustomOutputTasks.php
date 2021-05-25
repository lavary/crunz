<?php

declare(strict_types=1);

use Crunz\Schedule;

$scheduler = new Schedule();

$scheduler
    ->run('php --help')
    ->description('Custom logging test')
    ->everyMinute()
    ->appendOutputTo('custom.log')
;

return $scheduler;
