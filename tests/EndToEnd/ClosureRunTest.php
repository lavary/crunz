<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Tests\TestCase\EndToEndTestCase;

final class ClosureRunTest extends EndToEndTestCase
{
    /** @test */
    public function closureTasks(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder
            ->addTask('ClosureTasks')
            ->withConfig(['timezone' => 'UTC'])
        ;

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('schedule:run');

        $this->assertStringContainsString(
            'Closure output Var: 153',
            \str_replace(
                PHP_EOL,
                ' ',
                $process->getOutput()
            )
        );
    }

    public function test_prevent_overlapping_works_on_closures(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder
            ->addTask('NoOverlappingClosureTasks')
            ->withConfig(['timezone' => 'UTC'])
        ;

        $environment = $envBuilder->createEnvironment();

        // Warmup Crunz to avoid container's cache race condition
        $environment->runCrunzCommand('schedule:list');

        $firstCall = $environment->runCrunzCommand(
            'schedule:run',
            null,
            false
        );
        \usleep(50 * 1000); // wait 50ms
        $secondCall = $environment->runCrunzCommand('schedule:run');
        $firstCall->wait();

        $this->assertStringContainsString('Done', $firstCall->getOutput());
        $this->assertStringContainsString('No event is due!', $secondCall->getOutput());
    }
}
