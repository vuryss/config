<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Vuryss\Config;

use Psr\SimpleCache\CacheInterface;

/**
 * Class Config
 *
 * Manages file-based configuration with PSR-16 caching capabilities.
 */
class Config implements ConfigInterface
{
    const CACHE_KEY = 'vuryss:config';

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
     * Whether to check if the config files have been modified since last time the cache has been populated.
     * If the files have been modified, they will be read and parsed again to get the changes. Cache will be updated.
     *
     * @var bool
     */
    private $checkFilesForUpdates = true;

    /**
     * Last time any of the configuration files were updated.
     * This will be used when checking for config changes during development.
     *
     * @see $this->checkFilesForUpdates
     *
     * @var int
     */
    private $lastConfigFileUpdateTime = 0;

    /**
     * @var string
     */
    private $filesChecksum = '';

    /**
     * Config constructor.
     *
     * @throws Exception
     *
     * @param string|string[]     $files                File or array of configuration files to use.
     * @param CacheInterface|null $cache                PSR-16 compliant cache adapter to use.
     * @param bool                $checkFilesForUpdates Check config files for updates before serving cached values.
     */
    public function __construct($files, CacheInterface $cache = null, bool $checkFilesForUpdates = true)
    {
        if (!is_array($files) && !is_string($files)) {
            throw new Exception('You should provide a configuration file or array of configuration files.');
        }

        foreach ((array) $files as $file) {
            $this->appendConfigurationFile($file);
        }

        $this->filesChecksum = md5(implode(',', $this->files));

        if ($cache) {
            $this->cache = $cache;
        }

        $this->checkFilesForUpdates = $checkFilesForUpdates;

        $this->initialize();
    }

    /**
     * Returns whether the specified key exists in the currently loaded configuration or not.
     *
     * @param string $key Cache key under which the data is stored in the config.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        if (strpos($key, '.') === false) {
            return isset($this->config[$key]);
        }

        $key  = explode('.', $key);
        $data = $this->config;

        foreach ($key as $subKey) {
            if (empty($subKey) || !isset($data[$subKey])) {
                return false;
            }

            $data = $data[$subKey];
        }

        return true;
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
        if (strpos($key, '.') === false) {
            return isset($this->config[$key]) ? $this->config[$key] : $default;
        }

        $key  = explode('.', $key);
        $data = $this->config;

        foreach ($key as $subKey) {
            if (empty($subKey) || !isset($data[$subKey])) {
                return $default;
            }

            $data = $data[$subKey];
        }

        return $data;
    }

    /**
     * Sets or overwrites the current configuration value behind a given key.
     * Generally we should not be using this unless it's highly necessary.
     * All configuration should be set in the configuration files and not changed at runtime.
     *
     * NOTE: The change will not be stored in the configuration files! It will be only for the current script execution.
     *
     * @param string $key   Cache key under which the data is stored in the config.
     * @param mixed  $value Value to be set under the given configuration key.
     *
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        if (strpos($key, '.') === false) {
            $this->config[$key] = $value;
            return true;
        }

        return $this->setNested($key, $value);
    }

    /**
     * Sets nested value.
     *
     * @param string $key   Cache key under which the data is stored in the config.
     * @param mixed  $value Value to be set under the given configuration key.
     *
     * @return bool
     */
    private function setNested(string $key, $value)
    {
        $key  = explode('.', $key);
        $data = &$this->config;

        foreach ($key as $subKey) {
            if (empty($subKey)) {
                return false;
            }

            if (!isset($data[$subKey])) {
                $data[$subKey] = [];
            }

            $data = &$data[$subKey];
        }

        $data = $value;
        return true;
    }

    /**
     * @throws Exception
     *
     * @param string $file File which will be parsed and it's configuration contents merged to existing configuration.
     *
     * @return void
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

        if ($this->checkFilesForUpdates) {
            $this->updateLastConfigUpdateTime($file);
        }
    }

    /**
     * Updates the last config update time, which will be used to see if there are any changes to the files.
     *
     * @param string $file File to check for last updates.
     *
     * @return void
     */
    private function updateLastConfigUpdateTime(string $file)
    {
        $mtime = filemtime($file);

        if ($mtime > $this->lastConfigFileUpdateTime) {
            $this->lastConfigFileUpdateTime = $mtime;
        }
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @return void
     */
    private function initialize()
    {
        if ($this->cache) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $cachedConfig = $this->cache->get(self::CACHE_KEY . ':cache-config');

            if (!$cachedConfig) {
                $this->parseAndCacheConfigurationFiles();
                return;
            }

            if ($this->checkFilesForUpdates) {
                /** @noinspection PhpUnhandledExceptionInspection */
                [self::CACHE_KEY . ':cache-time' => $cachedTime, self::CACHE_KEY . ':cache-files' => $cachedFiles]
                    = $this->cache->getMultiple(
                        [self::CACHE_KEY . ':cache-time', self::CACHE_KEY . ':cache-files'],
                        null
                    );

                if (!$cachedTime || !$cachedFiles) {
                    $this->parseAndCacheConfigurationFiles();
                    return;
                }

                if ($this->lastConfigFileUpdateTime > $cachedTime || $this->filesChecksum !== $cachedFiles) {
                    $this->parseAndCacheConfigurationFiles();
                    return;
                }
            }

            $this->config = $cachedConfig;
            return;
        }

        $this->parseAndCacheConfigurationFiles();
    }

    /**
     * Parses the configuration files, filling up the internal config.
     * Then caches the data, if the cache is given.
     *
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @return void
     */
    private function parseAndCacheConfigurationFiles()
    {
        foreach ($this->files as $file) {
            $this->config = array_merge($this->config, yaml_parse_file($file));
        }

        if ($this->cache) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->cache->setMultiple(
                [
                    self::CACHE_KEY . ':cache-time'   => time(),
                    self::CACHE_KEY . ':cache-files'  => $this->filesChecksum,
                    self::CACHE_KEY . ':cache-config' => $this->config,
                ]
            );
        }
    }
}
