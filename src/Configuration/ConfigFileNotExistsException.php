<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Crunz\Exception\CrunzException;

final class ConfigFileNotExistsException extends CrunzException
{
    public static function fromFilePath(string $filePath): self
    {
        return new self("Configuration file '{$filePath}' not exists.");
    }
}
