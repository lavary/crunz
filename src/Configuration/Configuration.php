<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;

class Configuration
{
    /** @var array */
    private $config;
    /** @var ConfigurationParserInterface */
    private $configurationParser;
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(ConfigurationParserInterface $configurationParser, FilesystemInterface $filesystem)
    {
        $this->configurationParser = $configurationParser;
        $this->filesystem = $filesystem;
    }

    /**
     * Return a parameter based on a key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (null === $this->config) {
            $this->config = $this->configurationParser
                ->parseConfig();
        }

        if (\array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        $parts = \explode('.', $key);

        $value = $this->config;
        foreach ($parts as $part) {
            if (!\is_array($value) || !\array_key_exists($part, $value)) {
                return $default;
            }

            $value = $value[$part];
        }

        return $value;
    }

    public function getSourcePath(): string
    {
        $sourcePath = Path::create(
            [
                $this->filesystem
                    ->getCwd(),
                $this->get('source', 'tasks'),
            ]
        );

        return $sourcePath->toString();
    }
}
