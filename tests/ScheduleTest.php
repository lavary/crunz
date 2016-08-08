<?php

use Crunz\Schedule;

class ScheduleTest extends PHPUnit_Framework_TestCase  {
    
    public function testRun()
    {
        $escape     = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $escapeReal = '\\' === DIRECTORY_SEPARATOR ? '\\"' : '"';

        $schedule   = new Schedule;
        
        $schedule->run('path/to/command');
        $schedule->run('path/to/command -f --foo="bar"');
        $schedule->run('path/to/command', ['-f']);
        $schedule->run('path/to/command', ['--foo' => 'bar']);
        $schedule->run('path/to/command', ['-f', '--foo' => 'bar']);
        $schedule->run('path/to/command', ['--title' => 'A "real" test']);

        $events = $schedule->events();
        
        $this->assertEquals('path/to/command',                                $events[0]->getCommand());
        $this->assertEquals('path/to/command -f --foo="bar"',                 $events[1]->getCommand());
        $this->assertEquals('path/to/command -f',                             $events[2]->getCommand());
        $this->assertEquals("path/to/command --foo={$escape}bar{$escape}",    $events[3]->getCommand());
        $this->assertEquals("path/to/command -f --foo={$escape}bar{$escape}", $events[4]->getCommand());
 
        $this->assertEquals("path/to/command --title={$escape}A {$escapeReal}real{$escapeReal} test{$escape}", $events[5]->getCommand());
    }

}