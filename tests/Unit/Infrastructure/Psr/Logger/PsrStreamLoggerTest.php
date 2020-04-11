<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Infrastructure\Psr\Logger;

use Crunz\Exception\CrunzException;
use Crunz\Infrastructure\Psr\Logger\PsrStreamLogger;
use Crunz\Tests\TestCase\Faker;
use Crunz\Tests\TestCase\TemporaryFile;
use Crunz\Tests\TestCase\TestClock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class PsrStreamLoggerTest extends TestCase
{
    /** @dataProvider supportedLevelsProvider */
    public function test_supported_levels_are_logged(string $level): void
    {
        $message = Faker::words(5);
        $now = new \DateTimeImmutable();
        $tempFile = new TemporaryFile();
        $logger = $this->createLogger($tempFile, $now);

        $logger->log($level, $message);

        $this->assertSame(
            $this->formatLine(
                $now,
                $message,
                $level
            ),
            $tempFile->contents()
        );
    }

    /** @dataProvider unsupportedLevelsProvider */
    public function test_unsupported_levels_are_ignored(string $level): void
    {
        $message = Faker::words(5);
        $tempFile = new TemporaryFile();
        $logger = $this->createLogger($tempFile);

        $logger->log($level, $message);

        $this->assertEmpty($tempFile->contents());
    }

    /** @dataProvider supportedLevelsProvider */
    public function test_empty_context_is_ignored(string $level): void
    {
        $message = Faker::words(5);
        $now = new \DateTimeImmutable();
        $tempFile = new TemporaryFile();
        $logger = $this->createLogger(
            $tempFile,
            $now,
            null,
            true
        );

        $logger->log($level, $message);

        $this->assertSame(
            $this->formatLine(
                $now,
                $message,
                $level,
                true
            ),
            $tempFile->contents()
        );
    }

    /** @dataProvider supportedLevelsProvider */
    public function test_date_use_passed_time_zone(string $level): void
    {
        $timeZone = Faker::timeZone();
        $message = Faker::words(5);
        $now = new \DateTimeImmutable();
        $tempFile = new TemporaryFile();
        $logger = $this->createLogger(
            $tempFile,
            $now,
            $timeZone,
            false,
            true
        );

        $logger->log($level, $message);

        $this->assertSame(
            $this->formatLine(
                $now,
                $message,
                $level,
                false,
                true,
                false,
                $timeZone
            ),
            $tempFile->contents()
        );
    }

    /** @dataProvider supportedLevelsProvider */
    public function test_logging_with_allowed_line_breaks(string $level): void
    {
        $message = Faker::words(1) . "\n" . Faker::words(1);
        $now = new \DateTimeImmutable();
        $tempFile = new TemporaryFile();
        $logger = $this->createLogger(
            $tempFile,
            $now,
            null,
            false,
            false,
            true
        );

        $logger->log($level, $message);

        $this->assertSame(
            $this->formatLine(
                $now,
                $message,
                $level,
                false,
                false,
                true
            ),
            $tempFile->contents()
        );
    }

    /** @dataProvider supportedLevelsProvider */
    public function test_logging_with_disallowed_line_breaks(string $level): void
    {
        $message = Faker::words(1) . "\n" . Faker::words(1);
        $now = new \DateTimeImmutable();
        $tempFile = new TemporaryFile();
        $logger = $this->createLogger($tempFile, $now);

        $logger->log($level, $message);

        $this->assertSame(
            $this->formatLine(
                $now,
                $message,
                $level
            ),
            $tempFile->contents()
        );
    }

    public function supportedLevelsProvider(): iterable
    {
        yield 'info' => ['info'];
        yield 'error' => ['error'];
    }

    public function unsupportedLevelsProvider(): iterable
    {
        yield 'emergency' => ['emergency'];
        yield 'alert' => ['alert'];
        yield 'critical' => ['critical'];
        yield 'warning' => ['warning'];
        yield 'notice' => ['notice'];
        yield 'debug' => ['debug'];
    }

    private function createLogger(
        TemporaryFile $temporaryFile,
        ?\DateTimeImmutable $now = null,
        ?\DateTimeZone $timeZone = null,
        bool $ignoreEmptyContext = false,
        bool $timezoneLog = false,
        bool $allowLineBreaks = false
    ): LoggerInterface {
        $clock = new TestClock($now ?? Faker::dateTime());

        return new PsrStreamLogger(
            $timeZone ?? Faker::timeZone(),
            $clock,
            $temporaryFile->filePath(),
            $temporaryFile->filePath(),
            $ignoreEmptyContext,
            $timezoneLog,
            $allowLineBreaks
        );
    }

    private function formatLine(
        \DateTimeImmutable $date,
        string $message,
        string $level,
        bool $ignoreEmptyContext = false,
        bool $timeZoneLog = false,
        bool $allowLineBreaks = false,
        ?\DateTimeZone $timeZone = null
    ): string {
        $context = '[] []';
        if (true === $ignoreEmptyContext) {
            $context = ' ';
        }

        if (true === $timeZoneLog && null === $timeZone) {
            throw new CrunzException("TimeZone must be specified to use 'timeZoneLog'.");
        }

        if (true === $timeZoneLog) {
            $date = $date->setTimezone($timeZone);
        }

        if (!$allowLineBreaks) {
            $message = \str_replace(
                [
                    "\r\n",
                    "\r",
                    "\n",
                ],
                ' ',
                $message
            );
        }

        $dateString = $date->format('Y-m-d H:i:s');
        $levelName = \mb_strtoupper($level);

        return "[{$dateString}] crunz.{$levelName}: {$message} {$context}" . PHP_EOL;
    }
}
