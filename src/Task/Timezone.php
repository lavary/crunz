<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Crunz\Exception\EmptyTimezoneException;
use Crunz\Logger\ConsoleLoggerInterface;

class Timezone
{
    /** @var Configuration */
    private $configuration;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    public function __construct(
        Configuration $configuration,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->configuration = $configuration;
        $this->consoleLogger = $consoleLogger;
    }

    /** @throws EmptyTimezoneException */
    public function timezoneForComparisons(): \DateTimeZone
    {
        $newTimezone = $this->configuration
            ->get('timezone')
        ;

        $this->consoleLogger
            ->debug("Timezone from config: '<info>{$newTimezone}</info>'.");

        if (empty($newTimezone)) {
            throw new EmptyTimezoneException('Timezone must be configured. Please add it to your config file.');
        }

        $this->consoleLogger
            ->debug("Timezone for comparisons: '<info>{$newTimezone}</info>'.");

        return new \DateTimeZone($newTimezone);
    }
}
