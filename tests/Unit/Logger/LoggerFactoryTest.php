<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Logger;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Configuration\Configuration;
use Crunz\Configuration\ConfigurationParserInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Logger\Logger;
use Crunz\Logger\LoggerFactory;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\Logger\NullLogger;
use Crunz\Tests\TestCase\TemporaryFile;
use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;

final class LoggerFactoryTest extends TestCase
{
    /**
     * @test
     * @runInSeparateProcess
     */
    public function crunzLoggerTimezoneIsConfiguredTimezone(): void
    {
        $expectedTimezone = 'Asia/Tehran';
        $defaultTimezone = 'UTC';
        $crunzLogger = $this->createCrunzLogger(
            ['timezone' => $expectedTimezone, 'timezone_log' => true],
            $expectedTimezone
        );
        /** @var MonologLogger $monologLogger */
        $monologLogger = $this->getObjectProperty($crunzLogger, 'logger');
        /** @var \DateTimeZone $loggerTimezone */
        $loggerTimezone = $this->getObjectProperty($monologLogger, 'timezone');
        $this->assertNotNull($loggerTimezone);
        $this->assertSame($expectedTimezone, $loggerTimezone->getName());
        $this->assertNotSame($defaultTimezone, $loggerTimezone->getName());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function crunzLoggerTimezoneIsDefaultWhenTimezoneLogOptionIsFalse(): void
    {
        $expectedTimezone = 'Asia/Tehran';
        $crunzLogger = $this->createCrunzLogger(['timezone' => $expectedTimezone, 'timezone_log' => false]);

        /** @var MonologLogger $monologLogger */
        $monologLogger = $this->getObjectProperty($crunzLogger, 'logger');
        /** @var \DateTimeZone|null $loggerTimezone */
        $loggerTimezone = $this->getObjectProperty($monologLogger, 'timezone');
        $this->assertNull($loggerTimezone);
    }

    /** @param array<string,array> $config */
    private function createConfiguration(array $config = []): ConfigurationInterface
    {
        $mockConfigurationParser = $this->createMock(ConfigurationParserInterface::class);
        $mockConfigurationParser
            ->method('parseConfig')
            ->willReturn($config)
        ;

        return new Configuration(
            $mockConfigurationParser,
            $this->createMock(FilesystemInterface::class)
        );
    }

    /**
     * @param array<string,mixed> $config
     *
     * @throws \Crunz\Exception\CrunzException
     */
    private function createCrunzLogger(array $config = [], string $timezoneName = 'UTC'): Logger
    {
        $configuration = $this->createConfiguration($config);
        $mockTimezone = $this->createMock(Timezone::class);
        $mockTimezone
            ->method('timezoneForComparisons')
            ->willReturn(new \DateTimeZone($timezoneName))
        ;
        $loggerFactory = new LoggerFactory(
            $configuration,
            $mockTimezone,
            new NullLogger()
        );
        $tempFile = new TemporaryFile();

        return $loggerFactory->create(['debug' => $tempFile->filePath()]);
    }

    /** @return mixed */
    private function getObjectProperty(object $object, string $propertyName)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
