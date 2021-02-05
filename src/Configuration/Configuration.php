<?php

declare(strict_types=1);

namespace Crunz\Configuration;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;

final class Configuration implements ConfigurationInterface
{
    /** @var array<string,mixed> */
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
     * @param mixed $default
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

    /**
     * Set a parameter based on key/value.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        if (null === $this->config) {
            $this->config = $this->configurationParser
                ->parseConfig();
        }

        $parts = \explode('.', $key);

        if (\count($parts) > 1) {
            if (\array_key_exists($parts[0], $this->config) && \is_array($this->config[$parts[0]])) {
                $this->config[$parts[0]][$parts[1]] = $value;
            } else {
                $this->config[$parts[0]] = [$parts[1] => $value];
            }
        } else {
            $this->config[$key] = $value;
        }
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
