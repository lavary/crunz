<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\EventRunner;
use Crunz\HttpClient\HttpClientInterface;
use Crunz\Invoker;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Logger\LoggerFactory;
use Crunz\Mailer;
use Crunz\Schedule;
use Crunz\Tests\TestCase\FakeConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\StoreInterface;

final class EventRunnerTest extends TestCase
{
    /** @test */
    public function urlIsPingedBefore(): void
    {
        $url = 'https://ping-befo.re/';
        $output = $this->createMock(OutputInterface::class);

        $eventRunner = $this->createEventRunnerForPing($url);

        $schedule = new Schedule();
        $event = $schedule->run('php -v');
        $event->pingBefore($url);

        $eventRunner->handle($output, [$schedule]);
    }

    /** @test */
    public function urlIsPingedAfter(): void
    {
        $url = 'https://ping-aft.er/';
        $output = $this->createMock(OutputInterface::class);

        $eventRunner = $this->createEventRunnerForPing($url);

        $schedule = new Schedule();
        $event = $schedule->run('php -v');
        $event->thenPing($url);

        $eventRunner->handle($output, [$schedule]);
    }

    public function testEventLoggingConfiguration(): void
    {
        $logTarget = 'event.log';

        // create schedule with event that changes logging configuration
        $schedule = new Schedule();
        $schedule->run('php -v')
            ->appendOutputTo($logTarget)
        ;

        // mock the LoggerFactory
        $loggerFactory = $this->createMock(LoggerFactory::class);
        $loggerFactory->expects($this->once())
            ->method('createEvent')
            ->with($logTarget);

        // create an EventRunner to handle the Schedule
        $eventRunner = new EventRunner(
            $this->createMock(Invoker::class),
            new FakeConfiguration(),
            $this->createMock(Mailer::class),
            $loggerFactory,
            $this->createMock(HttpClientInterface::class),
            $this->createMock(ConsoleLoggerInterface::class)
        );

        $output = $this->createMock(OutputInterface::class);
        $eventRunner->handle($output, [$schedule]);
    }
    
    public function testLockIsReleasedOnError(): void
    {
        $output = $this->createMock(OutputInterface::class);

        if (\interface_exists(StoreInterface::class)) {
            $mockStore = $this->createMock(StoreInterface::class);
        } else {
            $mockStore = $this->createMock(BlockingStoreInterface::class);
        }

        $mockStore
            ->expects($this->once())
            ->method('delete')
        ;
        $schedule = new Schedule();
        $event = $schedule->run('wrong-command');
        $event->preventOverlapping($mockStore);

        $eventRunner = $this->createEventRunner(true);
        $eventRunner->handle($output, [$schedule]);
    }

    /**
     * @param string $url
     *
     * @return EventRunner
     */
    private function createEventRunnerForPing($url)
    {
        $invoker = $this->createMock(Invoker::class);
        $mailer = $this->createMock(Mailer::class);
        $loggerFactory = $this->createMock(LoggerFactory::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $consoleLogger = $this->createMock(ConsoleLoggerInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('ping')
            ->with($url)
        ;

        return new EventRunner(
            $invoker,
            new FakeConfiguration(),
            $mailer,
            $loggerFactory,
            $httpClient,
            $consoleLogger
        );
    }

    private function createEventRunner(bool $realInvoker = false): EventRunner
    {
        $invoker = true === $realInvoker
            ? new Invoker()
            : $this->createMock(Invoker::class)
        ;
        $mailer = $this->createMock(Mailer::class);
        $loggerFactory = $this->createMock(LoggerFactory::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $consoleLogger = $this->createMock(ConsoleLoggerInterface::class);

        return new EventRunner(
            $invoker,
            new FakeConfiguration(),
            $mailer,
            $loggerFactory,
            $httpClient,
            $consoleLogger
        );
    }
}
