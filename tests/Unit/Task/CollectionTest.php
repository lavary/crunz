<?php

namespace Crunz\Tests\Unit\Task;

use Crunz\Configuration\Configuration;
use Crunz\Finder\FinderInterface;
use Crunz\Task\Collection;
use Crunz\Tests\TestCase\Logger\NullLogger;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    /**
     * @test
     * @TODO Remove in v2
     * @group legacy
     * @@expectedDeprecation Probably you are relying on legacy tasks source recognition which is deprecated. Currently default source for tasks is relative to current working directory, make sure you changed it accordingly in your Cron task. Run your command with '-vvv' to check resolved paths. Legacy behavior will be removed in v2.
     */
    public function allLegacyPathsTriggerDeprecationWhenTasksFound()
    {
        $collection = $this->createCollection([\sys_get_temp_dir()], ['dsadsa']);
        $collection->allLegacyPaths();
    }

    /** @return Collection */
    private function createCollection(array $legacyPaths = [], array $tasks = [])
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration
            ->method('binRelativeSourcePaths')
            ->willReturn($legacyPaths)
        ;

        $finder = $this->createMock(FinderInterface::class);
        $finder
            ->method('find')
            ->willReturn($tasks)
        ;

        return new Collection(
            $configuration,
            $finder,
            new NullLogger()
        );
    }
}
