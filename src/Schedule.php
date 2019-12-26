<?php

declare(strict_types=1);

namespace Crunz;

use Crunz\Pinger\PingableInterface;
use Crunz\Pinger\PingableTrait;

class Schedule implements PingableInterface
{
    use PingableTrait;

    /**
     * All of the events on the schedule.
     *
     * @var Event[]
     */
    protected $events = [];

    /**
     * The array of callbacks to be run before all the events are finished.
     *
     * @var \Closure[]
     */
    protected $beforeCallbacks = [];

    /**
     * The array of callbacks to be run after all the event is finished.
     *
     * @var \Closure[]
     */
    protected $afterCallbacks = [];

    /**
     * The array of callbacks to call in case of an error.
     *
     * @var \Closure[]
     */
    protected $errorCallbacks = [];

    /**
     * Add a new event to the schedule object.
     *
     * @param string|\Closure $command
     * @param string[]        $parameters
     *
     * @return \Crunz\Event
     */
    public function run($command, array $parameters = [])
    {
        if (\is_string($command) && \count($parameters)) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $this->events[] = $event = new Event($this->id(), $command);

        return $event;
    }

    /**
     * Register a callback to be called before the operation.
     *
     * @return $this
     */
    public function before(\Closure $callback)
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called after the operation.
     *
     * @return $this
     */
    public function after(\Closure $callback)
    {
        return $this->then($callback);
    }

    /**
     * Register a callback to be called after the operation.
     *
     * @return $this
     */
    public function then(\Closure $callback)
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to call in case of an error.
     *
     * @return $this
     */
    public function onError(\Closure $callback)
    {
        $this->errorCallbacks[] = $callback;

        return $this;
    }

    /**
     * Return all registered before callbacks.
     *
     * @return \Closure[]
     */
    public function beforeCallbacks()
    {
        return $this->beforeCallbacks;
    }

    /**
     * Return all registered after callbacks.
     *
     * @return \Closure[]
     */
    public function afterCallbacks()
    {
        return $this->afterCallbacks;
    }

    /**
     * Return all registered error callbacks.
     *
     * @return \Closure[]
     */
    public function errorCallbacks()
    {
        return $this->errorCallbacks;
    }

    /**
     * Get or set the events of the schedule object.
     *
     * @param Event[] $events
     *
     * @return Event[]
     */
    public function events(array $events = null)
    {
        if (null !== $events) {
            return $this->events = $events;
        }

        return $this->events;
    }

    /**
     * Get all of the events on the schedule that are due.
     *
     * @return Event[]
     */
    public function dueEvents(\DateTimeZone $timeZone)
    {
        return \array_filter(
            $this->events,
            static function (Event $event) use ($timeZone) {
                return $event->isDue($timeZone);
            }
        );
    }

    /**
     * Dismiss an event after it is finished.
     *
     * @param int $key
     *
     * @return $this
     */
    public function dismissEvent($key)
    {
        unset($this->events[$key]);

        return $this;
    }

    /**
     * Generate a unique task id.
     *
     * @return string
     */
    protected function id()
    {
        while (true) {
            $id = \uniqid('crunz', true);
            if (!\array_key_exists($id, $this->events)) {
                return $id;
            }
        }
    }

    /**
     * Compile parameters for a command.
     *
     * @param string[] $parameters
     *
     * @return string
     */
    protected function compileParameters(array $parameters)
    {
        return \implode(
            ' ',
            \array_map(
                function ($value, $key) {
                    return \is_numeric($key) ? $value : "{$key}=" . (\is_numeric($value) ? $value : ProcessUtils::escapeArgument($value));
                },
                $parameters,
                \array_keys($parameters)
            )
        );
    }
}
