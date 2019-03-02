<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Task;

use Crunz\Configuration\Configuration;
use Crunz\Finder\FinderInterface;
use Crunz\Task\Collection;
use Crunz\Tests\TestCase\Logger\NullLogger;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
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
