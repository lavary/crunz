<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Infrastructure\Psr\Logger;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Infrastructure\Psr\Logger\EnabledLoggerDecorator;
use Crunz\Tests\TestCase\FakeConfiguration;
use Crunz\Tests\TestCase\Faker;
use Crunz\Tests\TestCase\Logger\SpyPsrLogger;
use Crunz\Tests\TestCase\UnitTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class EnabledLoggerDecoratorTest extends UnitTestCase
{
    /** @dataProvider disabledChannelProvider */
    public function test_disabled_channels_not_log(
        ConfigurationInterface $configuration,
        string $logLevel
    ): void {
        // Arrange
        $spyLogger = new SpyPsrLogger();
        $enabledLoggerDecorator = $this->createEnabledLoggerDecorator($spyLogger, $configuration);

        // Act
        $enabledLoggerDecorator->log($logLevel, Faker::words());

        // Assert
        $this->assertCount(0, $spyLogger->getLogs());
    }

    /** @dataProvider enabledChannelProvider */
    public function test_enabled_channels_log(
        ConfigurationInterface $configuration,
        string $logLevel
    ): void {
        // Arrange
        $spyLogger = new SpyPsrLogger();
        $enabledLoggerDecorator = $this->createEnabledLoggerDecorator($spyLogger, $configuration);

        // Act
        $enabledLoggerDecorator->log($logLevel, Faker::words());

        // Assert
        $this->assertCount(1, $spyLogger->getLogs());
    }

    /** @return iterable<string,array> */
    public function disabledChannelProvider(): iterable
    {
        yield 'output' => [
            new FakeConfiguration(['log_output' => false]),
            LogLevel::INFO,
        ];

        yield 'error' => [
            new FakeConfiguration(['log_errors' => false]),
            LogLevel::ERROR,
        ];
    }

    /** @return iterable<string,array> */
    public function enabledChannelProvider(): iterable
    {
        yield 'output' => [
            new FakeConfiguration(['log_output' => true]),
            LogLevel::INFO,
        ];

        yield 'error' => [
            new FakeConfiguration(['log_errors' => true]),
            LogLevel::ERROR,
        ];
    }

    private function createEnabledLoggerDecorator(
        LoggerInterface $logger,
        ConfigurationInterface $configuration
    ): EnabledLoggerDecorator {
        return new EnabledLoggerDecorator($logger, $configuration);
    }
}
