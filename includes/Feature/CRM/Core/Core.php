<?php
namespace WeDevs\ERP_PRO\Feature\CRM\Core;

use \WeDevs\ERP\Framework\Traits\Hooker;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base_Plugin class
 *
 * @since 1.2.2
 *
 * @class Base_Plugin The class that holds the entire Base_Plugin plugin
 */
class Core {

    use Hooker;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Class constructor
     */
    private function __construct() {
        $this->init_classes();
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * init classes
     *
     * @return void
     */
    public function init_classes() {
    }
}
