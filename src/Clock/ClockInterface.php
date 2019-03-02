<?php

declare(strict_types=1);

namespace Crunz\Clock;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
