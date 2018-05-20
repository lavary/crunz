<?php

namespace Crunz;

use Crunz\Configuration\Configuration;
use Crunz\Logger\LoggerFactory;
use Symfony\Component\Console\Output\OutputInterface;

class EventRunner
{
    /**
     * Schedule objects.
     *
     * @var array
     */
    protected $schedules = [];
    /**
     * Instance of the invoker class.
     *
     * @var \Crunz\Invoker
     */
    protected $invoker;
    /**
     * The Logger.
     *
     * @var \Crunz\Logger\Logger
     */
    protected $logger;
    /**
     * The Mailer.
     *
     * @var \Crunz\Mailer
     */
    protected $mailer;
    /** @var OutputInterface */
    private $output;
    /** @var Configuration */
    private $configuration;
    /** @var LoggerFactory */
    private $loggerFactory;

    /**
     * Instantiate the event runner.
     */
    public function __construct(
        Invoker $invoker,
        Configuration $configuration,
        Mailer $mailer,
        LoggerFactory $loggerFactory
    ) {
        $outputLogFile = $configuration->get('output_log_file');
        $errorLogFile = $configuration->get('errors_log_file');

        $this->logger = $loggerFactory->create(
            [
                // Logging streams
                'info' => $outputLogFile,
                'error' => $errorLogFile,
            ]
        );
        $this->invoker = $invoker;
        $this->mailer = $mailer;
        $this->configuration = $configuration;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * Handle an array of Schedule objects.
     */
    public function handle(OutputInterface $output, array $schedules = [])
    {
        $this->schedules = $schedules;
        $this->output = $output;

        foreach ($this->schedules as $schedule) {
            // Running the before-callbacks of the current schedule
            $this->invoke($schedule->beforeCallbacks());

            $events = $schedule->events();
            foreach ($events as $event) {
                $this->start($event);
            }
        }

        // Watch events until they are finished
        $this->manageStartedEvents();
    }

    /**
     * Run an event process.
     *
     *
     * @param \Crunz\Event $event
     */
    protected function start(Event $event)
    {
        // if sendOutputTo or appendOutputTo have been specified
        if (!$event->nullOutput()) {
            // if sendOutputTo then truncate the log file if it exists
            if (!$event->shouldAppendOutput) {
                $f = @fopen($event->output, 'r+');
                if ($f !== false) {
                    ftruncate($f, 0);
                    fclose($f);
                }
            }
            // Create an instance of the Logger specific to the event
            $event->logger = $this->loggerFactory->create(
                [
                    // Logging streams
                    'info' => $event->output,
                ]
            );
        }

        // Running the before-callbacks
        $event->outputStream = ($this->invoke($event->beforeCallbacks()));
        $event->start();
    }

    /**
     * Manage the running processes.
     */
    protected function manageStartedEvents()
    {
        while ($this->schedules) {
            foreach ($this->schedules as $scheduleKey => $schedule) {
                $events = $schedule->events();

                /** @var Event $event */
                foreach ($events as $eventKey => $event) {
                    $proc = $event->getProcess();
                    if ($proc->isRunning()) {
                        continue;
                    }

                    if ($proc->isSuccessful()) {
                        $event->outputStream .= $proc->getOutput();
                        $event->outputStream .= $this->invoke($event->afterCallbacks());

                        $this->handleOutput($event);
                    } else {
                        // Calling registered error callbacks with an instance of $event as argument
                        $this->invoke($schedule->errorCallbacks(), [$event]);

                        $this->handleError($event);
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

            usleep(500000);
        }
    }

    /**
     * Invoke an array of callables.
     *
     * @param array $callbacks
     * @param array $parameters
     *
     * @return string
     */
    protected function invoke(array $callbacks = [], array $parameters = [])
    {
        $output = '';
        foreach ($callbacks as $callback) {
            // Invoke the callback with buffering enabled
            $output .= $this->invoker->call($callback, $parameters, true);
        }

        return $output;
    }

    /**
     * Handle output.
     *
     * @param \Crunz\Event
     */
    protected function handleOutput(Event $event)
    {
        $logged = false;
        $logOutput = $this->configuration
            ->get('log_output')
        ;

        if ($logOutput) {
            $this->logger->info($this->formatEventOutput($event));
            $logged = true;
        }
        if (!$event->nullOutput()) {
            $event->logger->info($this->formatEventOutput($event));
            $logged = true;
        }
        if (!$logged) {
            $this->display($event->getOutputStream());
        }

        $emailOutput = $this->configuration
            ->get('email_output')
        ;
        if ($emailOutput && !empty($event->getOutputStream())) {
            $this->mailer->send(
                'Crunz: output for event: ' . (($event->description) ? $event->description : $event->getId()),
                $this->formatEventOutput($event)
            );
        }
    }

    /**
     * Handle errors.
     *
     * @param \Crunz\Event $event
     */
    protected function handleError(Event $event)
    {
        $logErrors = $this->configuration
            ->get('log_errors')
        ;
        $emailErrors = $this->configuration
            ->get('email_errors')
        ;

        if ($logErrors) {
            $this->logger->error($this->formatEventError($event));
        } else {
            $this->display($event->getProcess()->getErrorOutput());
        }

        // Send error as email as configured
        if ($emailErrors) {
            $this->mailer->send(
                'Crunz: reporting error for event:' . (($event->description) ? $event->description : $event->getId()),
                $this->formatEventError($event)
            );
        }
    }

    /**
     * Format the event output.
     *
     * @param  \Crunz\Event
     *
     * @return string
     */
    protected function formatEventOutput(Event $event)
    {
        return $event->description
            . '('
            . $event->getCommandForDisplay()
            . ') '
            . PHP_EOL
            . PHP_EOL
            . $event->outputStream
            . PHP_EOL;
    }

    /**
     * Format the event error message.
     *
     * @param \Crunz\Event $event
     *
     * @return string
     */
    protected function formatEventError(Event $event)
    {
        return $event->description
            . '('
            . $event->getCommandForDisplay()
            . ') '
            . PHP_EOL
            . $event->getProcess()->getErrorOutput()
            . PHP_EOL;
    }

    /**
     * Display content.
     *
     * @param string $output
     */
    protected function display($output)
    {
        $this->output
            ->write(\is_string($output) ? $output : '')
        ;
    }
}
