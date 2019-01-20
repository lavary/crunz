<?php

use Crunz\Schedule;

$scheduler = new Schedule();

$scheduler
    ->run('php -v')
    ->description('PHP version')
    ->everyMinute()
;

return $scheduler;
