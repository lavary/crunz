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

        $this->assertContains(
            'Closure output Var: 153',
            \str_replace(
                PHP_EOL,
                ' ',
                $process->getOutput()
            )
        );
    }
}
