<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Console\Command;

use Crunz\Console\Command\ClosureRunCommand;
use PHPUnit\Framework\TestCase;
use SuperClosure\Serializer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class ClosureRunCommandTest extends TestCase
{
    public function testReturnValueOfClosureIsOmitted(): void
    {
        $closure = static function (): int {
            return 0;
        };
        $command = new ClosureRunCommand();
        $input = $this->createInput($closure);
        $output = new NullOutput();

        $this->assertSame(
            0,
            $command->run($input, $output)
        );
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
