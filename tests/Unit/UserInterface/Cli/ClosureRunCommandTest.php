<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\UserInterface\Cli;

use Crunz\Tests\TestCase\UnitTestCase;
use Crunz\UserInterface\Cli\ClosureRunCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class ClosureRunCommandTest extends UnitTestCase
{
    /** @dataProvider closureValueProvider */
    public function testReturnValueOfClosureIsOmitted(int $returnValue): void
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
    public function commandIsHidden(): void
    {
        $command = $this->createCommand();

        $this->assertTrue($command->isHidden());
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
        return new ClosureRunCommand($this->createClosureSerializer());
    }
}
