<?php

declare(strict_types=1);

namespace Crunz\Logger;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Application\Service\LoggerFactoryInterface;
use Crunz\Clock\ClockInterface;
use Crunz\Exception\CrunzException;
use Crunz\Infrastructure\Psr\Logger\PsrStreamLoggerFactory;
use Crunz\Task\Timezone;

class LoggerFactory
{
    /** @var ConfigurationInterface */
    private $configuration;
    /** @var LoggerFactoryInterface */
    private $loggerFactory;
    /** @var Timezone */
    private $timezoneProvider;
    /** @var ClockInterface */
    private $clock;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    /**
     * @throws \Exception if the timezone supplied in configuration is not recognised as a valid timezone
     */
    public function __construct(
        ConfigurationInterface $configuration,
        Timezone $timezoneProvider,
        ConsoleLoggerInterface $consoleLogger,
        ClockInterface $clock
    ) {
        $this->configuration = $configuration;
        $this->timezoneProvider = $timezoneProvider;
        $this->clock = $clock;
        $this->consoleLogger = $consoleLogger;
    }

    public function create(): Logger
    {
        $this->initializeLoggerFactory();
        $configuration = $this->configuration;
        $innerLogger = $this->loggerFactory
            ->create($configuration)
        ;

        return new Logger($innerLogger);
    }

    public function createEvent(string $output): Logger
    {
        $this->initializeLoggerFactory();
        $eventConfiguration = $this->configuration->withNewEntry('output_log_file', $output);
        $innerLogger = $this->loggerFactory
            ->create($eventConfiguration)
        ;

        return new Logger($innerLogger);
    }

    private function initializeLoggerFactory(): void
    {
        if (null === $this->loggerFactory) {
            $timezoneLog = $this->configuration
                ->get('timezone_log')
            ;

            if ($timezoneLog) {
                $timezone = $this->timezoneProvider
                    ->timezoneForComparisons()
                ;

                $this->consoleLogger
                    ->veryVerbose("Timezone for '<info>timezone_log</info>': '<info>{$timezone->getName()}</info>'")
                ;
            }

            $this->loggerFactory = $this->createLoggerFactory(
                $this->configuration,
                $this->timezoneProvider,
                $this->clock
            );
        }
    }

    private function createLoggerFactory(
        ConfigurationInterface $configuration,
        Timezone $timezoneProvider,
        ClockInterface $clock
    ): LoggerFactoryInterface {
        $params = [];
        $loggerFactoryClass = $configuration->get('logger_factory');

        $this->consoleLogger
            ->veryVerbose("Class for '<info>logger_factory</info>': '<info>{$loggerFactoryClass}</info>'.")
        ;

        if (!\class_exists($loggerFactoryClass)) {
            throw new CrunzException("Class '{$loggerFactoryClass}' does not exists.");
        }

        $isPsrStreamLoggerFactory = \is_a(
            $loggerFactoryClass,
            PsrStreamLoggerFactory::class,
            true
        );
        if ($isPsrStreamLoggerFactory) {
            $params[] = $timezoneProvider;
            $params[] = $clock;
        }

        return new $loggerFactoryClass(...$params);
    }
}
