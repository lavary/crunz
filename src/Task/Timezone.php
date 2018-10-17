<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Crunz\Exception\EmptyTimezoneException;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Timezone\ProviderInterface;

/**
 * @internal
 */
class Timezone
{
    /** @var Configuration */
    private $configuration;
    /** @var ProviderInterface */
    private $timezoneProvider;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    public function __construct(
        Configuration $configuration,
        ProviderInterface $timezoneProvider,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->configuration = $configuration;
        $this->timezoneProvider = $timezoneProvider;
        $this->consoleLogger = $consoleLogger;
    }

    /**
     * @throws EmptyTimezoneException
     */
    public function timezoneForComparisons(): \DateTimeZone
    {
        $newTimezone = $this->configuration
            ->get('timezone')
        ;

        $this->consoleLogger
            ->debug("Timezone from config: '<info>{$newTimezone}</info>'.");

        if (empty($newTimezone)) {
            throw new EmptyTimezoneException(
                'Timezone must be configured. Please add it to your config file.'
            );

            $newTimezone = $this->timezoneProvider
                ->defaultTimezone()
                ->getName()
            ;

            $this->consoleLogger
                ->debug("Default timezone: '<info>{$newTimezone}</info>'.");
        }

        $this->consoleLogger
            ->debug("Timezone for comparisons: '<info>{$newTimezone}</info>'.");

        return new \DateTimeZone($newTimezone);
    }
}
