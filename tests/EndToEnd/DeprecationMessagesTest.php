<?php

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class DeprecationMessagesTest extends TestCase
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

        if (\method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command);
        } else {
            $process = new Process($path->toString());
        }

        $process->start();
        $process->wait();

        $this->assertSame(0, $process->getExitCode());
        $this->assertContains('[Deprecation] Test deprecation', $process->getOutput());
    }
}
