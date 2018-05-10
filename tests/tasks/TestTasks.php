<?php

use Crunz\Schedule;

$schedule = new Schedule();

$schedule->run('php -v')
    ->description('Show PHP version')
    ->everyMinute()
;

// IMPORTANT: You must return the schedule object
return $schedule;
