<?php

namespace Crunz;

use Symfony\Component\Process\ProcessUtils;

class Schedule
{
    /**
     * All of the events on the schedule.
     *
     * @var array
     */
    protected $events = [];

    /**
     * An alias for the command() method
     *
     * @param  string $command
     * @param  array  $parameters
     * @return \Crunz\Event
     */
     public function run($command, array $parameters = array())
     {   
        return $this->command($command, $parameters);
     }    

    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Crunz\Event
     */
    public function command($command, array $parameters = array())
    {

        return $this->exec($command, $parameters);
    }

    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Illuminate\Console\Scheduling\Event
     */
    public function exec($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $this->events[] = $event = new Event($command);

        return $event;
    }

    /**
     * Compile parameters for a command.
     *
     * @param  array  $parameters
     * @return string
     */
    protected function compileParameters(array $parameters)
    {
        return collect($parameters)->map(function ($value, $key) {
            return is_numeric($key) ? $value : $key . '=' . (is_numeric($value) ? $value : ProcessUtils::escapeArgument($value));
        })->implode(' ');
    }

    /**
     * Get all of the events on the schedule.
     *
     * @return array
     */
    public function events()
    {
        return $this->events;
    }

     /**
     * Get all of the events on the schedule that are due.
     *
     * @param  \Crunz\Invoker $invoker
     * @return array
     */
    public function dueEvents(Invoker $invoker)
    {
        
        return array_filter($this->events, function ($event) use ($invoker) {
            return $event->isDue($invoker);
        });
    }
}
