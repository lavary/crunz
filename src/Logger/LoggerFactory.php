<?php

namespace Crunz\Logger;

use Crunz\Configuration\NonSingletonConfiguration;
use Monolog\Logger as MonologLogger;

class LoggerFactory
{
    /** @var NonSingletonConfiguration */
    private $configuration;

    public function __construct(NonSingletonConfiguration $configuration)
    {
        $this->configuration = $configuration;
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
