<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Logger;

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
        /** @var MonologLogger $monolog */
        $monologLogger = $this->readAttribute($crunzLogger, 'logger');
        /** @var \DateTimeZone $loggerTimezone */
        $loggerTimezone = $this->readAttribute($monologLogger, 'timezone');
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

        /** @var MonologLogger $monolog */
        $monologLogger = $this->readAttribute($crunzLogger, 'logger');
        /** @var \DateTimeZone|null $loggerTimezone */
        $loggerTimezone = $this->readAttribute($monologLogger, 'timezone');
        $this->assertNull($loggerTimezone);
    }

    /** @return Configuration */
    private function createConfiguration(array $config = [])
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

    /** @return Logger */
    private function createCrunzLogger(array $config = [], $timezoneName = 'UTC')
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
}
