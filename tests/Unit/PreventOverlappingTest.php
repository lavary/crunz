<?php

namespace Crunz\Tests\Unit;

use Crunz\Configuration\Configuration;
use Crunz\Event;
use Crunz\EventRunner;
use Crunz\HttpClient\HttpClientInterface;
use Crunz\Invoker;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Logger\LoggerFactory;
use Crunz\Mailer;
use Crunz\Schedule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\StoreInterface;

final class PreventOverlappingTest extends TestCase
{
    /**
     * @dataProvider lockDataProvider
     * @test
     *
     * @param StoreInterface|null $store
     */
    public function testPreventOverlapping(StoreInterface $store = null)
    {
        $command = PHP_BINARY . ' -r "usleep(250000);"';

        $schedule1 = new Schedule();
        $event1 = $schedule1->run($command)
            ->description('Show PHP version')
            ->preventOverlapping()
            ->everyMinute();

        // Exact same event, might come from another schedule:run
        $schedule2 = new Schedule();
        $event2 = $schedule2->run($command)
            ->description('Show PHP version')
            ->preventOverlapping()
            ->everyMinute();

        $eventRunner = new MyEventRunner(
            new Invoker(),
            $this->createMock(Configuration::class),
            $this->createMock(Mailer::class),
            $this->createMock(LoggerFactory::class),
            $this->createMock(HttpClientInterface::class),
            $this->createMock(ConsoleLoggerInterface::class)
        );

        // Both events are due, none is locked yet
        $this->assertEquals([$event1], $schedule1->dueEvents(new \DateTimeZone(date_default_timezone_get())));
        $this->assertEquals([$event2], $schedule2->dueEvents(new \DateTimeZone(date_default_timezone_get())));

        // Start schedule1, so that event1 will be locked
        $eventRunner->handle(new NullOutput(), [$schedule1]);

        // Event is locked and therefore not due (even over the boundaries of multiple independent events and schedules)
        $this->assertEquals([], $schedule1->dueEvents(new \DateTimeZone(date_default_timezone_get())));
        $this->assertEquals([], $schedule2->dueEvents(new \DateTimeZone(date_default_timezone_get())));

        // Assert only one process is running
        $this->assertTrue($event1->getProcess()->isRunning());
        $this->assertNull($event2->getProcess(), 'Event 2 should not be running');

        // Assert lock on both events
        $this->assertTrue($event1->isLocked());
        $this->assertTrue($event2->isLocked());

        // Wait until the process finished
        while ($event1->isLocked()) {
            // Verify the events are still locked
            $this->assertEquals([], $schedule1->dueEvents(new \DateTimeZone(date_default_timezone_get())));
            $this->assertEquals([], $schedule2->dueEvents(new \DateTimeZone(date_default_timezone_get())));
            $this->assertTrue($event1->isLocked());
            $this->assertTrue($event2->isLocked());

            $eventRunner->manageStartedEvents();
            usleep(50000);
        }

        // Assert both locks were removed
        $this->assertFalse($event1->isLocked());
        $this->assertFalse($event2->isLocked());
    }

    public function lockDataProvider()
    {
        return [
            [null], // Default file locking
            [new FlockStore()],
        ];
    }
}

class MyEventRunner extends EventRunner
{
    /**
     * Manage the running processes.
     *
     * This is a simplified version that checks whether the processes are still running and returns afterwards.
     *
     * This adoption is non-blocking / does not wait for all processes to finish, because otherwise we could not test
     * the locking in a single threaded manner.
     */
    public function manageStartedEvents()
    {
        foreach ($this->schedules as $scheduleKey => $schedule) {
            $events = $schedule->events();

            /** @var Event $event */
            foreach ($events as $eventKey => $event) {
                $proc = $event->getProcess();
                if ($proc->isRunning()) {
                    continue;
                }

                // Dismiss the event after it's finished
                $this->invoke($event->afterCallbacks());
                $schedule->dismissEvent($eventKey);
            }

            if (!\count($schedule->events())) {
                $this->invoke($schedule->afterCallbacks());
                unset($this->schedules[$scheduleKey]);
            }
        }
    }
}
