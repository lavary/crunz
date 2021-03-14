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
    public function every_task_must_return_crunz_schedule_instance(string $crunzCommand): void
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
        $this->assertMatchesRegularExpression(
            "@Task at path '.*WrongTasks\\.php' returned 'array', but 'C( ?)runz\\\\Schedule' instance is required\.@",
            $normalizedOutput
        );
    }

    /** @return iterable<string,array> */
    public function scheduleInstanceProvider(): iterable
    {
        yield 'list' => ['schedule:list'];
        yield 'run' => ['schedule:run'];
    }
}
