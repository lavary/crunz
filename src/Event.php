<?php

namespace Crunz;

use Closure;
use Carbon\Carbon;
use LogicException;
use Cron\CronExpression;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Process\Process;

class Event
{
    /**
     * The command string.
     *
     * @var string
     */
    public $command;

    /**
     * Process that runs the event
     *
     * @var Symfony\Component\Process\Process
     */
    public $process;

    /**
     * The cron expression representing the event's frequency.
     *
     * @var string
     */
    public $expression = '* * * * * *';

    /**
     * The timezone the date should be evaluated on.
     *
     * @var \DateTimeZone|string
     */
    public $timezone;

    /**
     * The user the command should run as.
     *
     * @var string
     */
    public $user;

    /**
     * Indicates if the command should not overlap itself.
     *
     * @var bool
     */
    public $preventOverlapping = false;

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
     * The location that output should be sent to.
     *
     * @var string
     */
    protected $output = '/dev/null';

    /**
     * Indicates whether output should be appended.
     *
     * @var bool
     */
    protected $shouldAppendOutput = false;

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
     * The human readable description of the event.
     *
     * @var string
     */
    public $description;

    /**
     * Current working directory
     *
     * @var string
     */
    protected $cwd = null;

    /**
     * Position of cron fields
     *
     * @var array
     */
    protected $fieldsPosition = [
        
        'minute' => 1,
        'hour'   => 2,
        'day'    => 3,
        'month'  => 4,
        'week'   => 5,

    ];

    /**
     * Create a new event instance.
     *
     * @param  string  $command
     * @return void
     */
    public function __construct($command)
    {
        $this->command = $command;
        $this->output = $this->getDefaultOutput();
    }

    /**
     * Get the default output depending on the OS.
     *
     * @return string
     */
    protected function getDefaultOutput()
    {
        return (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';
    }

    /**
      * Run the given event.
      *
      * @param  \Crunz\Invoker  $invoker
      * @return void
      */
    public function run(Invoker $invoker)
    {
        // Starting the process asynchronously
        $this->process = new Process(trim($this->buildCommand(), '& '));
        $this->process->start();

        // Lock the process if preventOverlapping is set to True
        if ($this->preventOverlapping) {
            file_put_contents($this->lockFilePath(), $this->process->getPid());

            // Delete the file when the task is completed
            $this->after(function() {
                $lock_file = $this->lockFilePath();
                if (file_exists($lock_file)) {
                    unlink($lock_file);
                }
            });
        }

        return $this;
    }

     /**
     * Change current working directory
     *
     * @param  string $directory
     * @return \Crunz\Event
     */
    public function in($directory)
    {
        $this->cwd = $directory;

        return $this;
    }

    /**
     * Call all of the "before" callbacks for the event.
     *
     * @param  \Crunz\Invoker $invoker
     * @return void
     */
    public function callBeforeCallbacks(Invoker $invoker)
    {
        foreach ($this->beforeCallbacks as $callback) {
            $invoker->call($callback);
        }

        return $this;
    }

    /**
     * Call all of the "after" callbacks for the event.
     *
     * @param  \Crunz\Invoker $invoker
     * @return void
     */
    public function callAfterCallbacks(Invoker $invoker)
    {
        foreach ($this->afterCallbacks as $callback) {
            $invoker->call($callback);
        }

        return $this;
    }

    /**
     * Build the comand string.
     *
     * @return string
     */
    public function buildCommand()
    {
        // Change the current working directory if needed.
        if (!is_null($this->cwd)) {
            $this->command = 'cd ' .  $this->cwd . '; ' . $this->command;
        }

        //$redirect = $this->shouldAppendOutput ? ' >> ' : ' > ';
        
        $command  = $this->command;
                
        return $this->user ? 'sudo -u ' . $this->user . ' ' . $command : $command;
    }

    /**
     * Check if another instance of the event is still running
     *
     * @return boolean
     */
    public function isLocked()
    {
        $lock_file = $this->lockFilePath();
        
        $pid       = file_exists($lock_file) ? (int)trim(file_get_contents($lock_file)) : null;

        return (!is_null($pid) && posix_getsid($pid)) ? true : false;
   
    }

    /**
     * Get the lock file path for the task
     *
     * @return string
     */
    protected function lockFilePath()
    {
        return rtrim(sys_get_temp_dir(), '/') . '/crunz-' . md5($this->expression . $this->command);
    }

    /**
     * Determine if the given event should run based on the Cron expression.
     * @param  \Crunz\Caller $app
     * @return bool
     */
    public function isDue(Invoker $invoker)
    {
        return $this->expressionPasses() && $this->filtersPass($invoker);
    }

    /**
     * Determine if the Cron expression passes.
     *
     * @return bool
     */
    protected function expressionPasses()
    {
        $date = Carbon::now();

        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }

        return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
    }


    /**
     * Determine if the filters pass for the event.
     *
     * @param  \Crunz\Invoker $invoker
     * @return bool
     */
    public function filtersPass(Invoker $invoker)
    {
        foreach ($this->filters as $callback) {
            if (! $invoker->call($callback)) {
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
     * The Cron expression representing the event's frequency.
     *
     * @param  string  $expression
     * @return $this
     */
    public function cron($expression)
    {
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
        return $this->cron('0 * * * * *');
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public function daily()
    {
        return $this->cron('0 0 * * * *');
    }

    /**
     * Schedule the event to run on a certain date
     *
     * @param  string  $date
     * @return $this
     */
    public function on($date)
    {
        
        $date     = date_parse($date);
        $segments = array_only($date, array_flip($this->fieldsPosition));

        if ($date['year']) {
 
            $this->skip(function () use ($segments) {
                return (int) date('Y') != $segments['year'];
            });

        }
                
        foreach ($segments as $key => $value) {   
            if ($value != false) {                
                $this->spliceIntoPosition($this->fieldsPosition[$key], (int) $value);
            }
        }

        return $this;          
    }

    /**
     * Schedule the command at a given time.
     *
     * @param  string  $time
     * @return $this
     */
    public function at($time)
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @param  string  $time
     * @return $this
     */
    public function dailyAt($time)
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
                    ->spliceIntoPosition(1, count($segments) > 1 ? (int) $segments[1] : '0');
    }

    /**
     * Set Working period
     *
     */
    public function between($from, $to)
    {
        return $this->from($from)
                    ->to($to);    
        
    }
    
    /**
     * Check if event should be on
     *
     * @param  string  $datetime
     */
     public function from($datetime)
     { 
        return $this->skip(function() use ($datetime) {
            return $this->notYet($datetime);
        });
     }

    /**
     * Check if event should be off
     *
     * @param  string  $datetime
     * @return boolean
     */
    public function to($datetime)
    {          
        return $this->skip(function() use ($datetime) {
            return $this->past($datetime);
        });
    }

    /**
     * Check if time hasn't arrived
     *
     * @param  string  $time
     * @return boolean
     */
    protected function notYet($datetime)
    {  
        return time() < strtotime($datetime);
    }

    /**
     * Check if the time has passed
     *
     * @param  string $time
     * @return boolean
     */
    protected function past($datetime)
    {
       return time() > strtotime($datetime);
    }

    /**
     * Schedule the event to run twice daily.
     *
     * @param  int  $first
     * @param  int  $second
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
        return $this->cron('0 0 * * 0 *');
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param  int  $day
     * @param  string  $time
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
        return $this->cron('0 0 1 * * *');
    }

    /**
     * Schedule the event to run quarterly.
     *
     * @return $this
     */
    public function quarterly()
    {
        return $this->cron('0 0 1 */3 * *');
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return $this
     */
    public function yearly()
    {
        return $this->cron('0 0 1 1 * *');
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param  array|mixed  $days
     * @return $this
     */
    public function days($days)
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Set hour for the cron job
     *
     * @param  mixed $value
     * @return $this
     */
    public function hour($value)
    {
        $value = is_array($value) ? $value : func_get_args();
        
        return $this->spliceIntoPosition(2, implode(',', $value));
    }

    /**
     * Set minute for the cron job
     *
     * @param  mixed $value
     * @return $this
     */
    public function minute($value)
    {
        $value = is_array($value) ? $value : func_get_args();
        
        return $this->spliceIntoPosition(1, implode(',', $value));
    }

    /**
     * Set hour for the cron job
     *
     * @param  mixed $value
     * @return $this
     */
    public function dayOfMonth($value)
    {
        $value = is_array($value) ? $value : func_get_args();
        
        return $this->spliceIntoPosition(3, implode(',', $value));
    }

    /**
     * Set hour for the cron job
     *
     * @param  mixed $value
     * @return $this
     */
    public function month($value)
    {
        $value = is_array($value) ? $value : func_get_args();
        
        return $this->spliceIntoPosition(4, implode(',', $value));
    }

    /**
     * Set hour for the cron job
     *
     * @param  mixed $value
     * @return $this
     */
    public function dayOfWeek($value)
    {
        $value = is_array($value) ? $value : func_get_args();
        
        return $this->spliceIntoPosition(5, implode(',', $value));
    }

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param  \DateTimeZone|string  $timezone
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
     * @param  string  $user
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Do not allow the event to overlap each other.
     *
     * @param  string|int $safe_duration
     * @return $this
     */
    public function preventOverlapping()
    {
        $this->preventOverlapping = true;

        return $this->skip(function () {
            return $this->isLocked();
        });
    }

    /**
     * Register a callback to further filter the schedule.
     *
     * @param  \Closure  $callback
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
     * @param  \Closure  $callback
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
     * @param  string  $location
     * @param  bool  $append
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
     * @param  string  $location
     * @return $this
     */
    public function appendOutputTo($location)
    {
        return $this->sendOutputTo($location, true);
    }

    /**
     * Log output to a file
     *
     * @param  string   $data
     * @param  string   $output
     * @param  boolean  $append
     * @return $this
     */
    public function logOutput($data, $output = null, $append = true)
    { 
        if ($output == '/dev/null' || is_null($output)) {
            return;
        }

        $flag = $append ? FILE_APPEND : 0;
        file_put_contents($output, $data, $flag);

        return $this;
    }

    /**
     * Log event's output to the specified output file
     *
     * @param  string  $data
     * @return $this
     */
    public function logEventOutput($data)
    {
        $this->logOutput($data, $this->output, $this->shouldAppendOutput);

        return $this;
    }
    
    /**
     * Register a callback to ping a given URL before the job runs.
     *
     * @param  string  $url
     * @return $this
     */
    public function pingBefore($url)
    {
        return $this->before(function () use ($url) {
            (new HttpClient)->get($url);
        });
    }

    /**
     * Register a callback to be called before the operation.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function before(Closure $callback)
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to ping a given URL after the job runs.
     *
     * @param  string  $url
     * @return $this
     */
    public function thenPing($url)
    {
        return $this->then(function () use ($url) {
            (new HttpClient)->get($url);
        });
    }

    /**
     * Register a callback to be called after the operation.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function after(Closure $callback)
    {
        return $this->then($callback);
    }

    /**
     * Register a callback to be called after the operation.
     *
     * @param  \Closure  $callback
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
     * @param  string  $description
     * @return $this
     */
    public function name($description)
    {
        return $this->description($description);
    }

    /**
     * Set the human-friendly description of the event.
     *
     * @param  string  $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @param  int  $position
     * @param  string  $value
     * @return $this
     */
    protected function spliceIntoPosition($position, $value)
    {
        $segments = explode(' ', $this->expression);

        $segments[$position - 1] = $value;

        return $this->cron(implode(' ', $segments));
    }

    /**
     * Set the frequency for the cron job
     *
     * @param  string  $unit
     * @param  string  $value
     * @return $this
     */
    public function every($unit = null, $value = null)
    {
        if (!isset($this->fieldsPosition[$unit])) {
            return $this;
        }
        
        $value = $value == 1 ? '*' : '*/' . $value;
        return $this->spliceIntoPosition($this->fieldsPosition[$unit], $value)
                    ->applyMask($unit);
    }

    /**
     * Get the summary of the event for display.
     *
     * @return string
     */
    public function getSummaryForDisplay()
    {
        if (is_string($this->description)) {
            return $this->description;
        }

        return $this->buildCommand();
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
     * Mask a cron expression
     *
     * @param  string $unit
     * @return string
     */
    protected function applyMask($unit) 
    {
        $cron = explode(' ', $this->expression);
        $mask = ['0', '0', '1', '1', '*', '*'];

        $fpos = $this->fieldsPosition[$unit] - 1;
        array_splice($cron, 0, $fpos, array_slice($mask, 0, $fpos));
    
        return $this->cron(implode(' ', $cron));
    }

    /**
     * Handling dynamic frequency methods
     *
     * @param  string $methodName
     * @param  array  $params
     * @return $this
     */
    public function __call($methodName, $params)
    {
        preg_match('/^every([A-Z][a-zA-Z]+)?(Minute|Hour|Day|Month)s?$/', $methodName, $matches);

        if (!count($matches) || $matches[1] == 'Zero') {            
            throw new \BadMethodCallException();
        }

        $amount = !empty($matches[1]) ? word2number(split_camel($matches[1])) : 1;
        
        if (!$amount) {
            throw new \BadMethodCallException();
        }

        return $this->every(strtolower($matches[2]), $amount);
    }

}
