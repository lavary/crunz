<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Task;

use Crunz\Exception\EmptyTimezoneException;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\FakeConfiguration;
use Crunz\Tests\TestCase\Logger\NullLogger;
use PHPUnit\Framework\TestCase;

final class TimezoneTest extends TestCase
{
    /** @test */
    public function configured_timezone_cannot_be_empty(): void
    {
        $this->expectException(EmptyTimezoneException::class);

        $taskTimezone = new Timezone(
            new FakeConfiguration(['timezone' => null]),
            new NullLogger()
        );
        $taskTimezone->timezoneForComparisons();
    }
}
