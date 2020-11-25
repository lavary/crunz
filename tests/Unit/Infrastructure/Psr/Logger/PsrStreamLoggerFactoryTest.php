<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Infrastructure\Psr\Logger;

use Crunz\Infrastructure\Psr\Logger\EnabledLoggerDecorator;
use Crunz\Infrastructure\Psr\Logger\PsrStreamLoggerFactory;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\FakeConfiguration;
use Crunz\Tests\TestCase\TestClock;
use Crunz\Tests\TestCase\UnitTestCase;

final class PsrStreamLoggerFactoryTest extends UnitTestCase
{
    public function test_factory_returns_decorated_logger(): void
    {
        // Arrange
        $psrStreamLoggerFactory = $this->createStreamLoggerFactory();

        // Act
        $logger = $psrStreamLoggerFactory->create(new FakeConfiguration());

        // Assert
        $this->assertInstanceOf(EnabledLoggerDecorator::class, $logger);
    }

    private function createStreamLoggerFactory(): PsrStreamLoggerFactory
    {
        return new PsrStreamLoggerFactory(
            $this->createMock(Timezone::class),
            new TestClock(new \DateTimeImmutable())
        );
    }
}
