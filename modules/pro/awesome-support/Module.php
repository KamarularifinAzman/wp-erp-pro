<?php
namespace WeDevs\ERP_PRO\PRO\AwesomeSupport;

use WeDevs\ERP\ErpErrors;
use WeDevs\AwesomeSupport\Widget;
use WeDevs\AwesomeSupport\Import;
use WeDevs\AwesomeSupport\Settings;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main initiation class
 *
 * @since 1.0.0
 */
class Module {

    /**
     * Add-on Version
     *
     * @since 1.0.0
     * @var  string
     */
    public $version = '1.1.0';

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it does't find one, creates it.
     *
     * @since 1.0.0
     *
     * @return object Class instance
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct() {
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_awesome_support', array( $this, 'activate' ) );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        //after erp load
        add_action( 'erp_crm_loaded', [ $this, 'init_actions' ] );
    }

    /**
     * Fire on activate
     *
     * @since 1.0.0
     */
    public function activate() {

        if ( ! class_exists( 'Awesome_Support' ) ) {
            // deactivate current module
            wp_erp_pro()->module->deactivate_modules( [ 'awesome_support' ] );

            //print error
            $error_msg = sprintf(
                __( 'You need to install %s in order to use %s', 'erp-pro' ),
                '<a href="https://wordpress.org/plugins/awesome-support/" target="_blank"><strong>Awesome Support</strong></a>',
                '<strong>WP ERP - Awesome Support</strong>'
            );

            $error = new ErpErrors( 'erp_pro_extension_error' );
            $error->add( $error_msg );
            $error->save();
        }
    }

    /**
     * Define constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPERP_AS_VERSION', $this->version );
        define( 'WPERP_AS_FILE', __FILE__ );
        define( 'WPERP_AS_PATH', dirname( WPERP_AS_FILE ) );
        define( 'WPERP_AS_INCLUDES', WPERP_AS_PATH . '/includes' );
        define( 'WPERP_AS_URL', plugins_url( '', WPERP_AS_FILE ) );
        define( 'WPERP_AS_ASSETS', WPERP_AS_URL . '/assets' );
        define( 'WPERP_AS_VIEWS', WPERP_AS_PATH . '/views' );
        define( 'WPERP_AS_TEMPLATES_DIR', WPERP_AS_PATH . '/templates' );
    }

    /**
     * Include required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require ERP_PRO_MODULE_DIR . '/pro/awesome-support/includes/functions.php';
    }

    /**
     * Initialize WordPress action hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_actions() {
        if ( ! wperp()->modules->is_module_active('crm') ) {
            return;
        }
        add_action( 'init', [ $this, 'instantiate' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'load_assets' ] );
        add_filter( 'erp_integration_classes', [ $this, 'erp_as_settings_page' ] );
    }

    /**
     * Instantiate classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function instantiate() {
        new Settings();
        new Import();
        new Widget();
    }

    /**
     * Add all the assets required by the plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    function load_assets() {
        wp_register_style( 'erp-awesome-support', ERP_PRO_MODULE_URL . '/pro/awesome-support/assets/css/erp-awesome-support.css', [], date( 'i' ) );
        wp_enqueue_style( 'erp-awesome-support' );
    }

    /**
     * Awesome Support integration settings page
     *
     * @param $settings Main Settings Instance
     * @return array
     * @since 1.1.0
     */
    function erp_as_settings_page( $settings ) {
        $settings['awesome_support'] = new Settings();

        return $settings;
    }
}
