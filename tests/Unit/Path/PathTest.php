<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Path;

use Crunz\Exception\CrunzException;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    /** @test */
    public function create_requires_at_least_one_path(): void
    {
        $this->expectException(CrunzException::class);
        $this->expectExceptionMessage('At least one part expected.');

        Path::create([]);
    }

    /** @test */
    public function parts_are_delimited_by_directory_separator(): void
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

    /** @test */
    public function path_can_be_created_from_strings(): void
    {
        $parts = [
            'home',
            'user',
            'vendor',
            'bin',
            'crunz',
        ];
        $path = Path::fromStrings(...$parts);

        $this->assertSame(
            \implode(DIRECTORY_SEPARATOR, $parts),
            $path->toString()
        );
    }

    /** @test */
    public function doubled_directory_separator_is_normalized(): void
    {
        $parts = [
            'home' . DIRECTORY_SEPARATOR,
            'user',
        ];

        $path = Path::create($parts);

        $this->assertSame(
            'home' . DIRECTORY_SEPARATOR . 'user',
            $path->toString()
        );
    }
}
