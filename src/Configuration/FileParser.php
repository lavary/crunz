<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Symfony\Component\Yaml\Yaml;

class FileParser
{
    /** @var Yaml */
    private $yamlParser;

    public function __construct(Yaml $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    /**
     * @throws ConfigFileNotExistsException
     * @throws ConfigFileNotReadableException
     */
    public function parse(string $configPath): array
    {
        if (!\file_exists($configPath)) {
            throw ConfigFileNotExistsException::fromFilePath($configPath);
        }

        if (!\is_readable($configPath)) {
            throw ConfigFileNotReadableException::fromFilePath($configPath);
        }

        $yamlParser = $this->yamlParser;

        return [$yamlParser::parse(\file_get_contents($configPath))];
    }
}
