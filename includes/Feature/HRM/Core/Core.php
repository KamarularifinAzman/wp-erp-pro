<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Core;

use \WeDevs\ERP\Framework\Traits\Hooker;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base_Plugin class
 *
 * @since 1.8.6
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
     * Constructor for the Base_Plugin class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    private function __construct() {
        $this->define_constants();
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
     * define the plugin constant
     *
     * @return void
     */
    public function define_constants() {
        define( 'ERP_HRM_CORE_FILE', __FILE__ );
        define( 'ERP_HRM_CORE_PATH', dirname( ERP_HRM_CORE_FILE ) );
        define( 'ERP_HRM_CORE_INCLUDES', ERP_HRM_CORE_PATH . '/includes' );
        define( 'ERP_HRM_CORE_URL', plugins_url( '', ERP_HRM_CORE_FILE ) );
        define( 'ERP_HRM_CORE_ASSETS', ERP_HRM_CORE_URL . '/assets' );
    }

    /**
     * init classes
     *
     * @return void
     */
    public function init_classes() {
        new Assets();
    }
}
