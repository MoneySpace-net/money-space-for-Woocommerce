<?php

namespace MoneySpace;

class MNS_Router_Utility
{

    const QUERY_VAR = 'MNS_Route';
    const PLUGIN_NAME = 'MS Router';
    const DEBUG = FALSE;
    const MIN_PHP_VERSION = '5.2';
    const MIN_WP_VERSION = '3.0';
    const DB_VERSION = 1;
    const MONEYSPACE_PLUGIN_INIT_HOOK = 'MONEYSPACE_Router_init';

    /**
     * @static
     * @return string The system path to this plugin's directory, with no trailing slash
     */
    public static function plugin_path()
    {
        return WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
    }

    /**
     * @static
     * @return string The url to this plugin's directory, with no trailing slash
     */
    public static function plugin_url()
    {
        return WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
    }

    /**
     * Check that the minimum PHP and WP versions are met
     *
     * @static
     * @param string $php_version
     * @param string $wp_version
     * @return bool Whether the test passed
     */
    public static function prerequisites_met($php_version, $wp_version)
    {
        $pass = TRUE;
        $pass = $pass && version_compare($php_version, self::MIN_PHP_VERSION, '>=');
        $pass = $pass && version_compare($wp_version, self::MIN_WP_VERSION, '>=');
        return $pass;
    }

    public static function failed_to_load_notices($php_version = self::MIN_PHP_VERSION, $wp_version = self::MIN_WP_VERSION)
    {
        $message = sprintf(
            // translators: 1: Plugin name. 2: Minimum WordPress version. 3: Minimum PHP version.
            __('%1$s requires WordPress %2$s or higher and PHP %3$s or higher.', 'money-space'),
            self::PLUGIN_NAME,
            $wp_version,
            $php_version
        );

        printf('<div class="error"><p>%s</p></div>', esc_html($message));
    }

    public static function init()
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Constant contains proper 'MONEYSPACE' prefix
        do_action(self::MONEYSPACE_PLUGIN_INIT_HOOK);
    }
}
