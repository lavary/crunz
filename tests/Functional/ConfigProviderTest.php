<?php

declare(strict_types=1);

namespace Crunz\Tests\Functional;

use Crunz\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigProviderTest extends TestCase
{
    /** @test */
    public function configAlreadyExists(): void
    {
        $application = new Application('Crunz', '0.1.0-test.1');
        $command = $application->get('publish:config');

        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute([]);

        $this->assertSame(0, $returnCode);
        $this->assertContains('The configuration file already exists at', $commandTester->getDisplay());
    }
}
