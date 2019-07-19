<?php

declare(strict_types=1);

namespace Vuryss\Config;

/**
 * Interface ConfigInterface
 *
 * @package Vuryss\Config
 */
interface ConfigInterface
{
    /**
     * Returns whether the specified key exists in the currently loaded configuration or not.
     *
     * @param string $key Cache key under which the data is stored in the config.
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Retrieves
     *
     * @param string     $key     Cache key under which the data is stored in the config.
     * @param mixed|null $default Default value to be returned in case the value is not found in the stored config.
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

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
    public function set(string $key, $value): bool;
}
