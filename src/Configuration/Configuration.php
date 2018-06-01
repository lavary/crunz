<?php

namespace Crunz\Configuration;

use Crunz\Exception\ConfigFileNotFoundException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Configuration
{
    /** @var array */
    private $config;
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(
        ConfigurationInterface $configurationDefinition,
        Processor $definitionProcessor,
        FileParser $fileParser,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->config = $definitionProcessor->processConfiguration(
            $configurationDefinition,
            $fileParser->parse($this->configFilePath())
        );
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

    /**
     * @return string
     *
     * @throws ConfigFileNotFoundException
     */
    private function configFilePath()
    {
        $pathsParts = [
            [
                getbase(),
                'crunz.yml',
            ],
            [
                \getcwd(),
                'crunz.yml',
            ],
            [
                CRUNZ_ROOT,
                'crunz.yml',
            ],
        ];

        $paths = \array_map(
            function (array $parts) {
                return \implode(DIRECTORY_SEPARATOR, $parts);
            },
            $pathsParts
        );

        foreach ($paths as $configPath) {
            if (\file_exists($configPath)) {
                return $configPath;
            }
        }

        throw new ConfigFileNotFoundException(
            \sprintf(
                'Unable to find any config file. Tested paths: %s',
                \implode(' ', $paths)
            )
        );
    }
}
