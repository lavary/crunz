<?php

declare(strict_types=1);

namespace Crunz\Infrastructure\Dragonmantank\CronExpression;

use Cron\CronExpression;
use Crunz\Application\Cron\CronExpressionFactoryInterface;
use Crunz\Application\Cron\CronExpressionInterface;

final class DragonmantankCronExpressionFactory implements CronExpressionFactoryInterface
{
    public function createFromString(string $cronExpression): CronExpressionInterface
    {
        $innerCronExpression = CronExpression::factory($cronExpression);

        return new DragonmantankCronExpression($innerCronExpression);
    }
}
