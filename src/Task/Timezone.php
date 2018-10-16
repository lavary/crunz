<?php

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
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

    public function timezoneForComparisons()
    {
        $newTimezone = $this->configuration
            ->get('timezone')
        ;

        $this->consoleLogger
            ->debug("Timezone from config: '<info>{$newTimezone}</info>'.");

        /* @TODO Throw Exception in Crunz v2. */
        if (empty($newTimezone)) {
            @trigger_error(
                'Timezone is not configured and this is deprecated from 1.7 and will result in exception in 2.0 version. Add `timezone` key to your YAML config file.',
                E_USER_DEPRECATED
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
