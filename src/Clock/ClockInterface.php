<?php

namespace Crunz\Clock;

interface ClockInterface
{
    /** @return \DateTimeImmutable */
    public function now();
}
