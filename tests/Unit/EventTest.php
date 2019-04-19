<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\Event;
use Crunz\Task\TaskException;
use Crunz\Tests\TestCase\TestClock;
use PHPUnit\Framework\TestCase;
use SuperClosure\Serializer;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Lock\StoreInterface;

final class EventTest extends TestCase
{
    /**
     * The default configuration timezone.
     *
     * @var string
     */
    protected $defaultTimezone;

    /**
     * Unique identifier for the event.
     *
     * @var string
     */
    protected $id;

    public function setUp(): void
    {
        $this->id = \uniqid('crunz', true);

        $this->defaultTimezone = \date_default_timezone_get();
        \date_default_timezone_set('UTC');
    }

    public function tearDown(): void
    {
        \date_default_timezone_set($this->defaultTimezone);
    }

    /**
     * @group cronCompile
     */
    public function testDynamicMethods(): void
    {
        $e = new Event($this->id, 'php foo');
        $this->assertEquals('*/6 * * * *', $e->everySixMinutes()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 */12 * * *', $e->everyTwelveHours()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('*/35 * * * *', $e->everyThirtyFiveMinutes()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('*/578 * * * *', $e->everyFiveHundredSeventyEightMinutes()->getExpression());

        $this->setClockNow(new \DateTimeImmutable('2018-10-23 11:33:18'));

        $e = new Event($this->id, 'php foo');
        $e->everyFiftyMinutes()->mondays();

        $this->assertEquals('*/50 * * * 1', $e->getExpression());
        $this->assertFalse($e->isDue(new \DateTimeZone('UTC')));
    }

    /**
     * @group cronCompile
     */
    public function testUnitMethods(): void
    {
        $e = new Event($this->id, 'php foo');
        $this->assertEquals('0 * * * *', $e->hourly()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 0 * * *', $e->daily()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('45 15 * * *', $e->dailyAt('15:45')->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 4,8 * * *', $e->twiceDaily(4, 8)->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('0 0 * * 0', $e->weekly()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 0 1 * *', $e->monthly()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('0 0 1 */3 *', $e->quarterly()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 0 1 1 *', $e->yearly()->getExpression());
    }

    /**
     * @group cronCompile
     */
    public function testLowLevelMethods(): void
    {
        $timezone = new \DateTimeZone('UTC');

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('30 1 11 4 *', $e->on('01:30 11-04-2016')->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('45 13 * * *', $e->on('13:45')->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('45 13 * * *', $e->at('13:45')->getExpression());

        $e = new Event($this->id, 'php bar');

        $e->minute([12, 24, 35])
          ->hour('1-5', 4, 8)
          ->dayOfMonth(1, 6, 12, 19, 25)
          ->month('1-8')
          ->dayOfWeek('mon,wed,thu');

        $this->assertEquals('12,24,35 1-5,4,8 1,6,12,19,25 1-8 mon,wed,thu', $e->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('45 13 * * *', $e->cron('45 13 * * *')->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->isDue($timezone));
    }

    /**
     * @group cronCompile
     */
    public function testWeekdayMethods(): void
    {
        $e = new Event($this->id, 'php qux');
        $this->assertEquals('* * * * 2', $e->tuesdays()->getExpression());

        $e = new Event($this->id, 'php flob');
        $this->assertEquals('* * * * 3', $e->wednesdays()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('* * * * 4', $e->thursdays()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('* * * * 5', $e->fridays()->getExpression());

        $e = new Event($this->id, 'php baz');
        $this->assertEquals('* * * * 1-5', $e->weekdays()->getExpression());

        $e = new Event($this->id, 'php bla');
        $this->assertEquals('30 1 * * 2', $e->weeklyOn('2', '01:30')->getExpression());
    }

    public function testCronLifeTime(): void
    {
        $timezone = new \DateTimeZone('UTC');

        $event = new Event($this->id, 'php foo');
        $this->assertFalse(
            $event
                ->between('2015-01-01', '2015-01-02')
                ->isDue($timezone)
            )
        ;

        $futureDate = new \DateTimeImmutable('+1 year');

        $event = new Event($this->id, 'php foo');
        $this->assertFalse(
            $event
                ->from($futureDate->format('Y-m-d'))
                ->isDue($timezone)
            )
        ;

        $event = new Event($this->id, 'php foo');
        $this->assertFalse(
            $event
                ->to('2015-01-01')
                ->isDue($timezone)
            )
        ;
    }

    public function testCronConditions(): void
    {
        $timezone = new \DateTimeZone('UTC');

        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * *')->when(function () { return false; })->isDue($timezone));

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->cron('* * * * *')->when(function () { return true; })->isDue($timezone));

        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * *')->skip(function () { return true; })->isDue($timezone));

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->cron('* * * * *')->skip(function () { return false; })->isDue($timezone));
    }

    /** @test */
    public function moreThanFivePartsInCronExpressionResultsInException(): void
    {
        $this->expectException(TaskException::class);
        $this->expectExceptionMessage("Expression '* * * * * *' has more than five parts and this is not allowed.");

        $e = new Event(1, 'php foo -v');
        $e->cron('* * * * * *');
    }

    /** @test */
    public function callingSetProcessIsDeprecated(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("Method 'setProcess' is not supported.");

        $event = new Event(1, 'php foo -v');
        $event->setProcess();
    }

    public function testBuildCommand(): void
    {
        $e = new Event($this->id, 'php -i');

        $this->assertSame('php -i', $e->buildCommand());
    }

    public function testIsDue(): void
    {
        $timezone = new \DateTimeZone('UTC');
        $this->setClockNow(new \DateTimeImmutable('2015-04-12 00:00:00', $timezone));

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->sundays()->isDue($timezone));

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 19 * * 6', $e->saturdays()->at('19:00')->timezone('EST')->getExpression());
        $this->assertTrue($e->isDue($timezone));

        $e = new Event($this->id, 'php bar');
        $this->setClockNow(new \DateTimeImmutable(\date('Y') . '-04-12 00:00:00'));
        $this->assertTrue($e->on('00:00 ' . \date('Y') . '-04-12')->isDue($timezone));
    }

    public function testName(): void
    {
        $e = new Event($this->id, 'php foo');
        $e->description('Testing Cron');

        $this->assertEquals('Testing Cron', $e->description);
    }

    /** @test */
    public function inChangeWorkingDirectoryInBuildCommandOnWindows(): void
    {
        if (!$this->isWindows()) {
            $this->markTestSkipped('Required Windows OS.');
        }

        $workingDir = 'C:\\windows\\temp';
        $event = new Event($this->id, 'php -v');

        $event->in($workingDir);

        $this->assertSame("cd /d {$workingDir} & php -v", $event->buildCommand());
    }

    /** @test */
    public function inChangeWorkingDirectoryInBuildCommandOnUnix(): void
    {
        if ($this->isWindows()) {
            $this->markTestSkipped('Required Unix-based OS.');
        }

        $event = new Event($this->id, 'php -v');

        $event->in('/tmp');

        $this->assertSame('cd /tmp; php -v', $event->buildCommand());
    }

    /** @test */
    public function onDoNotRunTaskEveryMinute(): void
    {
        $event = new Event($this->id, 'php -i');

        $event->on('Thursday 8:00');

        $this->assertSame('0 8 * * *', $event->getExpression());
    }

    /** @test */
    public function settingUserPrependSudoToCommand(): void
    {
        if ($this->isWindows()) {
            $this->markTestSkipped('Required Unix-based OS.');
        }

        $event = new Event($this->id, 'php -v');

        $event->user('john');

        $this->assertSame('sudo -u john php -v', $event->buildCommand());
    }

    /** @test */
    public function customUserAndCwd(): void
    {
        if ($this->isWindows()) {
            $this->markTestSkipped('Required Unix-based OS.');
        }

        $event = new Event($this->id, 'php -i');

        $event->user('john');
        $event->in('/var/test');

        $this->assertSame('sudo -u john cd /var/test; sudo -u john php -i', $event->buildCommand());
    }

    /** @test */
    public function notImplementedUserChangeOnWindows(): void
    {
        if (!$this->isWindows()) {
            $this->markTestSkipped('Required Windows OS.');
        }

        $this->expectException(\Crunz\Exception\NotImplementedException::class);
        $this->expectExceptionMessage('Changing user on Windows is not implemented.');

        $event = new Event($this->id, 'php -i');

        $event->user('john');
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function closureCommandHaveFullBinaryPaths(): void
    {
        if (!\defined('CRUNZ_BIN')) {
            \define('CRUNZ_BIN', __FILE__);
        }

        $closure = function () {
            return 0;
        };
        $serializedClosure = (new Serializer())->serialize($closure);
        $queryClosure = \http_build_query([$serializedClosure]);
        $crunzBin = CRUNZ_BIN;

        $event = new Event($this->id, $closure);

        $command = $event->buildCommand();

        $this->assertSame(PHP_BINARY . " {$crunzBin} closure:run {$queryClosure}", $command);
    }

    /** @test */
    public function wholeOutputCatchesStdoutAndStderr(): void
    {
        $command = "php -r \"echo 'Test output'; throw new \Exception('Exception output');\"";
        $event = new Event(\uniqid('c', true), $command);
        $event->start();
        $process = $event->getProcess();

        while ($process->isRunning()) {
            \usleep(20000); // wait 20 ms
        }

        $wholeOutput = $event->wholeOutput();

        $this->assertContains(
            'Test output',
            $wholeOutput,
            'Missing standard output'
        );
        $this->assertContains(
            'Exception output',
            $wholeOutput,
            'Missing error output'
        );
    }

    /** @test */
    public function taskWillPreventOverlappingWithDefaultStore(): void
    {
        $this->assertPreventOverlapping();
    }

    /** @test */
    public function taskWillPreventOverlappingWithSemaphoreStore(): void
    {
        if (!\extension_loaded('sysvsem')) {
            $this->markTestSkipped('Semaphore extension not installed.');
        }

        $this->assertPreventOverlapping(new SemaphoreStore());
    }

    private function assertPreventOverlapping(StoreInterface $store = null): void
    {
        $event = $this->createPreventOverlappingEvent($store);
        $event2 = $this->createPreventOverlappingEvent($store);

        $event->start();

        $this->assertFalse($event2->isDue(new \DateTimeZone('UTC')));
    }

    private function createPreventOverlappingEvent(StoreInterface $store = null): Event
    {
        $command = "php -r 'sleep(2);'";

        $event = new Event(\uniqid('c', true), $command);
        $event->preventOverlapping($store);
        $event->everyMinute();

        return $event;
    }

    private function setClockNow(\DateTimeImmutable $dateTime): void
    {
        $testClock = new TestClock($dateTime);
        $reflection = new \ReflectionClass(Event::class);
        $property = $reflection->getProperty('clock');
        $property->setAccessible(true);
        $property->setValue($testClock);
    }

    private function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
