<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Tests\TestCase\EndToEndTestCase;

final class WrongTaskTest extends EndToEndTestCase
{
    /**
     * @test
     * @dataProvider scheduleInstanceProvider
     */
    public function everyTaskMustReturnCrunzScheduleInstance(string $crunzCommand): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder
            ->addTask('WrongTasks')
            ->withConfig(['timezone' => 'Europe/Warsaw'])
        ;

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand($crunzCommand);
        $normalizedOutput = $this->normalizeProcessErrorOutput($process);

        $this->assertFalse($process->isSuccessful());
        $this->assertRegExp(
            "@Task at path '.*WrongTasks\\.php' returned 'array', but 'C( ?)runz\\\\Schedule' instance is required\.@",
            $normalizedOutput
        );
    }

    public function scheduleInstanceProvider(): iterable
    {
        yield 'list' => ['schedule:list'];
        yield 'run' => ['schedule:run'];
    }
}
