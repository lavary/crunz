<?php

namespace Crunz\Tests\TestCase;

use Crunz\Filesystem\Filesystem;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Tests\TestCase\EndToEnd\Environment\EnvironmentBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

abstract class EndToEndTestCase extends TestCase
{
    /** @var FilesystemInterface */
    private $filesystem;

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

    /** @return EnvironmentBuilder */
    public function createEnvironmentBuilder()
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return new EnvironmentBuilder($this->filesystem);
    }
}
