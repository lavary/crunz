<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase;

use Crunz\Clock\ClockInterface;

final class TestClock implements ClockInterface
{
    /** @var \DateTimeImmutable */
    private $now;

    public function __construct(\DateTimeImmutable $now)
    {
        $this->now = $now;
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
