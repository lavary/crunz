<?php

namespace Crunz;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Crunz\ConfigurationDefinition;

class Configuration {

    /**
     * Store parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Store parameters
     *
     * @var $this
     */
    protected static $instance;

     
    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct($filename = null)
    {      
        $this->parameters = $this->process($filename);
    }

    /**
     * Make an instance of Configuration class
     *
     * @return self
     */
    public static function getInstance()
    {
        $filename = static::locateConfigFile();

        if (!file_exists($filename)) {
            return $this->parameters;
        }
        
        if (null === static::$instance) {
            static::$instance = new static($filename);
        }

        return static::$instance;
    }

    /**
     * Locate the right config file and return the name
     *
     * @return string
     */
    protected static function locateConfigFile()
    {    
        return file_exists('crunz.yml') ? 'crunz.yml' : __DIR__ . '/../crunz.yml';
    }

    /**
     * Set a parameter
     *     
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
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
     * Return a parameter based on a key
     *
     * @param  string $key
     * @return string
     */
    public function get($key, $default = null)
    {       
        if (is_null($key)) {
            return $this->parameters;
        }

        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }

        $array = $this->parameters;

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $array;
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

    /**
     * Check if a parameter exist
     *
     * @param  string $key
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
     * Handle the configuration settings
     *
     * @return array
     */
    protected function process($filename)
    {    
        $proc = new Processor();
        try {
            return $proc->processConfiguration(
                        new ConfigurationDefinition(),
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
     * Private clone method to prevent cloning of the instance of the
     * Singleton instance.
     *
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    { 
       
    }

}