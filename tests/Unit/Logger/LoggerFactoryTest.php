<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Logger;

use Crunz\Clock\Clock;
use Crunz\Event;
use Crunz\Exception\CrunzException;
use Crunz\Logger\LoggerFactory;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\FakeConfiguration;
use Crunz\Tests\TestCase\Logger\NullLogger;
use Crunz\Tests\TestCase\TemporaryFile;
use PHPUnit\Framework\TestCase;

final class LoggerFactoryTest extends TestCase
{
    public function test_logger_factory_creates_logger(): void
    {
        $loggerFactory = $this->createLoggerFactory();

        $loggerFactory->create();

        $this->expectNotToPerformAssertions();
    }

    public function test_logger_factory_creates_event_logger(): void
    {
        $loggerFactory = $this->createLoggerFactory();

        $tempFile = new TemporaryFile();

        $e = new Event('1', 'php foo');
        $e->output = $tempFile->filePath();

        $loggerFactory->createEvent($e->output);

        $this->expectNotToPerformAssertions();
    }

    public function test_wrong_logger_class_throws_exception(): void
    {
        $loggerFactory = $this->createLoggerFactory(['logger_factory' => 'Wrong\Class']);

        $this->expectException(CrunzException::class);
        $this->expectExceptionMessage("Class 'Wrong\Class' does not exists.");

        $loggerFactory->create();
    }

    /** @param array<string,mixed> $configuration */
    private function createLoggerFactory(array $configuration = []): LoggerFactory
    {
        $fakeConfiguration = new FakeConfiguration($configuration);
        $timeZoneProviderMock = $this->createMock(Timezone::class);

        return new LoggerFactory(
            $fakeConfiguration,
            $timeZoneProviderMock,
            new NullLogger(),
            new Clock()
        );
    }
}
