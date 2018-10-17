<?php

declare(strict_types=1);

namespace Crunz\Clock;

interface ClockInterface
{
    /** @return \DateTimeImmutable */
    public function now();
}
