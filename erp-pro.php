<?php
/**
 * Plugin Name: WP ERP PRO
 * Description: Automate & Manage your growing business even better using Human Resource, Customer Relations, Accounts Management right inside your WordPress
 * Plugin URI: https://wperp.com
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.3.6
 * License: GPL2
 * Text Domain: erp-pro
 * Domain Path: /languages/
 */

// don't call the file directly

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class WP_ERP_Pro {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.3.6';

    /**
     * Plugin version
     *
     * @var string
     */

    /**
     * Singleton pattern
     *
     * @var bool $instance
     */
    private static $instance = false;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Initializes the WP_ERP_Pro() class
     *
     * Checks for an existing WP_ERP_Pro() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

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
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        trigger_error( sprintf( 'Undefined property: %s', self::class . '::$' . $prop ) );
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return true;
        }

        return false;
    }

    /**
     * Constructor for the WP_ERP_Pro class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    private function __construct() {
        // autoload composer packages
        require_once __DIR__ . '/vendor/autoload.php';

        // define constants
        $this->define_constants();

        if ( file_exists( __DIR__ . '/local_env.php' ) ) {
            require_once 'local_env.php';
        }

        //spl_autoload_register( array( $this, 'erp_pro_autoload' ) );

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'check_erp_exist' ), 1 );

        // without erp_email hooks, extensions doesn't load properly. previously it was plugins_loaded hook
        add_action( 'erp_email', array( $this, 'init_module_class' ), 1 );
        add_action( 'erp_email', array( $this, 'init_plugin' ), 2 );
    }

    /**
     * Define all pro module constant
     *
     * @since  0.0.1
     *
     * @return void
     */
    public function define_constants() {
        define( 'ERP_PRO_PLUGIN_VERSION', $this->version );
        define( 'ERP_PRO_FILE', __FILE__ );
        define( 'ERP_PRO_DIR', dirname( ERP_PRO_FILE ) );
        define( 'ERP_PRO_INC', ERP_PRO_DIR . '/includes' );
        define( 'ERP_PRO_TEMPLATE_DIR', ERP_PRO_INC . '/templates' );
        define( 'ERP_PRO_ADMIN_DIR', ERP_PRO_INC . '/Admin' );
        define( 'ERP_PRO_PLUGIN_ASSEST', plugins_url( 'assets', ERP_PRO_FILE ) );
        define( 'ERP_PRO_MODULE_DIR', ERP_PRO_DIR . '/modules' );
        define( 'ERP_PRO_MODULE_URL', plugins_url( 'modules', ERP_PRO_FILE ) );
        define( 'ERP_PRO_FEATURE_DIR', ERP_PRO_INC . '/Feature' );
        define( 'ERP_PRO_FEATURE_URL', plugins_url( 'Feature', ERP_PRO_FEATURE_DIR ) );
    }

    public function erp_pro_autoload( $class ) {
        $file = '';
        if ( false !== strpos( $class, 'WeDevs\ERP_PRO' ) ) {
            $class_name = str_replace( array( 'WeDevs\ERP_PRO', '\\' ), array( '', '/' ), $class );
            $class_name = explode( '/', $class_name );

            $final_class_name = '';

            foreach ( $class_name as $name ) {
                // fix uppercase CRM / HRM
                if ( in_array( $name, [ 'HRM', 'CRM', 'hrm', 'crm', 'BP', 'bp' ] ) ) {
                    $final_class_name .= $name;
                } else {
                    $final_class_name .= ucfirst( strtolower( $name ) );
                }

                if ( next( $class_name ) ) {
                    $final_class_name .= '/';
                }
            }

            $file = ERP_PRO_INC . $final_class_name . '.php';
        }

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }

    /**
     * Placeholder for activation function
     */
    public function activate() {
        $installer = new \WeDevs\ERP_PRO\Install\Installer();
        $installer->do_install();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {
    }

    /**
     * Check is erp active or not
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function check_erp_exist() {
        if ( ! class_exists( 'WeDevs_ERP' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            add_action( 'admin_notices', array( $this, 'activation_notice' ) );
            add_action( 'wp_ajax_wp_erp_pro_install_erp', array( $this, 'install_erp' ) );
        }
    }

    /**
     * ERP activation notice
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function activation_notice() {
        $plugin_file      = basename( dirname( __FILE__ ) ) . '/erp-pro.php';
        $core_plugin_file = 'erp/wp-erp.php';

        require_once ERP_PRO_TEMPLATE_DIR . '/wp-erp-activation-notice.php';
    }

    /**
     * Install erp
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function install_erp() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'wp-erp-pro-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'erp';
        $api    = plugins_api( 'plugin_information', array(
			'slug' => $plugin,
			'fields' => array( 'sections' => false ),
		) );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );
        activate_plugin( 'erp/wp-erp.php' );

        wp_send_json_success();
    }

    /**
     * Load all things
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function init_plugin() {
        if ( ! class_exists( 'WeDevs_ERP' ) ) {
            return;
        }
        $update = new \WeDevs\ERP_PRO\Admin\ComposerUpgradeNotice();
        if ( $update->need_to_upgrade() ) {
            return;
        }
        $this->includes();
        $this->load_actions();
        $this->load_filters();
    }

    public function init_module_class() {
        // load module class
        $this->container['update'] = \WeDevs\ERP_PRO\Admin\Update::init();

        $modules = \WeDevs\ERP_PRO\Module::init();
        $this->container['module'] = $modules;
    }

    /**
     * Load all includes file for pro
     *
     * @since 0.0.1
     * @since 1.0.4 Included erp-helper file
     *
     * @return void
     */
    public function includes() {
        // load admin related files
        if ( is_admin() ) {
            new \WeDevs\ERP_PRO\Admin\Admin();

            // load update class
            new \WeDevs\ERP_PRO\Updates\Updater();
        }

        //load ajax hooks
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\ERP_PRO\Admin\Ajax();
        }

        //  load active modules.
        $this->container['module']->load_active_modules();

        if ( $this->container['update']->is_valid_license() ) {

            // instantiate CRM features
            \WeDevs\ERP_PRO\Feature\CRM\Base::init();

            // instantiate HRM features
            \WeDevs\ERP_PRO\Feature\HRM\Base::init();

            // instantiate accounting features
            \WeDevs\ERP_PRO\Feature\Accounting\Base::init();
        }
    }

    /**
     * Load all necessary Actions hooks
     *
     * @since 0.0.1
     *
     * @return void [description]
     */
    public function load_actions() {
        // init the classes
        add_action( 'init', array( $this, 'localization_setup' ) );

        add_action( 'rest_api_init', array( $this, 'load_rest_controllers' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );

        if ( ! is_admin() ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
        }

        // display plugin activation errors
        add_action( 'admin_notices', array( $this, 'display_activation_errors' ) );

        add_action( 'erp_module_after_deactivated', array( $this, 'module_deactivated' ) );
    }

    /**
     * Load all Filters Hook
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function load_filters() {
    }

    /**
     * Initialize plugin for localization
     *
     * @since 0.0.1
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wp-erp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function load_rest_controllers() {
        $obj = new \WeDevs\ERP_PRO\REST\ModulesController();
        $obj->register_routes();
    }

    /**
     * Register all scripts
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function register_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    }

    /**
     * Enqueue frontend scripts
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function enqueue_scripts() {
    }

    /**
    * Deactivates the extensions while deactivating the parent module like HRM, CRM, Accounting
    *
    * @since 1.2.3
    *
    * @param string $module_name
    *
    * @return void
    */
    public function module_deactivated( $module_name ) {
        $extensions        = $this->container['module']->get_all_modules();
        $to_be_deactivated = [];

        foreach ( $extensions as $id => $extension ) {
            $modules = $extension['category'];

            if ( ! in_array( $module_name, $modules, true ) || ! $this->container['module']->is_active( $id ) ) {
                continue;
            }

            $to_be_deactivated[] = $id;
        }

        $ajax = new \WeDevs\ERP_PRO\Admin\Ajax();

        $ajax->toggle_extension_helper( $to_be_deactivated, 'deactivate' );
    }

    /**
     * Admin scripts
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function admin_enqueue_scripts() {
        $localize_script = apply_filters( 'erp_pro_localize_script', array(
            'nonce'                  => wp_create_nonce( 'wp-erp-pro-nonce' ),
        ) );
    }

    /**
     * This method will display plugin/modules activation errors
     *
     * @since 0.0.1
     */
    public function display_activation_errors() {
        if ( class_exists( '\WeDevs\ERP\ErpErrors' ) ) {
            $errors = new \WeDevs\ERP\ErpErrors( 'erp_pro_extension_error' );
        } else {
            $errors = new \WeDevs\ERP\ERP_Errors( 'erp_pro_extension_error' );
        }

        if ( $errors->has_error() ) {
            echo $errors->display();
        }
    }

    /**
     * Get plugin path
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Set plugin in pro mode
     *
     * @since 0.0.1
     *
     * @param boolean $is_pro
     *
     * @return boolean
     */
    function set_as_pro( $is_pro ) {
        return true;
    }
}

/**
 * Load erp pro plugin
 *
 * @since 1.0.0
 *
 * @return void
 * */
function wp_erp_pro() {
    return WP_ERP_Pro::init();
}

wp_erp_pro();
