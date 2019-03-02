<?php

declare(strict_types=1);

namespace Crunz\Clock;

final class Clock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
