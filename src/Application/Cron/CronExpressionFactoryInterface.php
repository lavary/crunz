<?php

declare(strict_types=1);

namespace Crunz\Application\Cron;

interface CronExpressionFactoryInterface
{
    public function createFromString(string $cronExpression): CronExpressionInterface;
}
