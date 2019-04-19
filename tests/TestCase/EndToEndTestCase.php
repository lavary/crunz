<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase;

use Crunz\EnvFlags\EnvFlags;
use Crunz\Filesystem\Filesystem;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Process\Process;
use Crunz\Tests\TestCase\EndToEnd\Environment\EnvironmentBuilder;
use PHPUnit\Framework\TestCase;

abstract class EndToEndTestCase extends TestCase
{
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var EnvFlags */
    private $envFlags;

    public function createEnvironmentBuilder(): EnvironmentBuilder
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        if (null === $this->envFlags) {
            $this->envFlags = new EnvFlags();
        }

        return new EnvironmentBuilder($this->filesystem, $this->envFlags);
    }

    protected function normalizeOutput(string $output): string
    {
        $noNewLines = \str_replace(
            ["\n", "\r"],
            '',
            $output
        );
        $normalizedOutput = \preg_replace(
            "/\s+/",
            ' ',
            (string) $noNewLines
        );

        return \trim((string) $normalizedOutput);
    }

    protected function normalizeProcessOutput(Process $process): string
    {
        return $this->normalizeOutput($process->getOutput());
    }

    protected function normalizeProcessErrorOutput(Process $process): string
    {
        return $this->normalizeOutput($process->errorOutput());
    }
}
