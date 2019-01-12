<?php

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
                $this->get('source', 'tasks'),
            ]
        );

        return $sourcePath->toString();
    }

    /**
     * @deprecated Since v1.11
     *
     * @todo Remove in v2
     *
     * @return string[]
     */
    public function binRelativeSourcePaths()
    {
        $vendorBin = Path::create(
            [
                '..',
                '..',
                $this->get('source', 'tasks'),
            ]
        );
        $vendorCrunzBin = Path::create(
            [
                '..',
                '..',
                '..',
                $this->get('source', 'tasks'),
            ]
        );
        $paths = [
            $vendorBin->toString(),
            $vendorCrunzBin->toString(),
        ];

        return \array_map(
            function ($relativePart) {
                return Path::create([CRUNZ_BIN_DIR, $relativePart])->toString();
            },
            $paths
        );
    }
}
