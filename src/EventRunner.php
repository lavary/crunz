<?php

declare(strict_types=1);

namespace Crunz;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\HttpClient\HttpClientInterface;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Logger\LoggerFactory;
use Crunz\Pinger\PingableInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventRunner
{
    /**
     * Schedule objects.
     *
     * @var Schedule[]
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
    /** @var ConfigurationInterface */
    private $configuration;
    /** @var LoggerFactory */
    private $loggerFactory;
    /** @var HttpClientInterface */
    private $httpClient;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    /**
     * Instantiate the event runner.
     */
    public function __construct(
        Invoker $invoker,
        ConfigurationInterface $configuration,
        Mailer $mailer,
        LoggerFactory $loggerFactory,
        HttpClientInterface $httpClient,
        ConsoleLoggerInterface $consoleLogger
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
        $this->httpClient = $httpClient;
        $this->consoleLogger = $consoleLogger;
    }

    /**
     * Handle an array of Schedule objects.
     *
     * @param Schedule[] $schedules
     */
    public function handle(OutputInterface $output, array $schedules = []): void
    {
        $this->schedules = $schedules;
        $this->output = $output;

        foreach ($this->schedules as $schedule) {
            $this->consoleLogger
                ->debug("Invoke Schedule's ping before");

            $this->pingBefore($schedule);

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
     * @param \Crunz\Event $event
     */
    protected function start(Event $event): void
    {
        // if sendOutputTo or appendOutputTo have been specified
        if (!$event->nullOutput()) {
            // if sendOutputTo then truncate the log file if it exists
            if (!$event->shouldAppendOutput) {
                $f = @\fopen($event->output, 'r+');
                if (false !== $f) {
                    \ftruncate($f, 0);
                    \fclose($f);
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

        $this->consoleLogger
            ->debug("Invoke Event's ping before.");

        $this->pingBefore($event);

        // Running the before-callbacks
        $event->outputStream = ($this->invoke($event->beforeCallbacks()));
        $event->start();
    }

    /**
     * Manage the running processes.
     */
    protected function manageStartedEvents(): void
    {
        while ($this->schedules) {
            foreach ($this->schedules as $scheduleKey => $schedule) {
                $events = $schedule->events();
                // 10% chance that refresh will be called
                $refreshLocks = (\mt_rand(1, 100) <= 10);

                /** @var Event $event */
                foreach ($events as $eventKey => $event) {
                    if ($refreshLocks) {
                        $event->refreshLock();
                    }

                    $proc = $event->getProcess();
                    if ($proc->isRunning()) {
                        continue;
                    }

                    $runStatus = '';

                    if ($proc->isSuccessful()) {
                        $this->consoleLogger
                            ->debug("Invoke Event's ping after.");
                        $this->pingAfter($event);

                        $runStatus = '<info>success</info>';

                        $event->outputStream .= $event->wholeOutput();
                        $event->outputStream .= $this->invoke($event->afterCallbacks());

                        $this->handleOutput($event);
                    } else {
                        $runStatus = '<error>fail</error>';

                        // Invoke error callbacks
                        $this->invoke($event->errorCallbacks());
                        // Calling registered error callbacks with an instance of $event as argument
                        $this->invoke($schedule->errorCallbacks(), [$event]);
                        $this->handleError($event);
                    }

                    $id = $event->description ?: $event->getId();

                    $this->consoleLogger
                        ->debug("Task <info>${id}</info> status: {$runStatus}.");

                    // Dismiss the event if it's finished
                    $schedule->dismissEvent($eventKey);
                }

                // If there's no event left for the Schedule instance,
                // run the schedule's after-callbacks and remove
                // the Schedule from list of active schedules.                                                                                                                           zzzwwscxqqqAAAQ11
                if (!\count($schedule->events())) {
                    $this->consoleLogger
                        ->debug("Invoke Schedule's ping after.");

                    $this->pingAfter($schedule);
                    $this->invoke($schedule->afterCallbacks());
                    unset($this->schedules[$scheduleKey]);
                }
            }

            \usleep(250000);
        }
    }

    /**
     * Invoke an array of callables.
     *
     * @param \Closure[]         $callbacks
     * @param array<mixed,mixed> $parameters
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

    protected function handleOutput(Event $event): void
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
    protected function handleError(Event $event): void
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
            $output = $event->wholeOutput();

            $this->output
                ->write("<error>{$output}</error>");
        }

        // Send error as email as configured
        if ($emailErrors) {
            $this->mailer->send(
                'Crunz: reporting error for event:' . (($event->description) ? $event->description : $event->getId()),
                $this->formatEventError($event)
            );
        }
    }

    /** @return string */
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
            . $event->wholeOutput()
            . PHP_EOL;
    }

    /**
     * Display content.
     *
     * @param string|null $output
     */
    protected function display($output): void
    {
        $this->output
            ->write(\is_string($output) ? $output : '')
        ;
    }

    private function pingBefore(PingableInterface $schedule): void
    {
        if (!$schedule->hasPingBefore()) {
            $this->consoleLogger
                ->debug('There is no ping before url.');

            return;
        }

        $this->httpClient
            ->ping($schedule->getPingBeforeUrl());
    }

    private function pingAfter(PingableInterface $schedule): void
    {
        if (!$schedule->hasPingAfter()) {
            $this->consoleLogger
                ->debug('There is no ping after url.');

            return;
        }

        $this->httpClient
            ->ping($schedule->getPingAfterUrl());
    }
}
