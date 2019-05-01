<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Finder;

use Crunz\Filesystem\Filesystem;
use Crunz\Finder\Finder;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class FinderTest extends TestCase
{
    /** @var Filesystem */
    private $filesystem;
    /** @var Path */
    private $tasksDirectory;

    public function setUp(): void
    {
        $filesystem = new Filesystem();
        $this->filesystem = $filesystem;
        $this->tasksDirectory = Path::fromStrings(
            $filesystem->tempDir(),
            '.crunz',
            'finder-test'
        );
    }

    public function tearDown(): void
    {
        $tasksDirectory = $this->tasksDirectory;
        $this->filesystem
            ->removeDirectory($tasksDirectory->toString());
    }

    /**
     * @test
     * @dataProvider tasksProvider
     */
    public function findReturnsSplFileInfoCollection(string $suffix, Path ...$files): void
    {
        $this->createFiles(...$files);
        $tasksDirectory = $this->tasksDirectory;

        $finder = new Finder();
        $foundFiles = $finder->find($tasksDirectory, $suffix);

        $this->assertCount(\count($files), $foundFiles);
        $this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foundFiles);
    }

    public function tasksProvider(): iterable
    {
        $suffix = 'Here.php';
        $taskOne = Path::fromStrings('TestHere.php');
        $taskTwo = Path::fromStrings('first-level', 'OtherTestHere.php');
        $taskThree = Path::fromStrings(
            'first-level',
            'second-level',
            'TestHere.php'
        );

        yield 'flat' => [$suffix, $taskOne];
        yield 'firstLevel' => [
            $suffix,
            $taskOne,
            $taskTwo,
        ];
        yield 'secondLevel' => [
            $suffix,
            $taskOne,
            $taskTwo,
            $taskThree,
        ];
    }

    private function createFiles(Path ...$files): void
    {
        $tasksDirectory = $this->tasksDirectory;

        foreach ($files as $file) {
            $path = Path::fromStrings($tasksDirectory->toString(), $file->toString());
            $content = \bin2hex(\random_bytes(8));
            $this->filesystem
                ->dumpFile($path->toString(), $content);
        }
    }
}
