<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\UserInterface\Cli;

use Crunz\UserInterface\Cli\ClosureRunCommand;
use PHPUnit\Framework\TestCase;
use SuperClosure\Serializer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class ClosureRunCommandTest extends TestCase
{
    /** @dataProvider closureValueProvider */
    public function testReturnValueOfClosureIsOmitted(int $returnValue): void
    {
        $closure = static function () use ($returnValue): int {
            return $returnValue;
        };
        $command = new ClosureRunCommand();
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
        $command = new ClosureRunCommand();

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
        $closureSerializer = new Serializer();

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
}
