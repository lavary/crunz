<?php

declare(strict_types=1);

namespace Crunz;

use Closure;
use Cron\CronExpression;
use Crunz\Application\Service\ClosureSerializerInterface;
use Crunz\Clock\Clock;
use Crunz\Clock\ClockInterface;
use Crunz\Exception\NotImplementedException;
use Crunz\Infrastructure\Opis\Closure\OpisClosureSerializer;
use Crunz\Logger\Logger;
use Crunz\Path\Path;
use Crunz\Pinger\PingableInterface;
use Crunz\Pinger\PingableTrait;
use Crunz\Process\Process;
use Crunz\Task\TaskException;
use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Exception\InvalidArgumentException;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\StoreInterface;

class Event implements PingableInterface
{
    use PingableTrait;

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
     * @var string|null
     */
    public $description;

    /**
     * Event generated output.
     *
     * @var string|null
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
     * @var string|int
     */
    protected $id;

    /** @var string|Closure */
    protected $command;

    /**
     * Process that runs the event.
     *
     * @var Process
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
     * @var \Closure[]
     */
    protected $filters = [];

    /**
     * The array of reject callbacks.
     *
     * @var \Closure[]
     */
    protected $rejects = [];

    /**
     * The array of callbacks to be run before the event is started.
     *
     * @var \Closure[]
     */
    protected $beforeCallbacks = [];

    /**
     * The array of callbacks to be run after the event is finished.
     *
     * @var \Closure[]
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
     * @var array<string,int>
     */
    protected $fieldsPosition = [
        'minute' => 1,
        'hour' => 2,
        'day' => 3,
        'month' => 4,
        'week' => 5,
    ];

    /**
     * Indicates if the command should not overlap itself.
     *
     * @var bool
     */
    private $preventOverlapping = false;
    /** @var ClockInterface */
    private static $clock;
    /** @var ClosureSerializerInterface|null */
    private static $closureSerializer = null;

    /**
     * The symfony lock factory that is used to acquire locks. If the value is null, but preventOverlapping = true
     * crunz falls back to filesystem locks.
     *
     * @var Factory|LockFactory|null
     */
    private $lockFactory;
    /** @var string[] */
    private $wholeOutput = [];
    /** @var Lock */
    private $lock;
    /** @var \Closure[] */
    private $errorCallbacks = [];

    /**
     * Create a new event instance.
     *
     * @param string|Closure $command
     * @param string|int     $id
     */
    public function __construct($id, $command)
    {
        $this->command = $command;
        $this->id = $id;
        $this->output = $this->getDefaultOutput();
    }

    /**
     * Change the current working directory.
     *
     * @param string $directory
     *
     * @return self
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
        return 'NUL' === $this->output || '/dev/null' === $this->output;
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

        $command .= \is_string($this->command)
            ? $this->command
            : $this->serializeClosure($this->command)
        ;

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

    /** @return string */
    public function wholeOutput()
    {
        return \implode('', $this->wholeOutput);
    }

    /**
     * Start the event execution.
     *
     * @return int
     */
    public function start()
    {
        $command = $this->buildCommand();
        $process = Process::fromStringCommand($command);

        $this->setProcess($process);
        $this->getProcess()->start(
            function ($type, $content): void {
                $this->wholeOutput[] = $content;
            }
        );

        if ($this->preventOverlapping) {
            $this->lock();
        }

        /** @var int $pid */
        $pid = $this->getProcess()
            ->getPid();

        return $pid;
    }

    /**
     * The Cron expression representing the event's frequency.
     *
     * @throws TaskException
     */
    public function cron(string $expression): self
    {
        /** @var string[] $parts */
        $parts = \preg_split(
            '/\s/',
            $expression,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (\count($parts) > 5) {
            throw new TaskException("Expression '{$expression}' has more than five parts and this is not allowed.");
        }

        $this->expression = $expression;

        return $this;
    }

    /**
     * Schedule the event to run hourly.
     */
    public function hourly(): self
    {
        return $this->cron('0 * * * *');
    }

    /**
     * Schedule the event to run daily.
     */
    public function daily(): self
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
        $parsedDate = \date_parse($date);
        if (false === $parsedDate) {
            $parsedDate = [];
        }

        $segments = \array_intersect_key($parsedDate, $this->fieldsPosition);

        if ($parsedDate['year']) {
            $this->skip(static function () use ($parsedDate) {
                return (int) \date('Y') !== $parsedDate['year'];
            });
        }

        foreach ($segments as $key => $value) {
            if (false !== $value) {
                $this->spliceIntoPosition($this->fieldsPosition[$key], (string) $value);
            }
        }

        return $this;
    }

    /**
     * Schedule the command at a given time.
     *
     * @param string $time
     */
    public function at($time): self
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @param string $time
     */
    public function dailyAt($time): self
    {
        $segments = \explode(':', $time);
        $firstSegment = (int) $segments[0];
        $secondSegment = \count($segments) > 1
            ? (int) $segments[1]
            : '0'
        ;

        return $this
            ->spliceIntoPosition(2, (string) $firstSegment)
            ->spliceIntoPosition(1, (string) $secondSegment)
        ;
    }

    /**
     * Set Working period.
     *
     * @param string $from
     * @param string $to
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     */
    public function twiceDaily($first = 1, $second = 13): self
    {
        $hours = $first . ',' . $second;

        return $this
            ->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, $hours)
        ;
    }

    /**
     * Schedule the event to run only on weekdays.
     */
    public function weekdays(): self
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    /**
     * Schedule the event to run only on Mondays.
     */
    public function mondays(): self
    {
        return $this->days(1);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     */
    public function tuesdays(): self
    {
        return $this->days(2);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     */
    public function wednesdays(): self
    {
        return $this->days(3);
    }

    /**
     * Schedule the event to run only on Thursdays.
     */
    public function thursdays(): self
    {
        return $this->days(4);
    }

    /**
     * Schedule the event to run only on Fridays.
     */
    public function fridays(): self
    {
        return $this->days(5);
    }

    /**
     * Schedule the event to run only on Saturdays.
     */
    public function saturdays(): self
    {
        return $this->days(6);
    }

    /**
     * Schedule the event to run only on Sundays.
     */
    public function sundays(): self
    {
        return $this->days(0);
    }

    /**
     * Schedule the event to run weekly.
     */
    public function weekly(): self
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param int|string $day
     * @param string     $time
     */
    public function weeklyOn($day, $time = '0:0'): self
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, (string) $day);
    }

    /**
     * Schedule the event to run monthly.
     */
    public function monthly(): self
    {
        return $this->cron('0 0 1 * *');
    }

    /**
     * Schedule the event to run quarterly.
     */
    public function quarterly(): self
    {
        return $this->cron('0 0 1 */3 *');
    }

    /**
     * Schedule the event to run yearly.
     */
    public function yearly(): self
    {
        return $this->cron('0 0 1 1 *');
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param mixed $days
     */
    public function days($days): self
    {
        $days = \is_array($days) ? $days : \func_get_args();

        return $this->spliceIntoPosition(5, \implode(',', $days));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     */
    public function hour($value): self
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(2, \implode(',', $value));
    }

    /**
     * Set minute for the cron job.
     *
     * @param mixed $value
     */
    public function minute($value): self
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(1, \implode(',', $value));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     */
    public function dayOfMonth($value): self
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(3, \implode(',', $value));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     */
    public function month($value): self
    {
        $value = \is_array($value) ? $value : \func_get_args();

        return $this->spliceIntoPosition(4, \implode(',', $value));
    }

    /**
     * Set hour for the cron job.
     *
     * @param mixed $value
     */
    public function dayOfWeek($value): self
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
     * By default, the lock is acquired through file system locks. Alternatively, you can pass a symfony lock store
     * that will be responsible for the locking.
     *
     * @param StoreInterface|BlockingStoreInterface $store
     *
     * @return $this
     */
    public function preventOverlapping(object $store = null)
    {
        if (null !== $store && !($store instanceof BlockingStoreInterface || $store instanceof StoreInterface)) {
            $legacyClass = StoreInterface::class;
            $newClass = BlockingStoreInterface::class;
            $actualClass = \get_class($store);

            throw new \RuntimeException(
                "Instance of '{$newClass}' or '{$legacyClass}' is expected, '{$actualClass}' provided"
            );
        }

        $lockStore = $store ?: $this->createDefaultLockStore();
        $this->preventOverlapping = true;
        $this->lockFactory = \class_exists(Factory::class)
            ? new Factory($lockStore)
            : new LockFactory($lockStore)
        ;

        // Skip the event if it's locked (processing)
        $this->skip(function () {
            $lock = $this->createLockObject();
            $lock->acquire();

            return !$lock->isAcquired();
        });

        $releaseCallback = function (): void {
            $this->releaseLock();
        };

        // Delete the lock file when the event is completed
        $this->after($releaseCallback);
        // Or on error
        $this->addErrorCallback($releaseCallback);

        return $this;
    }

    /**
     * Register a callback to further filter the schedule.
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
     * @return $this
     */
    public function before(Closure $callback)
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called after the operation.
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
     * Return the event's process.
     *
     * @return Process $process
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
     * @param string         $unit
     * @param float|int|null $value
     */
    public function every($unit = null, $value = null): self
    {
        if (null === $unit || !isset($this->fieldsPosition[$unit])) {
            return $this;
        }

        $value = (1 === (int) $value) ? '*' : '*/' . $value;

        return $this->spliceIntoPosition($this->fieldsPosition[$unit], $value)
                    ->applyMask($unit);
    }

    /**
     * Return the event's command.
     *
     * @return string|int
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
     * @return string|\Closure
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
     * @return string|null
     */
    public function getOutputStream()
    {
        return $this->outputStream;
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

    /** @return \Closure[] */
    public function errorCallbacks()
    {
        return $this->errorCallbacks;
    }

    /**
     * If this event is prevented from overlapping, this method should be called regularly to refresh the lock.
     */
    public function refreshLock(): void
    {
        if (!$this->preventOverlapping) {
            return;
        }

        $lock = $this->createLockObject();
        $remainingLifetime = $lock->getRemainingLifetime();

        // Lock will never expire
        if (null === $remainingLifetime) {
            return;
        }

        // Refresh 15s before lock expiration
        $lockRefreshNeeded = $remainingLifetime < 15;
        if ($lockRefreshNeeded) {
            $lock->refresh();
        }
    }

    public function everyMinute(): self
    {
        return $this->cron('* * * * *');
    }

    public function everyTwoMinutes(): self
    {
        return $this->cron('*/2 * * * *');
    }

    public function everyThreeMinutes(): self
    {
        return $this->cron('*/3 * * * *');
    }

    public function everyFourMinutes(): self
    {
        return $this->cron('*/4 * * * *');
    }

    public function everyFiveMinutes(): self
    {
        return $this->cron('*/5 * * * *');
    }

    public function everyTenMinutes(): self
    {
        return $this->cron('*/10 * * * *');
    }

    public function everyFifteenMinutes(): self
    {
        return $this->cron('*/15 * * * *');
    }

    public function everyThirtyMinutes(): self
    {
        return $this->cron('*/30 * * * *');
    }

    public function everyTwoHours(): self
    {
        return $this->cron('0 */2 * * *');
    }

    public function everyThreeHours(): self
    {
        return $this->cron('0 */3 * * *');
    }

    public function everyFourHours(): self
    {
        return $this->cron('0 */4 * * *');
    }

    public function everySixHours(): self
    {
        return $this->cron('0 */6 * * *');
    }

    /**
     * Get the symfony lock object for the task.
     *
     * @return Lock
     */
    protected function createLockObject()
    {
        $this->checkLockFactory();

        if (null === $this->lock && null !== $this->lockFactory) {
            $ttl = 30;

            $this->lock = $this->lockFactory
                ->createLock($this->lockKey(), $ttl);
        }

        return $this->lock;
    }

    /**
     * Release the lock after the command completed.
     */
    protected function releaseLock(): void
    {
        $this->checkLockFactory();

        $lock = $this->createLockObject();
        $lock->release();
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
     * @return string
     */
    protected function serializeClosure(Closure $closure)
    {
        $closure = $this->closureSerializer()
            ->serialize($closure)
        ;
        $serializedClosure = \http_build_query([$closure]);
        $crunzRoot = CRUNZ_BIN;

        return PHP_BINARY . " {$crunzRoot} closure:run {$serializedClosure}";
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
            $taskTimeZone = \is_object($this->timezone) && $this->timezone instanceof \DateTimeZone
                ? $this->timezone
                    ->getName()
                : $this->timezone
            ;

            $now = $now->setTimezone(
                new \DateTimeZone(
                    $taskTimeZone
                )
            );
        }

        return CronExpression::factory($this->expression)->isDue($now->format('Y-m-d H:i:s'));
    }

    /**
     * Check if time hasn't arrived.
     *
     * @param string $datetime
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
     * @param string $datetime
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
     */
    protected function spliceIntoPosition($position, $value): self
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
     * @return self
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
     */
    protected function lock(): void
    {
        $lock = $this->createLockObject();
        $lock->acquire();
    }

    private function addErrorCallback(Closure $closure): void
    {
        $this->errorCallbacks[] = $closure;
    }

    /**
     * Set the event's process.
     */
    private function setProcess(Process $process): void
    {
        $this->process = $process;
    }

    /**
     * @return FlockStore
     *
     * @throws Exception\CrunzException
     */
    private function createDefaultLockStore()
    {
        try {
            $lockPath = Path::create(
                [
                    \sys_get_temp_dir(),
                    '.crunz',
                ]
            );

            $store = new FlockStore($lockPath->toString());
        } catch (InvalidArgumentException $exception) {
            // Fallback to system temp dir
            $lockPath = Path::create([\sys_get_temp_dir()]);
            $store = new FlockStore($lockPath->toString());
        }

        return $store;
    }

    private function lockKey(): string
    {
        if ($this->isClosure()) {
            /** @var \Closure $closure */
            $closure = $this->command;
            $command = $this->closureSerializer()
                ->closureCode($closure)
            ;
        } else {
            $command = $this->buildCommand();
        }

        return 'crunz-' . \md5($command);
    }

    private function checkLockFactory(): void
    {
        if (null === $this->lockFactory) {
            throw new \BadMethodCallException(
                'No lock factory. Please call preventOverlapping() first.'
            );
        }
    }

    private function getClock(): ClockInterface
    {
        if (null === self::$clock) {
            self::$clock = new Clock();
        }

        return self::$clock;
    }

    private function closureSerializer(): ClosureSerializerInterface
    {
        if (null === self::$closureSerializer) {
            self::$closureSerializer = new OpisClosureSerializer();
        }

        return self::$closureSerializer;
    }

    private function isWindows(): bool
    {
        $osCode = \mb_substr(
            PHP_OS,
            0,
            3
        );

        return 'WIN' === $osCode;
    }
}
