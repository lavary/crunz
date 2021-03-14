<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Logger;

use Crunz\Logger\ConsoleLogger;
use Crunz\Logger\ConsoleLoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleLoggerTest extends TestCase
{
    /**
     * @test
     * @dataProvider verbosityProvider
     */
    public function logger_writes_normal_only_with_suitable_verbosity(int $ioVerbosity): void
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
    public function logger_writes_verbose_only_with_suitable_verbosity(int $ioVerbosity): void
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
    public function logger_writes_very_verbose_only_with_suitable_verbosity(int $ioVerbosity): void
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
    public function logger_writes_debug_only_with_suitable_verbosity(int $ioVerbosity): void
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

    /** @return iterable<string,array<int>> */
    public function verbosityProvider(): iterable
    {
        yield 'quiet' => [ConsoleLoggerInterface::VERBOSITY_QUIET];
        yield 'normal' => [ConsoleLoggerInterface::VERBOSITY_NORMAL];
        yield 'verbose' => [ConsoleLoggerInterface::VERBOSITY_VERBOSE];
        yield 'veryVerbose' => [ConsoleLoggerInterface::VERBOSITY_VERY_VERBOSE];
        yield 'debug' => [ConsoleLoggerInterface::VERBOSITY_DEBUG];
    }

    /** @return MockObject|SymfonyStyle */
    private function mockSymfonyStyle(int $ioVerbosity)
    {
        $mock = $this->createMock(SymfonyStyle::class);

        $mock
            ->method('getVerbosity')
            ->willReturn($ioVerbosity)
        ;

        return $mock;
    }
}
