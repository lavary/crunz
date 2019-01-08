<?php

namespace Crunz\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

abstract class EndToEndTestCase extends TestCase
{
    /**
     * @param string $command
     *
     * @return Process
     */
    public function createProcess($command)
    {
        if (\method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command);
        } else {
            $process = new Process($command);
        }

        return $process;
    }
}
