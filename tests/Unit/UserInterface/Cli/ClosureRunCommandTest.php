<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\UserInterface\Cli;

use Crunz\Configuration\Configuration;
use Crunz\Configuration\ConfigurationParserInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Tests\TestCase\UnitTestCase;
use Crunz\UserInterface\Cli\ClosureRunCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class ClosureRunCommandTest extends UnitTestCase
{
    private function createConfiguration()
    {
        $cwd = \sys_get_temp_dir();
        $mockConfigurationParser = $this->createMock(ConfigurationParserInterface::class);
        $mockConfigurationParser
            ->method('parseConfig')
            ->willReturn(['bootstrap' => __DIR__ . '/bootstrap.php'])
        ;

        $mockFilesystem = $this->createMock(FilesystemInterface::class);
        $mockFilesystem
            ->method('getCwd')
            ->willReturn($cwd)
        ;

        return new Configuration($mockConfigurationParser, $mockFilesystem);
    }

    /** @dataProvider closureValueProvider */
    public function test_return_value_of_closure_is_omitted(int $returnValue): void
    {
        $closure = static function () use ($returnValue): int {
            return $returnValue;
        };
        $command = $this->createCommand();
        $input = $this->createInput($closure);
        $output = new NullOutput();

        $this->assertSame(
            0,
            $command->run($input, $output)
        );
    }

    /** @test */
    public function command_is_hidden(): void
    {
        $command = $this->createCommand();

        $this->assertTrue($command->isHidden());
    }

    /** @test */
    public function bootstrap_loaded(): void
    {
        $command = $this->createCommand();
        $input = $this->createInput(function() {});
        $output = new NullOutput();
        $command->run($input, $output);
        $this->assertTrue(defined('BOOTSTRAP_LOADED'));
    }

    /** @return iterable<string,array<int>> */
    public function closureValueProvider(): iterable
    {
        yield '0' => [0];
        yield '1' => [1];
    }

    private function createInput(\Closure $closure): ArrayInput
    {
        $closureSerializer = $this->createClosureSerializer();

        return new ArrayInput(
            [
                'closure' => \http_build_query(
                    [
                        $closureSerializer->serialize($closure),
                    ]
                ),
            ]
        );
    }

    private function createCommand(): ClosureRunCommand
    {
        return new ClosureRunCommand($this->createClosureSerializer(), $this->createConfiguration());
    }
}
