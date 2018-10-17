<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Task;

use Crunz\Configuration\Configuration;
use Crunz\Exception\EmptyTimezoneException;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Task\Timezone;
use Crunz\Timezone\ProviderInterface;
use PHPUnit\Framework\TestCase;

class TimezoneTest extends TestCase
{
    /** @test */
    public function configuredTimezoneCannotBeEmpty(): void
    {
        $mockConfiguration = $this->createMock(Configuration::class);

        $this->expectException(EmptyTimezoneException::class);

        $taskTimezone = new Timezone(
            $mockConfiguration,
            $this->createTimezoneDummy(),
            $this->createConsoleLoggerDummy()
        );
        $taskTimezone->timezoneForComparisons();
    }

    private function createTimezoneDummy(): ProviderInterface
    {
        return new class() implements ProviderInterface {
            /**
             * @return \DateTimeZone
             */
            public function defaultTimezone()
            {
                return new \DateTimeZone(\date_default_timezone_get());
            }
        };
    }

    private function createConsoleLoggerDummy(): ConsoleLoggerInterface
    {
        return new class() implements ConsoleLoggerInterface {
            public function normal($message)
            {
            }

            public function verbose($message)
            {
            }

            public function veryVerbose($message)
            {
            }

            public function debug($message)
            {
            }
        };
    }
}
