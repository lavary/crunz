<?php

namespace Crunz\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand {
    
    /**
     * Command arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Command options
     *
     * @var array
     */
    protected $options;
    
    /**
     * Store configuration settings
     *
     * @var \Crunz\Configuration
     */
    protected $config;

    /**
     * Set the configuration object
     *
     * @param \Crunz\ConfigurationInterface
     * @return $this
     */
    protected function setConfiguration(\Crunz\Configuration $configuration)
    {
        $this->config = $configuration;

        return $this;
    }

    /**
     * Fetch value from the configuration object
     *
     * @param  string $key
     * @return string
     */
    protected function config($key)
    {
        if ($this->config instanceof \Crunz\Configuration) {
            return $this->config->get($key);
        }
    }

}