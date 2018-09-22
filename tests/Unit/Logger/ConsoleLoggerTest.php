<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Logger;

use Crunz\Logger\ConsoleLogger;
use Crunz\Logger\ConsoleLoggerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleLoggerTest extends TestCase
{
    /**
     * @test
     * @dataProvider verbosityProvider
     */
    public function loggerWritesNormalOnlyWithSuitableVerbosity($ioVerbosity)
    {
        $expectedCalls = ($ioVerbosity >= ConsoleLoggerInterface::VERBOSITY_NORMAL) ? 1 : 0;
        $mockSymfonyStyle = $this->mockSymfonyStyle($ioVerbosity);
        $mockSymfonyStyle
            ->expects($this->exactly($expectedCalls))
            ->method('writeln')
        ;

        $consoleLogger = new ConsoleLogger($mockSymfonyStyle);
        $consoleLogger->normal('Some message');
    }

    /**
     * @test
     * @dataProvider verbosityProvider
     */
    public function loggerWritesVerboseOnlyWithSuitableVerbosity($ioVerbosity)
    {
        $expectedCalls = ($ioVerbosity >= ConsoleLoggerInterface::VERBOSITY_VERBOSE) ? 1 : 0;
        $mockSymfonyStyle = $this->mockSymfonyStyle($ioVerbosity);
        $mockSymfonyStyle
            ->expects($this->exactly($expectedCalls))
            ->method('writeln')
        ;

        $consoleLogger = new ConsoleLogger($mockSymfonyStyle);
        $consoleLogger->verbose('Some message');
    }

    /**
     * @test
     * @dataProvider verbosityProvider
     */
    public function loggerWritesVeryVerboseOnlyWithSuitableVerbosity($ioVerbosity)
    {
        $expectedCalls = ($ioVerbosity >= ConsoleLoggerInterface::VERBOSITY_VERY_VERBOSE) ? 1 : 0;
        $mockSymfonyStyle = $this->mockSymfonyStyle($ioVerbosity);
        $mockSymfonyStyle
            ->expects($this->exactly($expectedCalls))
            ->method('writeln')
        ;

        $consoleLogger = new ConsoleLogger($mockSymfonyStyle);
        $consoleLogger->veryVerbose('Some message');
    }

    /**
     * @test
     * @dataProvider verbosityProvider
     */
    public function loggerWritesDebugOnlyWithSuitableVerbosity($ioVerbosity)
    {
        $expectedCalls = ($ioVerbosity >= ConsoleLoggerInterface::VERBOSITY_DEBUG) ? 1 : 0;
        $mockSymfonyStyle = $this->mockSymfonyStyle($ioVerbosity);
        $mockSymfonyStyle
            ->expects($this->exactly($expectedCalls))
            ->method('writeln')
        ;

        $consoleLogger = new ConsoleLogger($mockSymfonyStyle);
        $consoleLogger->debug('Some message');
    }

    /**
     * @return \Generator
     */
    public function verbosityProvider()
    {
        yield 'quiet' => [ConsoleLoggerInterface::VERBOSITY_QUIET];
        yield 'normal' => [ConsoleLoggerInterface::VERBOSITY_NORMAL];
        yield 'verbose' => [ConsoleLoggerInterface::VERBOSITY_VERBOSE];
        yield 'veryVerbose' => [ConsoleLoggerInterface::VERBOSITY_VERY_VERBOSE];
        yield 'debug' => [ConsoleLoggerInterface::VERBOSITY_DEBUG];
    }

    /**
     * @param $ioVerbosity
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|SymfonyStyle
     */
    private function mockSymfonyStyle($ioVerbosity)
    {
        $mock = $this->createMock(SymfonyStyle::class);

        $mock
            ->method('getVerbosity')
            ->willReturn($ioVerbosity)
        ;

        return $mock;
    }
}
