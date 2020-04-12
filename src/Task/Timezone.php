<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Exception\EmptyTimezoneException;
use Crunz\Logger\ConsoleLoggerInterface;

class Timezone
{
    /** @var ConfigurationInterface */
    private $configuration;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;
    /** @var \DateTimeZone|null */
    private $timezoneForComparisons;

    public function __construct(
        ConfigurationInterface $configuration,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->configuration = $configuration;
        $this->consoleLogger = $consoleLogger;
    }

    /** @throws EmptyTimezoneException */
    public function timezoneForComparisons(): \DateTimeZone
    {
        if (null !== $this->timezoneForComparisons) {
            return $this->timezoneForComparisons;
        }

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

        $this->timezoneForComparisons = new \DateTimeZone($newTimezone);

        return $this->timezoneForComparisons;
    }
}
