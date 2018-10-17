<?php

declare(strict_types=1);

namespace Crunz\Clock;

final class Clock implements ClockInterface
{
    /** @return \DateTimeImmutable */
    public function now()
    {
        return new \DateTimeImmutable();
    }
}
