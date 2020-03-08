<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Application\Cron;

use Crunz\Application\Cron\CronExpressionInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractCronExpressionTest extends TestCase
{
    /**
     * @test
     *
     * @param \DateTimeImmutable[] $expectedRunDates
     * @dataProvider multipleRunDatesProvider
     */
    public function multipleRunDates(
        string $cronExpressionString,
        \DateTimeImmutable $now,
        int $total,
        ?\DateTimeZone $timeZone,
        array $expectedRunDates
    ): void {
        $cronExpression = $this->createExpression($cronExpressionString);
        $runDates = $cronExpression->multipleRunDates(
            $total,
            $now,
            $timeZone
        );

        $this->assertEquals($expectedRunDates, $runDates);
    }

    /** @return iterable<string,array> */
    public function multipleRunDatesProvider(): iterable
    {
        $now = new \DateTimeImmutable('2019-01-01 11:12:13');
        $nextRuns = [new \DateTimeImmutable('2019-01-01 11:13:00')];
        yield 'one every minute' => [
            '* * * * *',
            $now,
            1,
            null,
            $nextRuns,
        ];

        $now = new \DateTimeImmutable('2019-02-01 05:09:01');
        $nextRuns = [
            new \DateTimeImmutable('2019-02-01 05:10:00'),
            new \DateTimeImmutable('2019-02-01 05:15:00'),
        ];
        yield 'two every five minutes' => [
            '*/5 * * * *',
            $now,
            2,
            null,
            $nextRuns,
        ];

        $timeZone = new \DateTimeZone('Europe/Warsaw');
        $now = new \DateTimeImmutable('2019-03-02 07:02:01', $timeZone);
        $nextRuns = [
            new \DateTimeImmutable('2019-03-02 07:10:00', $timeZone),
            new \DateTimeImmutable('2019-03-02 07:20:00', $timeZone),
        ];
        yield 'two timezone aware' => [
            '*/10 * * * *',
            $now,
            2,
            null,
            $nextRuns,
        ];
    }

    abstract protected function createExpression(string $cronExpression): CronExpressionInterface;
}
