<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase;

use Crunz\Clock\ClockInterface;

final class TestClock implements ClockInterface
{
    /** @var \DateTimeInterface */
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
