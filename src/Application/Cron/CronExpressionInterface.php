<?php

declare(strict_types=1);

namespace Crunz\Application\Cron;

interface CronExpressionInterface
{
    /** @return \DateTimeImmutable[] */
    public function multipleRunDates(
        int $total,
        \DateTimeImmutable $now,
        ?\DateTimeZone $timeZone = null
    ): array;
}
