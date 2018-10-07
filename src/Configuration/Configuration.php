<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;

class Configuration
{
    /** @var array */
    private $config;
    /** @var ConfigurationParser */
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
     * @param null   $default
     *
     * @return string
     */
    public function get($key, $default = null)
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

    /** @return string */
    public function getSourcePath()
    {
        $sourcePath = Path::create(
            [
                $this->filesystem
                    ->getCwd(),
                $this->get('source'),
            ]
        );

        return $sourcePath->toString();
    }
}
