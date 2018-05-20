<?php

namespace Crunz\Configuration;

use Crunz\Exception\CrunzException;

class ConfigFileNotReadableException extends CrunzException
{
    /**
     * @param string $filePath
     *
     * @return ConfigFileNotReadableException
     */
    public static function fromFilePath($filePath)
    {
        return new self("Config file '{$filePath}' is not readable.");
    }
}
