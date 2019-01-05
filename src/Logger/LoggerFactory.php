<?php

namespace Crunz\Logger;

use Crunz\Configuration\Configuration;
use Monolog\Logger as MonologLogger;

class LoggerFactory
{
    /** @var Configuration */
    private $configuration;

    /**
     * LoggerFactory constructor.
     *
     * @param Configuration $configuration
     *
     * @throws \Exception if the timezone supplied in configuration is not recognised as a valid timezone
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $timezone = $configuration->get('timezone');
        $timezoneLog = $configuration->get('timezone_log');
        if ($timezoneLog and $timezone) {
            MonologLogger::setTimezone(new \DateTimeZone($timezone));
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
