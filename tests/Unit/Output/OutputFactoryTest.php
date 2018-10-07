<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Output;

use Crunz\Output\OutputFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class OutputFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider inputProvider
     */
    public function inputDefinesOutputVerbosity(InputInterface $input, $expectedVerbosity)
    {
        $factory = new OutputFactory($input);

        $output = $factory->createOutput();

        $this->assertSame($expectedVerbosity, $output->getVerbosity());
    }

    public function inputProvider()
    {
        yield 'quietShort' => [
            $this->createInput('-q'),
            OutputInterface::VERBOSITY_QUIET,
        ];

        yield 'quietLong' => [
            $this->createInput('--quiet'),
            OutputInterface::VERBOSITY_QUIET,
        ];

        yield 'normal' => [
            $this->createInput('--filter'),
            OutputInterface::VERBOSITY_NORMAL,
        ];

        yield 'verbose' => [
            $this->createInput('-v'),
            OutputInterface::VERBOSITY_VERBOSE,
        ];

        yield 'veryVerbose' => [
            $this->createInput('-vv'),
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        ];

        yield 'debug' => [
            $this->createInput('-vvv'),
            OutputInterface::VERBOSITY_DEBUG,
        ];
    }

    private function createInput($option)
    {
        return new ArgvInput(['', $option]);
    }
}
