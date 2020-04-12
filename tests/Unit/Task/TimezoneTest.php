<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Task;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Exception\EmptyTimezoneException;
use Crunz\Task\Timezone;
use Crunz\Tests\TestCase\Logger\NullLogger;
use PHPUnit\Framework\TestCase;

final class TimezoneTest extends TestCase
{
    /** @test */
    public function configuredTimezoneCannotBeEmpty(): void
    {
        $mockConfiguration = $this->createMock(ConfigurationInterface::class);

        $this->expectException(EmptyTimezoneException::class);

        $taskTimezone = new Timezone(
            $mockConfiguration,
            new NullLogger()
        );
        $taskTimezone->timezoneForComparisons();
    }
}
