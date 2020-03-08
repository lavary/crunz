<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Infrastructure\Dragonmantank\CronExpression;

use Cron\CronExpression;
use Crunz\Application\Cron\CronExpressionInterface;
use Crunz\Infrastructure\Dragonmantank\CronExpression\DragonmantankCronExpression;
use Crunz\Tests\Unit\Application\Cron\AbstractCronExpressionTest;

final class DragonmantankCronExpressionTest extends AbstractCronExpressionTest
{
    protected function createExpression(string $cronExpression): CronExpressionInterface
    {
        $innerCronExpression = CronExpression::factory($cronExpression);

        return new DragonmantankCronExpression($innerCronExpression);
    }
}
