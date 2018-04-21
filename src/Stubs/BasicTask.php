<?php

/*
|--------------------------------------------------------------------------------------
|  Task File
|--------------------------------------------------------------------------------------
|
| This file basically registers a new task to be executed by Crunz
| To get the list of all frequency and constraint method, you may
| go to this link: https://github.com/lavary/crunz#scheduling-frequency-and-constraints
|
*/

use Crunz\Schedule;

$scheduler = new Schedule();

$scheduler->run('DummyCommand')
          ->description('DummyDescription')
          ->in('DummyPath')
          ->DummyFrequency()
          ->DummyConstraint()
          ->preventOverlapping();

return $scheduler;
