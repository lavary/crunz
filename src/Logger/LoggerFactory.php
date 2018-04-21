<?php

namespace Crunz\Logger;

use Monolog\Logger as MonologLogger;

class LoggerFactory
{
    /**
     * Create an instance of the Logger class.
     *
     * @return \Logger\Logger
     */
    public static function makeOne(array $streams = [])
    {
        $logger = new Logger(new MonologLogger('crunz'));

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
