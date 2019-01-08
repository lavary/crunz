<?php

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class DeprecationMessagesTest extends EndToEndTestCase
{
    /** @test */
    public function earlyDeprecationShouldBeVisible()
    {
        $path = Path::create(
            [
                'bin',
                'deprecation-application',
                'crunz',
            ]
        );
        $command = PHP_BINARY . " {$path->toString()}";

        $process = $this->createProcess($command);
        $process->start();
        $process->wait();

        $this->assertSame(0, $process->getExitCode());
        $this->assertContains('[Deprecation] Test deprecation', $process->getOutput());
    }
}
