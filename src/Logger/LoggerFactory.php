<?php

namespace Crunz\Logger;

use Crunz\Configuration\Configuration;
use Crunz\Task\Timezone;
use Monolog\Logger as MonologLogger;

class LoggerFactory
{
    /** @var Configuration */
    private $configuration;

    /**
     * @throws \Exception if the timezone supplied in configuration is not recognised as a valid timezone
     */
    public function __construct(
        Configuration $configuration,
        Timezone $timezoneProvider,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->configuration = $configuration;
        $timezoneLog = $configuration->get('timezone_log');

        if ($timezoneLog) {
            $timezone = $timezoneProvider->timezoneForComparisons();
            $consoleLogger->veryVerbose("Timezone for '<info>timezone_log</info>': '<info>{$timezone->getName()}</info>'");

            MonologLogger::setTimezone($timezone);
        }
    }

    /**
     * @return Logger
     */
    public function create(array $streams = [])
    {
        $logger = new Logger(new MonologLogger('crunz'), $this->configuration);

        // Adding stream for normal output
        foreach ($streams as $stream => $file) {
            if (!$file) {
                continue;
            }

            $logger->addStream(
                $file,
                $stream,
                false
            );
        }

        return $logger;
    }
}
