<?php

namespace Crunz\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Crunz\Singleton;

class Configuration extends Singleton {

    /**
     * Store parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The instance of the configuration class
     *
     * @var $this
     */
    protected static $instance;

    /**
     * Process the configuration file into an array
     *
     */
    protected function __construct()
    {
        $this->parameters = $this->process($this->locateConfigFile());      
    }

    /**
     * Handle the configuration settings
     *
     * @return array
     */
    protected function process($filename)
    {    
        $proc = new Processor();       
        try {
            return $proc->processConfiguration(
                        new Definition(),
                        $this->parse($filename)
                    );
        } catch (InvalidConfigurationException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Load configuration files and parse them
     *
     * @return array
     */
    protected function parse($filename)
    {    
        $conf = [];

        $conf[] = Yaml::parse(
            file_get_contents($filename)
        );

        return $conf;
    }

    /**
     * Locate the right config file and return its name
     *
     * @return string
     */
    protected function locateConfigFile()
    {    
        $config_file = getenv('CRUNZ_BASE_DIR') . '/crunz.yml';
        
        return file_exists($config_file) ? $config_file : __DIR__ . '/../../crunz.yml';
    }

    /**
     * Set a parameter
     *     
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return array
     */
    public function set($key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Check if a parameter exist
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        if (! $array) {
            return false;
        }

        if (is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $this->parameters)) {
            return true;
        }

        $array = $this->parameters;
        
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Return a parameter based on a key
     *
     * @param  string $key
     *
     * @return string
     */
    public function get($key, $default = null)
    {       
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }
        
        $array = $this->parameters;

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Return all the parameters as an array
     *
     * @return array
     */
    public function all()
    {
       return $this->parameters;
    }
}