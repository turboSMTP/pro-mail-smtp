<?php

namespace FreeMailSMTP\Core;

use FreeMailSMTP\Helpers\PluginSourceCache;

class WPMailCaller
{
    private $cache;  // Keeps plugin name cache

    const UNKNOWN_SOURCE = 'Unknown Source';
    const MUST_USE_PLUGIN = 'Must Use Plugin';
    const CORE_WP = 'Core WP';

    public function __construct() {
        $this->cache = PluginSourceCache::getInstance();
    }

    /**
     * Gets the plugin name of the source file that called wp_mail.
     *
     * @return string The plugin name, or 'Unknown Source' if not found.
     */
    public function get_source_plugin_name()
    {
        // First check if we have cached plugin name
        if ($this->cache->hasPluginName()) {
            return $this->cache->getPluginName();
        }

        // Get source info only if plugin name is not cached
        $source_info = $this->get_source_info();

        $source_file = isset($source_info['file']) ? $source_info['file'] : self::UNKNOWN_SOURCE;

        if (strpos($source_file, WP_CONTENT_DIR . '/mu-plugins') !== false) {
            $plugin_name = self::MUST_USE_PLUGIN;
        } elseif (strpos($source_file, WP_PLUGIN_DIR) !== false) {
            // get folder name after plugins folder ( plugin name )
            $relative_path = str_replace(WP_PLUGIN_DIR . '/', '', $source_file);
            $parts = explode('/', $relative_path, 2);
            $plugin_name = (count($parts) > 0) ? $parts[0] : self::UNKNOWN_SOURCE;
        } elseif (strpos($source_file, ABSPATH . 'wp-includes') !== false) {
            $plugin_name = self::CORE_WP;
        } elseif (strpos($source_file, get_template_directory()) === 0) {
            $plugin_name = $this->get_theme_name();
        } else {
            $plugin_name = self::UNKNOWN_SOURCE;
        }

        $this->cache->setPluginName($plugin_name);
        return $plugin_name;
    }

    /**
     * Gets the theme name.
     *
     * @return string The theme name, or 'Unknown Source' if not found.
     */
    private function get_theme_name() {
        $theme = wp_get_theme();
        if ($theme && $theme->exists()) {
            return $theme->get('Name');
        }

        return self::UNKNOWN_SOURCE;
    }

    /**
     * Gets the source file and line number of the wp_mail call.
     *
     * @return array An array containing 'file' and 'line', or an empty array if not found.
     */
    private function get_source_info()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 100); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

        foreach ($backtrace as $item) {
            if (isset($item['function']) && $item['function'] === 'wp_mail') {
                return [
                    'file' => isset($item['file']) ? $item['file'] : 'unknown',
                    'line' => isset($item['line']) ? $item['line'] : 'unknown',
                ];
            }
        }

        return [];
    }
}
