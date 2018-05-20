<?php

namespace Crunz\Configuration;

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

    private function configFilePath()
    {
        $config_file = CRUNZ_ROOT . '/crunz.yml';

        return \file_exists($config_file) ? $config_file : __DIR__ . '/../../crunz.yml';
    }
}
