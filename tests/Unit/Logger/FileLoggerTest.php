<?php

namespace Crunz\Tests\Unit\Logger;

use Crunz\Configuration\Configuration;
use Crunz\Configuration\ConfigurationParserInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Logger\Logger;
use Crunz\Logger\LoggerFactory;
use Crunz\Tests\TestCase\TemporaryFile;
use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;

final class FileLoggerTest extends TestCase
{
    /**
     * @test
     */
    public function invalidTimezoneThrowException()
    {
        $configuration = $this->createConfiguration(['timezone' => 'invalid_ts', 'timezone_log' => true]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('*bad timezone*');
        new LoggerFactory($configuration);
    }

    /**
     * @test
     */
    public function crunzLoggerTimezoneIsConfiguredTimezone()
    {
        $expectedTimezone = 'Asia/Tehran';
        $defaultTimezone = 'UTC';
        $crunzLogger = $this->createCrunzLogger(['timezone' => $expectedTimezone, 'timezone_log' => true]);
        /** @var MonologLogger $monolog */
        $monologLogger = $this->readAttribute($crunzLogger, 'logger');
        /** @var \DateTimeZone|null $loggerTimezone */
        $loggerTimezone = $this->readAttribute($monologLogger, 'timezone');

        $this->assertSame($expectedTimezone, $loggerTimezone->getName());
        $this->assertNotSame($defaultTimezone, $loggerTimezone->getName());
    }

    /**
     * @test
     */
    public function crunzLoggerTimezoneIsDefaultWhenTimezoneLogOptionIsFalse()
    {
        $expectedTimezone = 'Asia/Tehran';
        $defaultTimezone = 'UTC';
        $crunzLogger = $this->createCrunzLogger(['timezone' => $expectedTimezone, 'timezone_log' => false]);
        /** @var MonologLogger $monolog */
        $monologLogger = $this->readAttribute($crunzLogger, 'logger');
        /** @var \DateTimeZone|null $loggerTimezone */
        $loggerTimezone = $this->readAttribute($monologLogger, 'timezone');
        $this->assertNotSame($expectedTimezone, $loggerTimezone->getName());
        $this->assertSame($defaultTimezone, $loggerTimezone->getName());
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
    private function createCrunzLogger(array $config = [])
    {
        $configuration = $this->createConfiguration($config);
        $tempFile = new TemporaryFile();
        $loggerFactory = new LoggerFactory($configuration);

        return $loggerFactory->create(['debug' => $tempFile->filePath()]);
    }
}
