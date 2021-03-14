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
    public function url_is_pinged_before(): void
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
    public function url_is_pinged_after(): void
    {
        $url = 'https://ping-aft.er/';
        $output = $this->createMock(OutputInterface::class);

        $eventRunner = $this->createEventRunnerForPing($url);

        $schedule = new Schedule();
        $event = $schedule->run('php -v');
        $event->thenPing($url);

        $eventRunner->handle($output, [$schedule]);
    }

    public function test_lock_is_released_on_error(): void
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
