<?php

declare(strict_types=1);

namespace Crunz\Infrastructure\Dragonmantank\CronExpression;

use Cron\CronExpression;
use Crunz\Application\Cron\CronExpressionInterface;

final class DragonmantankCronExpression implements CronExpressionInterface
{
    /** @var CronExpression */
    private $innerCronExpression;

    public function __construct(CronExpression $innerCronExpression)
    {
        $this->innerCronExpression = $innerCronExpression;
    }

    /** {@inheritdoc} */
    public function multipleRunDates(int $total, \DateTimeImmutable $now, ?\DateTimeZone $timeZone = null): array
    {
        $timeZoneNow = null !== $timeZone
            ? $now->setTimezone($timeZone)
            : $now
        ;

        $dates = $this->innerCronExpression
            ->getMultipleRunDates($total, $timeZoneNow)
        ;

        return \array_map(
            static function (\DateTime $runDate): \DateTimeImmutable {
                return \DateTimeImmutable::createFromMutable($runDate);
            },
            $dates
        );
    }
}
