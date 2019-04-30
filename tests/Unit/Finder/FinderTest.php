<?php

namespace Crunz\Tests\Unit\Finder;

use Crunz\Filesystem\Filesystem;
use Crunz\Finder\Finder;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class FinderTest extends TestCase
{
    /** @test */
    public function findReturnsSplFileInfoCollection()
    {
        $filesystem = new Filesystem();
        $tasksDirectory = Path::fromStrings(
            $filesystem->tempDir(),
            '.crunz',
            'finder-test'
        );

        $taskOne = Path::fromStrings($tasksDirectory->toString(), 'TestHere.php');
        $taskTwo = Path::fromStrings(
            $tasksDirectory->toString(),
            'first-level',
            'OtherTestHere.php'
        );
        $taskThree = Path::fromStrings(
            $tasksDirectory->toString(),
            'first-level',
            'second-level',
            'TestHere.php'
        );

        $filesystem->dumpFile($taskOne->toString(), 'Some content here');
        $filesystem->dumpFile($taskTwo->toString(), 'Some content here');
        $filesystem->dumpFile($taskThree->toString(), 'Some content here');

        $finder = new Finder();
        $files = $finder->find($tasksDirectory, 'Here.php');

        $this->assertCount(3, $files);
        $this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $files);

        $filesystem->removeDirectory($tasksDirectory->toString());
    }
}
