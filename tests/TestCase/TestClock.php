<?php

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

    /** @return \DateTimeImmutable */
    public function now()
    {
        return $this->now;
    }
}
