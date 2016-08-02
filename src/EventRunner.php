<?php

namespace Crunz;

use Symfony\Component\Process\Process;
use SuperClosure\Serializer;
use Crunz\Configuration\Configurable;

class EventRunner {

    use Configurable;

    /**
     * Schedule objects
     *
     * @var array
     */
    protected $schedules = [];

    /**
     * Instance of the invoker class
     *
     * @var \Crunz\Invoker
     */
    protected $invoker;

    /**
     * Instance of SuperClosure serializer
     *
     * @var \SuperClosure\Serializer
     */
    protected $serializer;

    /**
     * Instantiate the event runner
     *
     */
    public function __construct()
    {
        $this->configurable();

        // This is used for serializing closures
        $this->serializer = new Serializer();
        
        // This is used for invoking a set of closures
        $this->invoker    = new Invoker();
    }

    /**
     * Handle an array of Schedule objects
     *
     * @param array $schedules
     */
    public function handle(Array $schedules = [])
    {
        $this->schedules = $schedules;
        
        foreach ($this->schedules as $schedule) {
            
            // Running the before-callbacks of the current schedule
            $this->invoke($schedule->beforeCallbacks());

            $events = $schedule->events();
            foreach ($events as $event) {
                $this->start($event);
            }
        }

        // Watch events until they are finished
        $this->ManageStartedEvents();
    }

    /**
     * Run an event process
     *
     *
     * @param \Crunz\Event $event
     */
    protected function start(Event $event)
    {
        // Running the before-callbacks
        $this->invoke($event->beforeCallbacks());
        
        if ($event->isClosure()) {
            $closure = $this->serializer->serialize($event->getCommand());
            $command = __DIR__ . '/../crunz closure:run ' . http_build_query([$closure]);
        } else {
            $command = trim($event->buildCommand(), '& ');
        }

        $event->setProcess(new Process($command));
        $event->getProcess()->start();
    }

    /**
     * Manage the running processes
     *
     * @return void
     */
    protected function ManageStartedEvents()
    {
       while ($this->schedules) {
            
            foreach ($this->schedules as $scheduleKey => $schedule) {
                
                $events = $schedule->events();
                foreach ($events as $eventKey => $event) {

                    $proc = $event->getProcess();
                    if ($proc->isRunning()) {
                        continue;
                    }

                    if ($proc->isSuccessful()) {
                        
                        $this->invoke($event->afterCallbacks());                       
                        
                        if ($this->config('log_output')) {
                            $this->logOutput($event);   
                        }
                    
                    } else {
                        
                        // Calling registered error callbacks with an instance of $event as argument
                        $this->invoke($schedule->errorCallbacks(), [$event]);
                        
                        if ($this->config('log_errors')) {
                            $this->logError($event);
                        }

                    }

                    // Dismiss the event if it's finished
                    $schedule->dismissEvent($eventKey);

                }

                // If there's no event left for the Schedule instance,
                // run the schedule's after-callbacks and remove
                // the Schedule from list of active schedules.                                                                                                                           zzzwwscxqqqAAAQ11
                if (!count($schedule->events())) {
                    $this->invoke($schedule->afterCallbacks());
                    unset($this->schedules[$scheduleKey]);
                }
            }
        } 
    }

    /**
     * Return output log
     *
     * @param \Crunz\Event $event
     */
    protected function logOutput(Event $event)
    {
        $info = $this->logDate()
              . ' [Performed: '
              . $event->getSummaryForDisplay()
              . '] by running "'
              . $event->buildCommand()
              . '" output: ' . $event->getProcess()->getOutput()
              . PHP_EOL;

        $path = $this->config('output_log_file');

        if ($event->nullOutput()) {
            $this->saveLog($info, $path);
        } else {
            $this->saveLog($info, $event->output, $event->shouldAppendOutput);
        }
    }

    /**
     * Return error log
     *
     * @param \Crunz\Event $event
     */
    protected function logError(Event $event)
    {
        $info = $this->logDate()
              . ' [Error occured with: '
              . $event->getSummaryForDisplay()
              . '] ' 
              . $event->getProcess()->getErrorOutput()
              . ' while running "'
              . $event->buildCommand()
              . '"'
              . PHP_EOL;

        $this->saveLog($info, $this->config('errors_log_file'));        
    }

    /**
     * Save the log info in the respective log file
     *
     * @param string $data 
     * @param string $output
     * @param $flag
     */
    protected function saveLog($data, $output, $flag = FILE_APPEND)
    {
        return file_put_contents($output, $data, $flag);  
    }

    /**
     *
     * @return string
     */
    protected function logDate()
    {
        return date($this->logDateFormat()); 
    }

    /**
     * Return date format to be used in log files
     *
     * @param highlight_string(str)
     */
    protected function logDateFormat()
    {
        return $this->config('log_date_format');   
    }

    /**
     * Invoke an array of callables
     *
     * @param array $callbacks
     */
    protected function invoke(array $callbacks = [], Array $parameters = [])
    {
       foreach ($callbacks as $callback) {
         $this->invoker->call($callback, $parameters); 
       } 
    }

}