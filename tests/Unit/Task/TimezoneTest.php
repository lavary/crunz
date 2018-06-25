<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Task;

use Crunz\Configuration\Configuration;
use Crunz\Exception\EmptyTimezoneException;
use Crunz\Task\Timezone;
use PHPUnit\Framework\TestCase;

class TimezoneTest extends TestCase
{
    /** @test */
    public function configuredTimezoneCannotBeEmpty(): void
    {
        $mockConfiguration = $this->createMock(Configuration::class);

        $this->expectException(EmptyTimezoneException::class);

        $taskTimezone = new Timezone($mockConfiguration);
        $taskTimezone->timezoneForComparisons();
    }
}
