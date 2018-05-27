<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Crunz\Exception\CrunzException;

class ConfigFileNotExistsExtension extends CrunzException
{
    /**
     * @param string $filePath
     *
     * @return ConfigFileNotExistsExtension
     */
    public static function fromFilePath($filePath)
    {
        return new self("Configuration file '{$filePath}' not exists.");
    }
}
