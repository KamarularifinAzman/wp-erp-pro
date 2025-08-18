<?php
namespace WeDevs\ERP_PRO\PRO\Hubspot;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

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
        // due to composer autoload some classes were loading automatically
        if ( ! wp_erp_pro()->module->is_active( 'hubspot' ) ) {
            return;
        }

        include dirname( __FILE__ ) . '/includes/functions.php';
        include dirname( __FILE__ ) . '/includes/erp-helper.php';

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
        define( 'ERP_HUBSPOT_FILE', __FILE__ );
        define( 'ERP_HUBSPOT_PATH', dirname( ERP_HUBSPOT_FILE ) );
        define( 'ERP_HUBSPOT_INCLUDES', ERP_HUBSPOT_PATH . '/includes' );
        define( 'ERP_HUBSPOT_VIEWS', ERP_HUBSPOT_INCLUDES . '/views' );
        define( 'ERP_HUBSPOT_URL', plugins_url( '', ERP_HUBSPOT_FILE ) );
        define( 'ERP_HUBSPOT_ASSETS', ERP_HUBSPOT_URL . '/assets' );
    }

    /**
     * Init the plugin classes.
     *
     * @return void
     */
    private function init_classes() {
        new \WeDevs\ERP\Hubspot\AdminMenu();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\ERP\Hubspot\AjaxHandler();
        }
    }

    /**
     * Init the plugin actions.
     *
     * @return void
     */
    private function init_actions() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_footer', 'erp_hubspot_enqueue_js' );
    }

    /**
     * Init the plugin filters.
     *
     * @return void
     */
    private function init_filters() {
        add_filter( 'erp_integration_classes', [ $this, 'register_integrations' ] );
    }

    /**
     * Enqueue scripts.
     */
    public function enqueue_scripts() {
        // styles
        wp_enqueue_style( 'erp-hubspot-styles', ERP_HUBSPOT_ASSETS . '/css/style.css', false );
    }

    /**
     * Register integrations.
     *
     * @param  array $integrations
     *
     * @return array
     */
    public function register_integrations( $integrations ) {
        $integrations['hubspot'] = new \WeDevs\ERP\Hubspot\Hubspot_Integration();

        return $integrations;
    }
}
