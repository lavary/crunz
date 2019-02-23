<?php

declare(strict_types=1);

namespace Crunz\Tests\Functional;

use Crunz\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ScheduleRunTest extends TestCase
{
    /** @test */
    public function showList(): void
    {
        $application = new Application('Crunz', '0.1.0-test.1');
        $command = $application->get('schedule:run');

        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute([]);

        $this->assertSame(0, $returnCode);
        $this->assertContains(PHP_VERSION, $commandTester->getDisplay());
    }
}
