<?php

namespace Crunz\Clock;

final class Clock implements ClockInterface
{
    /** @return \DateTimeImmutable */
    public function now()
    {
        return new \DateTimeImmutable();
    }
}
