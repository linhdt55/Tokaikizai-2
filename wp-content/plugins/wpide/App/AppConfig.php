<?php
namespace WPIDE\App;

use WPIDE\App\Config\Config;
use const WPIDE\Constants\DIR;

class AppConfig {

    /* @var $config Config */
    protected static $config;

    public static function load(): Config
    {
        if (empty(self::$config)) {
            // Only load config after WordPress init
            if (!did_action('init')) {
                add_action('init', fn() => self::$config = new Config(require DIR.'_configuration.php'));
                // Return empty config temporarily
                self::$config = new Config([]);
            } else {
                self::$config = new Config(require DIR.'_configuration.php');
            }
        }
        return self::$config;
    }

    public static function get($key = null, $default = null)
    {
        return self::load()->get($key, $default);
    }

    public static function update($key , $value)
    {
        return self::load()->update($key, $value);
    }

    public static function delete($key)
    {
        self::load()->delete($key);
    }

    public static function getField($key)
    {
        return self::load()->getField($key);
    }

    public static function fieldExists($key): bool
    {
        return self::load()->fieldExists($key);
    }
}
