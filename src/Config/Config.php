<?php

declare(strict_types=1);

namespace Vuryss\Config;

use Psr\SimpleCache\CacheInterface;

class Config implements ConfigInterface
{
    const FORMAT_YAML = 'yaml';

    /**
     * Format of the configuration file
     *
     * @var string
     */
    private $fileFormat = 'yaml';

    /**
     * Configuration files.
     *
     * @var string[]
     */
    private $files;

    /**
     * Caching adapter, should be PSR-16 compliant.
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * The merged, final configuration.
     *
     * @var array
     */
    private $config = [];

    /**
     * Config constructor.
     *
     * @throws Exception
     *
     * @param string|string[]     $files File or array of configuration files to use.
     * @param CacheInterface|null $cache PSR-16 compliant cache adapter to use.
     */
    public function __construct($files, CacheInterface $cache = null)
    {
        if (!is_array($files) && !is_string($files)) {
            throw new Exception('You should provide a configuration file or array of configuration files.');
        }

        foreach ((array) $files as $file) {
            $this->appendConfigurationFile($file);
        }

        if ($cache) {
            $this->cache = $cache;
        }

        $this->initialize();
    }

    /**
     * Returns whether the specified key exists in the currently loaded configuration or not.
     *
     * @param string $key Cache key under which the data is stored in the config.
     *
     * @return boolean
     */
    public function has(string $key): bool
    {
        // TODO: Implement has() method.
    }

    /**
     * Retrieves
     *
     * @param string     $key     Cache key under which the data is stored in the config.
     * @param mixed|null $default Default value to be returned in case the value is not found in the stored config.
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // TODO: Implement get() method.
    }

    /**
     * Sets or overwrites the current configuration value behind a given key.
     * Generally we should not be using this unless it's highly necessary.
     * All configuration should be set in the configuration files and not changed at runtime.
     *
     * @param string $key   Cache key under which the data is stored in the config.
     * @param mixed  $value Value to be set under the given configuration key.
     *
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        // TODO: Implement set() method.
    }

    /**
     * @throws Exception
     *
     * @param string $file
     */
    private function appendConfigurationFile($file)
    {
        if (!is_string($file)) {
            throw new Exception('Invalid configuration file path!');
        }

        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception('Configuration file not found or not readable: ' . $file);
        }

        $this->files[] = $file;
    }

    private function initialize()
    {
        foreach ($this->files as $file) {
            $this->config = array_merge($this->config, yaml_parse_file($file));
        }
    }
}
