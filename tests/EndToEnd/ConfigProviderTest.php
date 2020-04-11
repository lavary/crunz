<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Console\Command\ConfigGeneratorCommand;
use Crunz\Filesystem\Filesystem;
use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;
use Symfony\Component\Yaml\Yaml;

final class ConfigProviderTest extends EndToEndTestCase
{
    public function test_config_can_be_published(): void
    {
        $environmentBuilder = $this->createEnvironmentBuilder();
        $environmentBuilder->withConfig(['timezone' => null]);
        $environment = $environmentBuilder->createEnvironment();
        $process = $environment->runCrunzCommand('publish:config');

        $configPath = Path::fromStrings($environment->rootDirectory(), ConfigGeneratorCommand::CONFIG_FILE_NAME);
        $filesystem = new Filesystem();

        $this->assertTrue($process->isSuccessful(), "Process output: {$process->getOutput()}{$process->errorOutput()}");
        $this->assertFileExists($configPath->toString());
        $this->assertIsArray(
            Yaml::parse(
                $filesystem->readContent(
                    $configPath->toString()
                )
            )
        );
    }
}
