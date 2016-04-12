<?php

use Crunz\Event;
use Crunz\Invoker;
use Carbon\Carbon;

class EventTest extends PHPUnit_Framework_TestCase {
    
    /**
     * The default configuration timezone.
     *
     * @var string
     */
    protected $defaultTimezone;

    public function setUp()
    {
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
        $e = new Event('php foo');
        $this->assertEquals('*/6 * * * * *',   $e->everySixMinutes()->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('0 */12 * * * *',  $e->everyTwelveHours()->getExpression());
        
        $e = new Event('php foo');
        $this->assertEquals('*/35 * * * * *',  $e->everyThirtyFiveMinutes()->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('*/578 * * * * *', $e->everyFiveHundredSeventyEightMinutes()->getExpression());

        $e = new Event('php foo');
        $e->everyFiftyMinutes()->mondays();
        
        $this->assertEquals('*/50 * * * 1 *', $e->getExpression());
        $this->assertFalse($e->isDue(new Invoker()));

    }

    /**
     * @group cronCompile
     */
    public function testUnitMethods()
    {
        $e = new Event('php foo');
        $this->assertEquals('0 * * * * *',   $e->hourly()->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('0 0 * * * *',   $e->daily()->getExpression());

        $e = new Event('php foo');
        $this->assertEquals('45 15 * * * *', $e->dailyAt('15:45')->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('0 4,8 * * * *', $e->twiceDaily(4,8)->getExpression());

         $e = new Event('php foo');
        $this->assertEquals('0 0 * * 0 *',   $e->weekly()->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('0 0 1 * * *',   $e->monthly()->getExpression());

        $e = new Event('php foo');
        $this->assertEquals('0 0 1 */3 * *', $e->quarterly()->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('0 0 1 1 * *',   $e->yearly()->getExpression());
    }

    /**
     * @group cronCompile
     */
    public function testLowLevelMethods()
    {
        $e = new Event('php foo');
        $this->assertEquals('30 1 11 4 * *', $e->on('01:30 11-04-2016')->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('45 13 * * * *', $e->on('13:45')->getExpression());

        $e = new Event('php foo');
        $this->assertEquals('45 13 * * * *', $e->at('13:45')->getExpression());

        $e = new Event('php bar');
        
        $e->minute([12, 24, 35])
          ->hour('1-5', 4, 8)
          ->dayOfMonth(1, 6, 12, 19, 25)
          ->month('1-8')
          ->dayOfWeek('mon,wed,thu');

        $this->assertEquals('12,24,35 1-5,4,8 1,6,12,19,25 1-8 mon,wed,thu *', $e->getExpression()); 

        $e = new Event('php foo');
        $this->assertEquals('45 13 * * * *', $e->cron('45 13 * * * *')->getExpression());

        $e = new Event('php foo');
        $this->assertTrue($e->isDue(new Invoker()));

    }

    /**
     * @group cronCompile
     */
    public function testWeekdayMethods()
    {
        $e = new Event('php foo');
        $this->assertEquals('* * * * 4 *', $e->thursdays()->getExpression());

        $e = new Event('php bar');
        $this->assertEquals('* * * * 5 *', $e->fridays()->getExpression());
    }

    public function testCronLifeTime()
    {
        $e = new Event('php foo');
        $this->assertFalse($e->cron('* * * * * *')->between('1-1-2015', '1-2-2015')->isDue(new Invoker()));

        $e = new Event('php foo');
        $this->assertFalse($e->cron('* * * * * *')->from('01-01-2048')->isDue(new Invoker()));

        $e = new Event('php foo');
        $this->assertFalse($e->cron('* * * * * *')->to('01-01-2015')->isDue(new Invoker()));
    }

    public function testCronConditions()
    {
        $e = new Event('php foo');
        $this->assertFalse($e->cron('* * * * * *')->when(function() { return false; })->isDue(new Invoker()));

        $e = new Event('php foo');
        $this->assertFalse($e->cron('* * * * * *')->skip(function() { return true; })->isDue(new Invoker()));
    }

    public function testOutputs()
    {
        $e = new Event('php -i');        
        $this->assertEquals('php -i >> /dev/null 2>&1 &', $e->appendOutputTo('/dev/null')->buildCommand());

        $e = new Event('php -i');        
        $this->assertEquals('php -i > /dev/null 2>&1 &', $e->sendOutputTo('/dev/null')->buildCommand());
    }

    public function testBuildCommand()
    {
        $e = new Event('php -i');

        $defaultOutput = (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';
        $this->assertSame("php -i > {$defaultOutput} 2>&1 &", $e->buildCommand());
    }

    public function testIsDue()
    {
        Carbon::setTestNow(Carbon::create(2015, 4, 12, 0, 0, 0));

        $e = new Event('php foo');
        $this->assertTrue($e->sundays()->isDue(new Invoker()));
        
        $e = new Event('php bar');
        $this->assertEquals('0 19 * * 6 *', $e->saturdays()->at('19:00')->timezone('EST')->getExpression());
        $this->assertTrue($e->isDue(new Invoker()));
             
    }

    public function testName()
    {
        $e = new Event('php foo');
        $e->description('Testing Cron');

        $this->assertEquals('Testing Cron', $e->description);
    }

}