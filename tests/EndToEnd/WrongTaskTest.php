<?php

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class WrongTaskTest extends EndToEndTestCase
{
    /**
     * @test
     * @dataProvider scheduleInstanceProvider
     * @TODO Check for exception in v2
     */
    public function everyTaskMustReturnCrunzScheduleInstance($crunzCommand)
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder->addTask('WrongTasks');

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand($crunzCommand);
        $filePath = Path::fromStrings(
            $environment->rootDirectory(),
            'tasks',
            'WrongTasks.php'
        );
        $normalizedOutput = $this->normalizeProcessOutput($process);

        $this->assertContains(
            "[Deprecation] File '{$filePath->toString()}' didn't return '\Crunz\Schedule' instance, this behavior is deprecated since v1.12 and will result in exception in v2.0+",
            $normalizedOutput
        );
    }

    public function scheduleInstanceProvider()
    {
        yield 'list' => ['schedule:list'];
        yield 'run' => ['schedule:run'];
    }
}
