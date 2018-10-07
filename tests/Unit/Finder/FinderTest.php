<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Finder;

use Crunz\Finder\Finder;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class FinderTest extends TestCase
{
    /** @test */
    public function findReturnsSplFileInfoCollection()
    {
        $path = Path::create([__DIR__, '*.php']);
        $globIterator = new \GlobIterator(
            $path->toString(),
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
        );

        $finder = new Finder();
        $files = $finder->find($path);

        $this->assertCount($globIterator->count(), $files);
        $this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $files);
    }
}
