<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Path;

use Crunz\Exception\CrunzException;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    /** @test */
    public function createRequiresAtLeastOnePath()
    {
        $this->expectException(CrunzException::class);
        $this->expectExceptionMessage('At least one part expected.');

        Path::create([]);
    }

    /** @test */
    public function partsAreDelimitedByDirectorySeparator()
    {
        $parts = [
            'home',
            'crunz',
            'bin',
        ];

        $path = Path::create($parts);

        $this->assertSame(
            \implode(DIRECTORY_SEPARATOR, $parts),
            $path->toString()
        );
    }
}
