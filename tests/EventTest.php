<?php

use Crunz\Event;
use Carbon\Carbon;

class EventTest extends PHPUnit_Framework_TestCase {

    /**
     * The default configuration timezone.
     *
     * @var string
     */
    protected $defaultTimezone;

    /**
     * Unique identifier for the event
     *
     * @var string
     */
    protected $id;

    public function setUp()
    {
        $this->id = uniqid();
        
        $this->defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    public function tearDown()
    {
        date_default_timezone_set($this->defaultTimezone);
        Carbon::setTestNow(null);
    }

    /**
     * @group cronCompile
     */
    public function testDynamicMethods()
    {
        $e = new Event($this->id, 'php foo');
        $this->assertEquals('*/6 * * * * *',   $e->everySixMinutes()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 */12 * * * *',  $e->everyTwelveHours()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('*/35 * * * * *',  $e->everyThirtyFiveMinutes()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('*/578 * * * * *', $e->everyFiveHundredSeventyEightMinutes()->getExpression());

        $e = new Event($this->id, 'php foo');
        $e->everyFiftyMinutes()->mondays();

        $this->assertEquals('*/50 * * * 1 *', $e->getExpression());
        $this->assertFalse($e->isDue());

    }

    /**
     * @group cronCompile
     */
    public function testUnitMethods()
    {
        $id = uniqid();

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('0 * * * * *',   $e->hourly()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 0 * * * *',   $e->daily()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('45 15 * * * *', $e->dailyAt('15:45')->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 4,8 * * * *', $e->twiceDaily(4,8)->getExpression());

         $e = new Event($this->id, 'php foo');
        $this->assertEquals('0 0 * * 0 *',   $e->weekly()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 0 1 * * *',   $e->monthly()->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('0 0 1 */3 * *', $e->quarterly()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 0 1 1 * *',   $e->yearly()->getExpression());
    }

    /**
     * @group cronCompile
     */
    public function testLowLevelMethods()
    {
        $e = new Event($this->id, 'php foo');
        $this->assertEquals('30 1 11 4 * *', $e->on('01:30 11-04-2016')->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('45 13 * * * *', $e->on('13:45')->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('45 13 * * * *', $e->at('13:45')->getExpression());

        $e = new Event($this->id, 'php bar');

        $e->minute([12, 24, 35])
          ->hour('1-5', 4, 8)
          ->dayOfMonth(1, 6, 12, 19, 25)
          ->month('1-8')
          ->dayOfWeek('mon,wed,thu');

        $this->assertEquals('12,24,35 1-5,4,8 1,6,12,19,25 1-8 mon,wed,thu *', $e->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertEquals('45 13 * * * *', $e->cron('45 13 * * * *')->getExpression());

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->isDue());

    }

    /**
     * @group cronCompile
     */
    public function testWeekdayMethods()
    {
        $e = new Event($this->id, 'php foo');
        $this->assertEquals('* * * * 4 *', $e->thursdays()->getExpression());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('* * * * 5 *', $e->fridays()->getExpression());
    }

    public function testCronLifeTime()
    {
        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * * *')->between('1-1-2015', '1-2-2015')->isDue());

        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * * *')->from('01-01-2048')->isDue());

        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * * *')->to('01-01-2015')->isDue());
    }

    public function testCronConditions()
    {
        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * * *')->when(function() { return false; })->isDue());

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->cron('* * * * * *')->when(function() { return true; })->isDue());

        $e = new Event($this->id, 'php foo');
        $this->assertFalse($e->cron('* * * * * *')->skip(function() { return true; })->isDue());

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->cron('* * * * * *')->skip(function() { return false; })->isDue());
    }

    public function testBuildCommand()
    {
        $e = new Event($this->id, 'php -i');

        $this->assertSame("php -i", $e->buildCommand());
    }

    public function testIsDue()
    {
        Carbon::setTestNow(Carbon::create(2015, 4, 12, 0, 0, 0));

        $e = new Event($this->id, 'php foo');
        $this->assertTrue($e->sundays()->isDue());

        $e = new Event($this->id, 'php bar');
        $this->assertEquals('0 19 * * 6 *', $e->saturdays()->at('19:00')->timezone('EST')->getExpression());
        $this->assertTrue($e->isDue());

    }

    public function testName()
    {
        $e = new Event($this->id, 'php foo');
        $e->description('Testing Cron');

        $this->assertEquals('Testing Cron', $e->description);
    }

}
