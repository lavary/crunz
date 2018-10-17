<?php

declare(strict_types=1);

namespace Crunz;

use Closure;
use Cron\CronExpression;
use Crunz\Clock\Clock;
use Crunz\Clock\ClockInterface;
use Crunz\Exception\NotImplementedException;
use Crunz\Logger\Logger;
use Crunz\Path\Path;
use Crunz\Pinger\PingableInterface;
use Crunz\Pinger\PingableTrait;
use SuperClosure\Serializer;
use Symfony\Component\Process\Process;

/**
 * @method self everyMinute() Run task every minute.
 * @method self everyHour()   Run task every hour.
 * @method self everyDay()    Run task every day.
 * @method self everyMonth()  Run task every month.
 */
class Event implements PingableInterface
{
    use PingableTrait;

    /**
     * Indicates if the command should not overlap itself.
     *
     * @var bool
     */
    public $preventOverlapping = false;

    /**
     * The location that output should be sent to.
     *
     * @var string
     */
    public $output = '/dev/null';

    /**
     * Indicates whether output should be appended.
     *
     * @var bool
     */
    public $shouldAppendOutput = false;

    /**
     * The human readable description of the event.
     *
     * @var string
     */
    public $description;

    /**
     * Event generated output.
     *
     * @var string
     */
    public $outputStream;

    /**
     * Event personal logger instance.
     *
     * @var Logger
     */
    public $logger;
    /**
     * The event's unique identifier.
     *
     * @var string
     */
    protected $id;

    /**
     * The command string.
     *
     * @var string
     */
    protected $command;

    /**
     * Process that runs the event.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * The cron expression representing the event's frequency.
     *
     * @var string
     */
    protected $expression = '* * * * *';

    /**
     * The timezone the date should be evaluated on.
     *
     * @var \DateTimeZone|string
     */
    protected $timezone;

    /**
     * The user the command should run as.
     *
     * @var string
     */
    protected $user;

    /**
     * The array of filter callbacks.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The array of reject callbacks.
     *
     * @var array
     */
    protected $rejects = [];

    /**
     * The array of callbacks to be run before the event is started.
     *
     * @var array
     */
    protected $beforeCallbacks = [];

    /**
     * The array of callbacks to be run after the event is finished.
     *
     * @var array
     */
    protected $afterCallbacks = [];

    /**
     * Current working directory.
     *
     * @var string
     */
    protected $cwd;

    /**
     * Position of cron fields.
     *
     * @var array
     */
    protected $fieldsPosition = [
        'minute' => 1,
        'hour' => 2,
        'day' => 3,
        'month' => 4,
        'week' => 5,
    ];
    /** @var ClockInterface */
    private static $clock;

    /**
     * Create a new event instance.
     *
     * @param string $command
     */
    public function __construct($id, $command)
    {
        $this->command = $command;
        $this->id = $id;
        $this->output = $this->getDefaultOutput();
    }

    /**
     * Handling dynamic frequency methods.
     *
     * @param string $methodName
     * @param array  $params
     *
     * @return $this
     */
    public function __call($methodName, $params)
    {
        \preg_match('/^every([A-Z][a-zA-Z]+)?(Minute|Hour|Day|Month)s?$/', $methodName, $matches);

        if (!\count($matches) || 'Zero' === $matches[1]) {
            throw new \BadMethodCallException();
        }

        $amount = !empty($matches[1]) ? $this->wordToNumber($this->splitCamel($matches[1])) : 1;

        if (!$amount) {
            throw new \BadMethodCallException();
        }

        return $this->every(\mb_strtolower($matches[2]), $amount);
    }

    /**
     * Change the current working directory.
     *
     * @param string $directory
     *
     * @return $this
     */
    public function in($directory)
    {
        $this->cwd = $directory;

        return $this;
    }

    /**
     * Determine if the event's output is sent to null.
     *
     * @return bool
     */
    public function nullOutput()
    {
        return  'NUL' === $this->output || '/dev/null' === $this->output;
    }

    /**
     * Build the command string.
     *
     * @return string
     */
    public function buildCommand()
    {
        $command = '';

        if ($this->cwd) {
            if ($this->user) {
                $command .= $this->sudo($this->user);
            }

            // Support changing drives in Windows
            $cdParameter = $this->isWindows() ? '/d ' : '';
            $andSign = $this->isWindows() ? ' &' : ';';

            $command .= "cd {$cdParameter}{$this->cwd}{$andSign} ";
        }

        if ($this->user) {
            $command .= $this->sudo($this->user);
        }

        $command .= $this->isClosure() ? $this->serializeClosure($this->command) : $this->command;

        return \trim($command, '& ');
    }

    /**
     * Determine whether the passed value is a closure ot not.
     *
     * @return bool
     */
    public function isClosure()
    {
        return \is_object($this->command) && ($this->command instanceof Closure);
    }

    /**
     * Determine if the given event should run based on the Cron expression.
     *
     * @return bool
     */
    public function isDue(\DateTimeZone $timeZone)
    {
        return $this->expressionPasses($timeZone) && $this->filtersPass();
    }

    /**
     * Determine if the filters pass for the event.
     *
     * @return bool
     */
    public function filtersPass()
    {
        $invoker = new Invoker();

        foreach ($this->filters as $callback) {
            if (!$invoker->call($callback)) {
                return false;
            }
        }

        foreach ($this->rejects as $callback) {
            if ($invoker->call($callback)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Start the event execution.
     *
     * @return int
     */
    public function start()
    {
        $this->setProcess(new Process($this->buildCommand()));
        $this->getProcess()->start();

        if ($this->preventOverlapping) {
            $this->lock();
        }

        return $this->getProcess()->getPid();
    }

    /**
     * The Cron expression representing the event's frequency.
     *
     * @param string $expression
     *
     * @return $this
     */
    public function cron($expression)
    {
        $parts = \preg_split(
            '/\s/',
            $expression,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        // @TODO Throw exception in v2
        if (\count($parts) > 5) {
            @\trigger_error(
                'Using cron expression with more than 5 parts is deprecated from v1.9 and will result in exception in v2.0. If you are using dragonmantank/cron-expression package be aware that passing more than five parts to this method will result in exception.',
                E_USER_DEPRECATED
            );
        }

        $this->expression = $expression;

        return $this;
    }

    /**
     * Schedule the event to run hourly.
     *
     * @return $this
     */
    public function hourly()
    {
        return $this->cron('0 * * * *');
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public function daily()
    {
        return $this->cron('0 0 * * *');
    }

    /**
     * Schedule the event to run on a certain date.
     *
     * @param string $date
     *
     * @return $this
     */
    public function on($date)
    {
        $date = \date_parse($date);
        $segments = \array_intersect_key($date, $this->fieldsPosition);

        if ($date['year']) {
            $this->skip(function () use ($date) {
                return (int) \date('Y') !== $date['year'];
            });
        }

        foreach ($segments as $key => $value) {
            if (false !== $value) {
                $this->spliceIntoPosition($this->fieldsPosition[$key], (int) $value);
            }
        }

        return $this;
    }

    /**
     * Schedule the command at a given time.
     *
     * @param string $time
     *
     * @return $this
     */
    public function at($time)
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @param string $time
     *
     * @return $this
     */
    public function dailyAt($time)
    {
        $segments = \explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
                    ->spliceIntoPosition(1, \count($segments) > 1 ? (int) $segments[1] : '0');
    }

    /**
     * Set Working period.
     */
    public function between($from, $to)
    {
        return $this->from($from)
                    ->to($to);
    }

    /**
     * Check if event should be on.
     *
     * @param string $datetime
     */
    public function from($datetime)
    {
        return $this->skip(function () use ($datetime) {
            return $this->notYet($datetime);
        });
    }

    /**
     * Check if event should be off.
     *
     * @param string $datetime
     */
    public function to($datetime)
    {
        return $this->skip(function () use ($datetime) {
            return $this->past($datetime);
        });
    }

    /**
     * Schedule the event to run twice daily.
     *
     * @param int $first
     * @param int $second
     *
     * @return $this
     */
    public function twiceDaily($first = 1, $second = 13)
    {
        $hours = $first . ',' . $second;

        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays()
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    /**
     * Schedule the event to run only on Mondays.
     *
     * @return $this
     */
    public function mondays()
    {
        return $this->days(1);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays()
    {
        return $this->days(2);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays()
    {
        return $this->days(3);
    }

    /**
     * Schedule the event to run only on Thursdays.
     *
     * @return $this
     */
    public function thursdays()
    {
        return $this->days(4);
    }

    /**
     * Schedule the event to run only on Fridays.
     *
     * @return $this
     */
    public function fridays()
    {
        return $this->days(5);
    }

    /**
     * Schedule the event to run only on Saturdays.
     *
     * @return $this
     */
    public function saturdays()
    {
        return $this->days(6);
    }

    /**
     * Schedule the event to run only on Sundays.
     *
     * @return $this
     */
    public function sundays()
    {
        return $this->days(0);
    }

    /**
     * Schedule the event to run weekly.
     *
     * @return $this
     */
    public function weekly()
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     *
     * @return $this
     */
    public function weeklyOn($day, $time = '0:0')
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, $day);
    }

    /**
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public function monthly()
    {
        return $this->cron('0 0 1 * *');
    }

    /**
     * Schedule the event to run quarterly.
     *
     * @return $this
     */
    public function quarterly()
    {
        return $this->cron('0 0 1 */3 *');
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return $this
     */
    public function yearly()
    {
        return $this->cron('0 0 1 1 *');
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param mixed $days
     *
     * @return $this
     */
    public function days($days)
    {
        $days = \is_array($days) ? $days : \func_get_args();

        return $this->spliceIntoPosition(5, \implode(',', $days));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function hour($value)
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(2, \implode(',', $value));
    }

    /**
     * Set minute for the cron job.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function minute($value)
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(1, \implode(',', $value));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function dayOfMonth($value)
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(3, \implode(',', $value));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function month($value)
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(4, \implode(',', $value));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function dayOfWeek($value)
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(5, \implode(',', $value));
    }

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param \DateTimeZone|string $timezone
     *
     * @return $this
     */
    public function timezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Set which user the command should run as.
     *
     * @param string $user
     *
     * @return $this
     */
    public function user($user)
    {
        if ($this->isWindows()) {
            throw new NotImplementedException('Changing user on Windows is not implemented.');
        }

        $this->user = $user;

        return $this;
    }

    /**
     * Do not allow the event to overlap each other.
     *
     * @param string|int $safe_duration
     *
     * @return $this
     */
    public function preventOverlapping()
    {
        $this->preventOverlapping = true;

        // Skip the event if it's locked (processing)
        $this->skip(function () {
            return $this->isLocked();
        });

        // Delete the lock file when the event is completed
        $this->after(function () {
            $lockfile = $this->lockFile();
            if (\file_exists($lockfile)) {
                \unlink($lockfile);
            }
        });

        return $this;
    }

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function when(Closure $callback)
    {
        $this->filters[] = $callback;

        return $this;
    }

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function skip(Closure $callback)
    {
        $this->rejects[] = $callback;

        return $this;
    }

    /**
     * Send the output of the command to a given location.
     *
     * @param string $location
     * @param bool   $append
     *
     * @return $this
     */
    public function sendOutputTo($location, $append = false)
    {
        $this->output = $location;

        $this->shouldAppendOutput = $append;

        return $this;
    }

    /**
     * Append the output of the command to a given location.
     *
     * @param string $location
     *
     * @return $this
     */
    public function appendOutputTo($location)
    {
        return $this->sendOutputTo($location, true);
    }

    /**
     * Register a callback to be called before the operation.
     *
     * @param \Closure $callback
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
     * @param \Closure $callback
     *
     * @return $this
     */
    public function after(Closure $callback)
    {
        return $this->then($callback);
    }

    /**
     * Register a callback to be called after the operation.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function then(Closure $callback)
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * Set the human-friendly description of the event.
     *
     * @param string $description
     *
     * @return $this
     */
    public function name($description)
    {
        return $this->description($description);
    }

    /**
     * Set the event's process.
     *
     * @param \Symfony\Component\Process\Process $process
     *
     * @return $this
     */
    public function setProcess(\Symfony\Component\Process\Process $process = null)
    {
        $this->process = $process;

        return $this;
    }

    /**
     * Return the event's process.
     *
     * @return \Symfony\Component\Process\Process $process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Set the human-friendly description of the event.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Another way to the frequency of the cron job.
     *
     * @param string $unit
     * @param string $value
     *
     * @return $this
     */
    public function every($unit = null, $value = null)
    {
        if (!isset($this->fieldsPosition[$unit])) {
            return $this;
        }

        $value = 1 === $value ? '*' : '*/' . $value;

        return $this->spliceIntoPosition($this->fieldsPosition[$unit], $value)
                    ->applyMask($unit);
    }

    /**
     * Return the event's command.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the summary of the event for display.
     *
     * @return string
     */
    public function getSummaryForDisplay()
    {
        if (\is_string($this->description)) {
            return $this->description;
        }

        return $this->buildCommand();
    }

    /**
     * Get the command for display.
     *
     * @return string
     */
    public function getCommandForDisplay()
    {
        return $this->isClosure() ? 'object(Closure)' : $this->buildCommand();
    }

    /**
     * Get the Cron expression for the event.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set the event's command.
     *
     * @param string $command
     *
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Return the event's command.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Return the current working directory.
     *
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->cwd;
    }

    /**
     * Return event's full output.
     *
     * @return string
     */
    public function getOutputStream()
    {
        return $this->outputStream;
    }

    /**
     * Return all registered before callbacks.
     *
     * @return array
     */
    public function beforeCallbacks()
    {
        return $this->beforeCallbacks;
    }

    /**
     * Return all registered after callbacks.
     *
     * @return array
     */
    public function afterCallbacks()
    {
        return $this->afterCallbacks;
    }

    /**
     * Check if another instance of the event is still running.
     *
     * @return bool
     */
    public function isLocked()
    {
        $pid = $this->lastPid();
        $hasPid = (null !== $pid);

        // No POSIX on Windows
        if ($this->isWindows()) {
            return $hasPid;
        }

        return ($hasPid && \posix_getsid($pid)) ? true : false;
    }

    /**
     * Get the last process Id of the event.
     *
     * @return int
     */
    public function lastPid()
    {
        $lock_file = $this->lockFile();

        return \file_exists($lock_file) ? (int) \trim(\file_get_contents($lock_file)) : null;
    }

    /**
     * Get the lock file path for the task.
     *
     * @return string
     */
    public function lockFile()
    {
        return \rtrim(\sys_get_temp_dir(), '/') . '/crunz-' . \md5($this->buildCommand());
    }

    /**
     * Get the default output depending on the OS.
     *
     * @return string
     */
    protected function getDefaultOutput()
    {
        return (DIRECTORY_SEPARATOR === '\\') ? 'NUL' : '/dev/null';
    }

    /**
     * Add sudo to the command.
     *
     * @param string $user
     *
     * @return string
     */
    protected function sudo($user)
    {
        return "sudo -u {$user} ";
    }

    /**
     * Convert closure to an executable command.
     *
     * @param string $closure
     *
     * @return string
     */
    protected function serializeClosure($closure)
    {
        $closure = (new Serializer())->serialize($closure);
        $serializedClosure = \http_build_query([$closure]);
        $crunzRoot = Path::create([\getcwd(), 'crunz']);

        return PHP_BINARY . " {$crunzRoot->toString()} closure:run {$serializedClosure}";
    }

    /**
     * Determine if the Cron expression passes.
     *
     * @return bool
     */
    protected function expressionPasses(\DateTimeZone $timeZone)
    {
        $now = $this->getClock()
            ->now();
        $now = $now->setTimezone($timeZone);

        if ($this->timezone) {
            $now = $now->setTimezone(new \DateTimeZone($this->timezone));
        }

        return CronExpression::factory($this->expression)->isDue($now->format('Y-m-d H:i:s'));
    }

    /**
     * Check if time hasn't arrived.
     *
     * @param string $time
     *
     * @return bool
     */
    protected function notYet($datetime)
    {
        return \time() < \strtotime($datetime);
    }

    /**
     * Check if the time has passed.
     *
     * @param string $time
     *
     * @return bool
     */
    protected function past($datetime)
    {
        return \time() > \strtotime($datetime);
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @param int    $position
     * @param string $value
     *
     * @return $this
     */
    protected function spliceIntoPosition($position, $value)
    {
        $segments = \explode(' ', $this->expression);

        $segments[$position - 1] = $value;

        return $this->cron(\implode(' ', $segments));
    }

    /**
     * Mask a cron expression.
     *
     * @param string $unit
     *
     * @return string
     */
    protected function applyMask($unit)
    {
        $cron = \explode(' ', $this->expression);
        $mask = ['0', '0', '1', '1', '*', '*'];
        $fpos = $this->fieldsPosition[$unit] - 1;

        \array_splice($cron, 0, $fpos, \array_slice($mask, 0, $fpos));

        return $this->cron(\implode(' ', $cron));
    }

    /**
     * Lock the event.
     *
     * @param \Crunz\Event $event
     *
     * @return string
     */
    protected function lock()
    {
        \file_put_contents($this->lockFile(), $this->process->getPid());
    }

    /** @return ClockInterface */
    private function getClock()
    {
        if (null === self::$clock) {
            self::$clock = new Clock();
        }

        return self::$clock;
    }

    private function splitCamel($text)
    {
        $pattern = '/(?<=[a-z])(?=[A-Z])/x';
        $segments = \preg_split($pattern, $text);

        return \mb_strtolower(
            \implode(
                $segments,
                ' '
            )
        );
    }

    private function isWindows()
    {
        $osCode = \mb_substr(
            PHP_OS,
            0,
            3
        );

        return 'WIN' === $osCode;
    }

    private function wordToNumber($text)
    {
        $data = \strtr(
            $text,
            [
                'zero' => '0',
                'a' => '1',
                'one' => '1',
                'two' => '2',
                'three' => '3',
                'four' => '4',
                'five' => '5',
                'six' => '6',
                'seven' => '7',
                'eight' => '8',
                'nine' => '9',
                'ten' => '10',
                'eleven' => '11',
                'twelve' => '12',
                'thirteen' => '13',
                'fourteen' => '14',
                'fifteen' => '15',
                'sixteen' => '16',
                'seventeen' => '17',
                'eighteen' => '18',
                'nineteen' => '19',
                'twenty' => '20',
                'thirty' => '30',
                'forty' => '40',
                'fourty' => '40',
                'fifty' => '50',
                'sixty' => '60',
                'seventy' => '70',
                'eighty' => '80',
                'ninety' => '90',
                'hundred' => '100',
                'thousand' => '1000',
                'million' => '1000000',
                'billion' => '1000000000',
                'and' => '',
            ]
        );

        // Coerce all tokens to numbers
        $parts = \array_map(
            function ($val) {
                return (float) $val;
            },
            \preg_split('/[\s-]+/', $data)
        );

        $tmp = null;
        $sum = 0;
        $last = null;

        foreach ($parts as $part) {
            if (null !== $tmp) {
                if ($tmp > $part) {
                    if ($last >= 1000) {
                        $sum += $tmp;
                        $tmp = $part;
                    } else {
                        $tmp += $part;
                    }
                } else {
                    $tmp *= $part;
                }
            } else {
                $tmp = $part;
            }

            $last = $part;
        }

        return $sum + $tmp;
    }
}
