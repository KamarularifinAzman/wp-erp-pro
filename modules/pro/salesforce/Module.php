<?php
namespace WeDevs\ERP_PRO\PRO\Salesforce;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Salesforce integration class
 */
class Module {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.2.0';

    /**
     * Class constructor.
     */
    private function __construct() {
        // load the addon
        add_action( 'erp_crm_loaded', array( $this, 'plugin_init' ) );
    }

    /**
     * Initialize the class.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Init the plugin.
     *
     * @return void
     */
    public function plugin_init() {
	    include dirname( __FILE__ ) . '/includes/erp-helper.php';
	    include dirname( __FILE__ ) . '/includes/functions.php';

        // Define constants
        $this->define_constants();

        // Instantiate classes
        $this->init_classes();

        // Initialize the action hooks
        $this->init_actions();

        // Initialize the filter hooks
        $this->init_filters();
    }

    /**
     * Define the plugin constants.
     *
     * @return void
     */
    private function define_constants() {
        define( 'ERP_SALESFORCE_FILE', __FILE__ );
        define( 'ERP_SALESFORCE_PATH', dirname( ERP_SALESFORCE_FILE ) );
        define( 'ERP_SALESFORCE_INCLUDES', ERP_SALESFORCE_PATH . '/includes' );
        define( 'ERP_SALESFORCE_VIEWS', ERP_SALESFORCE_INCLUDES . '/views' );
        define( 'ERP_SALESFORCE_URL', plugins_url( '', ERP_SALESFORCE_FILE ) );
        define( 'ERP_SALESFORCE_ASSETS', ERP_SALESFORCE_URL . '/assets' );
    }

    /**
     * Init the plugin classes.
     *
     * @return void
     */
    private function init_classes() {
        new \WeDevs\ERP\Salesforce\AdminMenu();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\ERP\Salesforce\AjaxHandler();
        }
    }

    /**
     * Init the plugin actions.
     *
     * @return void
     */
    private function init_actions() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_footer', 'erp_salesforce_enqueue_js' );
    }

    /**
     * Init the plugin filters.
     *
     * @return void
     */
    private function init_filters() {
        add_filter( 'erp_integration_classes', [ $this, 'register_integrations' ] );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [ $this, 'plugin_action_links' ] );
    }

    /**
     * Add action links
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=erp-settings#/erp-integration' ) . '">' . __( 'Settings', 'erp-pro' ) . '</a>';
        return $links;
    }

    /**
     * Enqueue scripts.
     */
    public function enqueue_scripts() {
        // styles
        wp_enqueue_style( 'erp-salesforce-styles', ERP_SALESFORCE_ASSETS . '/css/style.css', false );
    }

    /**
     * Register integrations.
     *
     * @param  array $integrations
     *
     * @return array
     */
    public function register_integrations( $integrations ) {
        $integrations['salesforce'] = new \WeDevs\ERP\Salesforce\SalesforceIntegration();

        return $integrations;
    }
}
