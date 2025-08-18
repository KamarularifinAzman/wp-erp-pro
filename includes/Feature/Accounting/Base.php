<?php
namespace WeDevs\ERP_PRO\Feature\Accounting;

use \WeDevs\ERP\Framework\Traits\Hooker;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base class Accounting features
 *
 * @since 1.2.3
 */
final class Base {

    use Hooker;

    /**
     * Holds various class instances
     *
     * @since 1.2.3
     *
     * @var array
     */
    private $container = [];

    /**
     * Class constructor
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.2.3
     *
     * @return void
     */
    private function __construct() {

        if ( wperp()->modules->is_module_active('accounting') ) {
            $this->define_constants();
            $this->action( 'erp_accounting_loaded', 'erp_accounting_loaded' );
        }
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.2.3
     *
     * @return object
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Initializes classes and hooks
     *
     * @return void
     */
    public function erp_accounting_loaded() {
        $this->init_classes();
        $this->includes();
    }

    /**
     * define the plugin constant
     *
     * @return void
     */
    public function define_constants() {
        define( 'ERP_ACCT_FEATURE_FILE', __FILE__ );
        define( 'ERP_ACCT_FEATURE_PATH', dirname( ERP_ACCT_FEATURE_FILE ) );
        define( 'ERP_ACCT_FEATURE_URL', plugins_url( '', ERP_ACCT_FEATURE_FILE ) );
    }

    /**
     * init classes
     *
     * @since 1.2.3
     *
     * @return void
     */
    public function init_classes() {
        $this->container['rest'] = new Api\Rest_Controller();
        $this->container['core'] = new Core\Core();
    }

    /**
     * Includes necessary files
     *
     * @since 1.2.3
     *
     * @return void
     */
    public function includes() {
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[$prop] );
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );
        }
    }
}
