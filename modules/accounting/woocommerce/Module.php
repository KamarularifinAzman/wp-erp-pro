<?php
namespace WeDevs\ERP_PRO\Accounting\woocommerce;
use WeDevs\ERP\ErpErrors;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs ERP WooCommerce Main Class
 */
class Module {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.5.0';


    /**
     * Initializes the WeDevs_ERP_WooCommerce class
     *
     * Checks for an existing WeDevs_ERP_WooCommerce instance
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
     * Constructor for the WeDevs_ERP_WooCommerce class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct() {
        // plugin not installed - notice
        add_action( 'admin_notices', [ $this, 'admin_notice' ] );

        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_woocommerce', array( $this, 'activate' ) );

        // Make sure both ERP and WC is loaded before initialize
        add_action( 'erp_loaded', [ $this, 'after_erp_loaded' ] );
        add_action( 'woocommerce_loaded', [ $this, 'after_wc_loaded' ] );
    }

    /**
     * Display an error message if WP ERP is not active
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_notice() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            // deactivate current module
            wp_erp_pro()->module->deactivate_modules( [ 'woocommerce' ] );

            //print error
            $error_msg = sprintf(
                __( 'You need to install %s in order to use %s', 'erp-pro' ),
                '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce</strong></a>',
                '<strong>WP ERP - WooCommerce</strong>'
            );

            $error = new ErpErrors( 'erp_pro_extension_error' );
            $error->add( $error_msg );
            $error->save();
        }
    }

    /**
     * Executes while Plugin Activation
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate() {

        if ( ! class_exists( 'WooCommerce' ) ) {
            // deactivate current module
            wp_erp_pro()->module->deactivate_modules( [ 'woocommerce' ] );

            //print error
            $error_msg = sprintf(
                __( 'You need to install %s in order to use %s', 'erp-pro' ),
                '<a href="https://wordpress.org/plugins/awesome-support/" target="_blank"><strong>WooCommerce</strong></a>',
                '<strong>WP ERP - WooCommerce</strong>'
            );

            $error = new ErpErrors( 'erp_pro_extension_error' );
            $error->add( $error_msg );
            $error->save();
        }
        else {
            // Create all necessary tables
            $this->create_tables();
        }
    }

    /**
     * Execute after ERP is loaded
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function after_erp_loaded() {
        if ( ! did_action( 'woocommerce_loaded' ) ) {
            add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );

        } else {
            $this->init_plugin();
        }
    }

    /**
     * Execute after WooCommerce is loaded
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function after_wc_loaded() {
        if ( ! did_action( 'erp_loaded' ) ) {
            add_action( 'erp_loaded', [ $this, 'init_plugin' ] );

        } else {
            $this->init_plugin();
        }
    }

    /**
     * Execute if ERP main is installed
     *
     * @since 1.0.0
     * @since 1.1.0 Check if `WPERP_WOOCOMMERCE_VERSION` is all ready defined
     *
     * @return void
     */
    public function init_plugin() {
        if ( defined( 'WPERP_WOOCOMMERCE_VERSION' ) || ! defined( 'WC_VERSION' ) ) {
            return;
        }

        $this->define_constants();
        $this->includes();
        $this->init_classes();
        $this->init_actions();
        $this->init_filters();
    }

    /**
     * Define Add-on constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPERP_WOOCOMMERCE_VERSION', $this->version );
        define( 'WPERP_WOOCOMMERCE_FILE', __FILE__ );
        define( 'WPERP_WOOCOMMERCE_PATH', dirname( WPERP_WOOCOMMERCE_FILE ) );
        define( 'WPERP_WOOCOMMERCE_INCLUDES', WPERP_WOOCOMMERCE_PATH . '/includes' );
        define( 'WPERP_WOOCOMMERCE_URL', plugins_url( '', WPERP_WOOCOMMERCE_FILE ) );
        define( 'WPERP_WOOCOMMERCE_ASSETS', WPERP_WOOCOMMERCE_URL . '/assets' );
    }

    /**
     * Include the required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/Model/Order_Product.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/Model/Product_Order.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/customer.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/settings.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/subscription.php';

        if ( version_compare( WC_VERSION , '3.0', '>=' ) ) {
            if ( version_compare( WPERP_VERSION , '1.5.0', '>=' ) ) {
                include_once WPERP_WOOCOMMERCE_INCLUDES . '/accounting-new.php';
            } else {
                include_once WPERP_WOOCOMMERCE_INCLUDES . '/accounting.php';
            }

            include_once WPERP_WOOCOMMERCE_INCLUDES . '/functions.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/orders.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/segment.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/products.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/tax.php';

        } else {
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/accounting.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/functions.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/orders.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/segment.php';
        }

        include_once WPERP_WOOCOMMERCE_INCLUDES . '/class-updates.php'; //@since 1.3.2
    }

    /**
     * Instantiate classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_classes() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/CLI/Commands.php';
            \WP_CLI::add_command( 'erpwc', '\WeDevs\ERP\WooCommerce\CLI\Commands' );
        }

        \WeDevs\ERP\WooCommerce\Order::init();
        \WeDevs\ERP\WooCommerce\Customer::init();
        \WeDevs\ERP\WooCommerce\Segment::init();
        new \WeDevs\ERP\WooCommerce\Subscription();
        \WeDevs\ERP\WooCommerce\Products::init();
        \WeDevs\ERP\WooCommerce\Tax::init();

        // Updater class
        new \WeDevs\ERP\WooCommerce\Updates();
    }

    /**
     * Initializes action hooks
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function init_actions() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Initializes action filters
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function init_filters() {
        // Settings page filter
        add_filter( 'erp_settings_pages', [ $this, 'add_settings_page' ] );
        add_action( 'erp_settings_save_erp-woocommerce_section', [ $this, 'get_settings_instance' ] );
    }

    /**
     * Retrieves woocommerce settings cclass instance
     *
     * @since 1.3.6
     *
     * @param string $module
     *
     * @return Object
     */
    public function get_settings_instance( $module ) {
        return new \WeDevs\ERP\WooCommerce\Settings();
    }

    /**
     * Register all styles and scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'erp-wc-style', WPERP_WOOCOMMERCE_ASSETS . '/css/admin.css' );

        if ( empty( $_GET['page'] ) || 'erp-settings' !== $_GET['page'] ) {
            return;
        }

        wp_enqueue_script( 'erp-wc-settings', WPERP_WOOCOMMERCE_ASSETS . '/js/settings.js', ['jquery'], false, true );

        wp_localize_script( 'erp-wc-settings', 'erpWC', array(
            'nonce' => wp_create_nonce( 'erp-wc-nonce' ),
        ) );

        $product_categories = erp_acct_get_all_product_cats();

        $product_cat = [];

        foreach ( $product_categories as $product_category ) {
            $product_cat[ $product_category['id'] ] = $product_category['name'];
        }

        // get all tax categories
        $args = [
            'number'  => -1,
        ];

        $tax_categories = erp_acct_get_all_tax_cats( $args );

        $tax_cat = [];

        foreach ( $tax_categories as $tax_category ) {
            $tax_cat[ $tax_category['id'] ] = $tax_category['name'];
        }

        // get all vendors
        $args = [
            'number' => '-1',
            'type'   => 'vendor',
            'no_object' => true,
        ];

        $vendors = erp_acct_get_accounting_people( $args );

        $owners = [];

        foreach ( $vendors as $vendor ) {
            $name = $vendor['first_name'];
            $name .= ! empty( $vendor['last_name'] ) ? ' ' . $vendor['last_name'] : '';
            $owners[ $vendor['id'] ] = $name;
        }

        $product_types = [];

        foreach ( erp_acct_get_product_types() as $product_type ) {
            $product_types[ $product_type->id ] = $product_type->name;
        }

        wp_localize_script( 'erp-wc-settings', 'erp_wc_settings', [
            'nonce'         => wp_create_nonce( 'erp-settings-nonce' ),
            'product_cat'   => $product_cat,
            'product_types' => $product_types,
            'tax_cat'       => $tax_cat,
            'vendors'       => $owners,
        ] );
    }

    /**
     * Register HR settings page
     *
     * @since 1.0.0
     *
     * @param array
     */
    public function add_settings_page( $settings = [] ) {
        $settings[] = new \WeDevs\ERP\WooCommerce\Settings();

        return $settings;
    }

    /**
     * Create table schema
     *
     * @since 1.0.0
     * @since 1.3.2 added woocommerce product sync support
     *
     * @return void
     **/
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_schema = [
            "CREATE TABLE {$wpdb->prefix}erp_wc_orders (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                people_id bigint(20) DEFAULT NULL,
                order_id bigint(20) DEFAULT NULL,
                order_status varchar(11) DEFAULT NULL,
                order_date datetime DEFAULT NULL,
                order_total decimal(13,2) DEFAULT NULL,
                accounting tinyint(4) DEFAULT '0',
                PRIMARY KEY  (id),
                KEY people_id (people_id),
                KEY order_id (order_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_wc_order_product (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                order_id bigint(20) DEFAULT NULL,
                product_id bigint(20) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY order_id (order_id),
                KEY product_id (product_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_product_sync_types (
                id int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) DEFAULT NULL,
                slug varchar(100) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;",
        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

        //@since 1.3.2
        // insert required rows
        if ( ! $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}erp_acct_product_sync_types" ) ) {
            $date       = date( 'Y-m-d' );
            $user_id    = get_current_user_id();
            $wpdb->query(
                "INSERT INTO {$wpdb->prefix}erp_acct_product_sync_types (`name`, slug, created_at, created_by) VALUES
                ('System', 'system', '$date', '$user_id'), ('WooCommerce', 'woocommerce', '$date', '$user_id')"
            );
        }

        // get system sync_type_id
        $system_sync_type_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM `{$wpdb->prefix}erp_acct_product_sync_types` WHERE slug = %s",
                array( 'system' )
            )
        );

        $table = $wpdb->prefix . 'erp_acct_products';
        $cols  = $wpdb->get_col( "DESC $table" );

        if ( ! in_array( 'product_sync_type_id', $cols ) ) {
            $wpdb->query(
                "ALTER TABLE $table ADD `product_sync_type_id` INT(11) NOT NULL DEFAULT '$system_sync_type_id'  AFTER `product_type_id`,  ADD   INDEX  `product_sync_type_id` (`product_sync_type_id`);"
            );
        }

        if ( ! in_array( 'synced_product_id', $cols ) ) {
            $wpdb->query(
                "ALTER TABLE $table ADD `synced_product_id` BIGINT(20) NOT NULL DEFAULT '0'  AFTER `product_sync_type_id`;"
            );
        }
    }
}
