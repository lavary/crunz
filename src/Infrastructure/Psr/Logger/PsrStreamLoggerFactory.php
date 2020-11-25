<?php

declare(strict_types=1);

namespace Crunz\Infrastructure\Psr\Logger;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Application\Service\LoggerFactoryInterface;
use Crunz\Clock\ClockInterface;
use Crunz\Task\Timezone;
use Psr\Log\LoggerInterface;

final class PsrStreamLoggerFactory implements LoggerFactoryInterface
{
    /** @var Timezone */
    private $timezoneProvider;
    /** @var ClockInterface */
    private $clock;

    public function __construct(Timezone $timezoneProvider, ClockInterface $clock)
    {
        $this->timezoneProvider = $timezoneProvider;
        $this->clock = $clock;
    }

    public function create(ConfigurationInterface $configuration): LoggerInterface
    {
        $timezone = $this->timezoneProvider
            ->timezoneForComparisons()
        ;

        return new EnabledLoggerDecorator(
            new PsrStreamLogger(
                $timezone,
                $this->clock,
                $configuration->get('output_log_file'),
                $configuration->get('errors_log_file'),
                $configuration->get('log_ignore_empty_context'),
                $configuration->get('timezone_log'),
                $configuration->get('log_allow_line_breaks')
            ),
            $configuration
        );
    }
}
