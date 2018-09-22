<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\Configuration\Configuration;
use Crunz\EventRunner;
use Crunz\HttpClient\HttpClientInterface;
use Crunz\Invoker;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Logger\LoggerFactory;
use Crunz\Mailer;
use Crunz\Schedule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class EventRunnerTest extends TestCase
{
    /** @test */
    public function urlIsPingedBefore()
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
    public function urlIsPingedAfter()
    {
        $url = 'https://ping-aft.er/';
        $output = $this->createMock(OutputInterface::class);

        $eventRunner = $this->createEventRunnerForPing($url);

        $schedule = new Schedule();
        $event = $schedule->run('php -v');
        $event->thenPing($url);

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
        $configuration = $this->createMock(Configuration::class);
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
            $configuration,
            $mailer,
            $loggerFactory,
            $httpClient,
            $consoleLogger
        );
    }
}
