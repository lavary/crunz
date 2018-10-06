<?php

namespace Crunz\Configuration;

use Crunz\Filesystem\FilesystemInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Configuration
{
    /** @var array */
    private $config;
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;
    /** @var ConfigurationParser */
    private $configurationParser;
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        ConfigurationParserInterface $configurationParser,
        FilesystemInterface $filesystem
    ) {
        $this->propertyAccessor = $propertyAccessor;
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

        $path = \implode(
            '',
            \array_map(
                function ($keyPart) {
                    return "[{$keyPart}]";
                },
                \explode('.', $key)
            )
        );

        try {
            return $this->propertyAccessor
                ->getValue(
                    $this->config,
                    $path
                )
            ;
        } catch (AccessException $exception) {
            return $default;
        }
    }

    /** @return string */
    public function getSourcePath()
    {
        return $this->makePath(
            [
                $this->filesystem
                    ->getCwd(),
                $this->get('source'),
            ]
        );
    }

    private function makePath(array $parts)
    {
        return \implode(DIRECTORY_SEPARATOR, $parts);
    }
}
