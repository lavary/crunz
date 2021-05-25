<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase;

use PHPUnit\Framework\Constraint\DirectoryExists;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\RegularExpression;

trait PolyfillAssertTrait
{
    public static function assertMatchesRegularExpression(
        string $pattern,
        string $string,
        string $message = ''
    ): void {
        static::assertThat($string, new RegularExpression($pattern), $message);
    }

    public static function assertDirectoryDoesNotExist(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new LogicalNot(new DirectoryExists()), $message);
    }

    public static function assertFileDoesNotExist(string $file, string $message = ''): void
    {
        static::assertThat($file, new LogicalNot(new FileExists()), $message);
    }
}
