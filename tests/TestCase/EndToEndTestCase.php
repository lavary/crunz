<?php

namespace Crunz\Tests\TestCase;

use Crunz\EnvFlags\EnvFlags;
use Crunz\Filesystem\Filesystem;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Tests\TestCase\EndToEnd\Environment\EnvironmentBuilder;
use PHPUnit\Framework\TestCase;

abstract class EndToEndTestCase extends TestCase
{
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var EnvFlags */
    private $envFlags;

    /** @return EnvironmentBuilder */
    public function createEnvironmentBuilder()
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        if (null === $this->envFlags) {
            $this->envFlags = new EnvFlags();
        }

        return new EnvironmentBuilder($this->filesystem, $this->envFlags);
    }
}
