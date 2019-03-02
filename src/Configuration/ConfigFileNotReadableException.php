<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Crunz\Exception\CrunzException;

final class ConfigFileNotReadableException extends CrunzException
{
    public static function fromFilePath(string $filePath): self
    {
        return new self("Config file '{$filePath}' is not readable.");
    }
}
