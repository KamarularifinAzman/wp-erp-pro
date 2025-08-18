<?php
namespace WeDevs\ERP_PRO\PRO\Mailchimp;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Mailchimp integration class
 */
class Module {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.3.0';

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
        define( 'ERP_MAILCHIMP_FILE', __FILE__ );
        define( 'ERP_MAILCHIMP_PATH', dirname( ERP_MAILCHIMP_FILE ) );
        define( 'ERP_MAILCHIMP_INCLUDES', ERP_MAILCHIMP_PATH . '/includes' );
        define( 'ERP_MAILCHIMP_VIEWS', ERP_MAILCHIMP_INCLUDES . '/views' );
        define( 'ERP_MAILCHIMP_URL', plugins_url( '', ERP_MAILCHIMP_FILE ) );
        define( 'ERP_MAILCHIMP_ASSETS', ERP_MAILCHIMP_URL . '/assets' );
    }

    /**
     * Init the plugin classes.
     *
     * @return void
     */
    private function init_classes() {
        new \WeDevs\ERP\Mailchimp\AdminMenu();
        new \WeDevs\ERP\Mailchimp\Webhook_Manager();
        new \WeDevs\ERP\Mailchimp\Sync_Handler();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\ERP\Mailchimp\AjaxHandler();
        }
    }

    /**
     * Init the plugin actions.
     *
     * @return void
     */
    private function init_actions() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_footer', 'erp_mailchimp_enqueue_js' );
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
        wp_enqueue_style( 'erp-mailchimp-styles', ERP_MAILCHIMP_ASSETS . '/css/style.css', false );

        wp_enqueue_script( 'erp-mailchimp-settings', ERP_MAILCHIMP_ASSETS . '/js/settings.js', ['erp-settings'], false, true);
        wp_localize_script( 'erp-mailchimp-settings', 'erp_crm_mailchimp_values', [
            'contact-groups' => erp_crm_get_contact_groups( [ 'number' => -1 ] ),
            'contact-owners' => erp_crm_get_crm_user(),
            'life-stages'    => erp_crm_get_life_stages_dropdown_raw(),
        ] );
    }

    /**
     * Register integrations.
     *
     * @param  array $integrations
     *
     * @return array
     */
    public function register_integrations( $integrations ) {
        $integrations['mailchimp'] = new \WeDevs\ERP\Mailchimp\Mailchimp_Integration();

        return $integrations;
    }
}
