<?php
namespace WeDevs\ERP_PRO\Traits;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Singleton Trait
 *
 * @since 0.0.1
 */
trait Singleton {

    /**
     * Singleton class instance holder
     *
     * @since 0.0.1
     *
     * @var object
     */
    protected static $instance;

    /**
     * Cloning is forbidden.
     *
     * @since 0.0.1
     */
    private function __clone() {
        // Cloning is forbidden.
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 0.0.1
     */
    public function __wakeup() {
        // Unserializing instances of this class is forbidden.
    }

    /**
     * Make a class instance
     *
     * @since 0.0.1
     *
     * @return object
     */
    public static function init() {

        if ( ! self::$instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
