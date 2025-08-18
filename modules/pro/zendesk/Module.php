<?php
namespace WeDevs\ERP_PRO\PRO\Zendesk;

// don't call the file directly
use WeDevs\ERP\Zendesk\Ajax;
use WeDevs\ERP\Zendesk\Settings;
use WeDevs\ERP\Zendesk\User;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * ERP_Zendesk_Integration class
 */
class Module {
    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.2.0';

    /**
     * Constructor for the ERP_Zendesk class
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
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_zendesk', array( $this, 'activate' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_zendesk', array( $this, 'deactivate' ) );

        $this->define_constants();
        $this->init_hooks();
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'ERP_ZENDESK_VERSION', $this->version );
        define( 'ERP_ZENDESK_FILE', __FILE__ );
        define( 'ERP_ZENDESK_PATH', dirname( ERP_ZENDESK_FILE ) );
        define( 'ERP_ZENDESK_INCLUDES', ERP_ZENDESK_PATH . '/includes' );
        define( 'ERP_ZENDESK_URL', plugins_url( '', ERP_ZENDESK_FILE ) );
        define( 'ERP_ZENDESK_ASSETS', ERP_ZENDESK_URL . '/assets' );
    }

    /**
     * Initializes the ERP_Zendesk() class
     *
     * Checks for an existing ERP_Zendesk() instance
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
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        update_option( 'erp_zendesk_version', ERP_ZENDESK_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {
        // Loads frontend scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        //
        add_action( 'erp_crm_contact_left_widgets', array( $this, 'erp_zendesk_customer_activity' ), 8, 1 );
        add_action( 'admin_init', array( $this, 'init_classes' ) );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [ $this, 'plugin_action_links' ] );

        // Delete people meta
        add_action( 'erp_after_delete_people', array( $this, 'delete_contact' ), 10, 2 );

        // Integration settings field
        add_filter( 'erp_integration_classes', [ $this, 'erp_zendesk_settings_page' ] );
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
     * Instantiate all required class
     *
     * @return void
     */
    public function init_classes() {
        new Settings();
        new User();
        new Ajax();
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts( $hook ) {
        if ( 'crm_page_erp-sales-customers' !== $hook && 'wp-erp_page_erp-crm' !== $hook ) {
            return ;
        }

        if ( isset( $_GET['section'] ) && $_GET['section'] !== 'contacts' ) {
            return;
        }

        $contact_id = null;
        if ( isset( $_GET['id'] ) && ( !empty( $_GET['id'] ) ) ) {
            $contact_id = $_GET['id'];
        }

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'erp-zendesk', plugins_url( 'assets/css/erp-zendesk.css', __FILE__ ), false, date( 'Ymd' ) );
        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'erp-zendesk', plugins_url( 'assets/js/erp-zendesk.js', __FILE__ ), array( 'jquery' ), false, true );
        /**
         * Example for setting up text strings from Javascript files for localization
         *
         * Uncomment line below and replace with proper localization variables.
         */
        wp_localize_script( 'erp-zendesk', 'erpZendesk', array(
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'contact_id' => $contact_id,
            'nonce'      => wp_create_nonce()
        ) );
    }

    /**
     * ERP Zendesk customer activity list
     *
     * @return void
     */
    public function erp_zendesk_customer_activity() {
        ?>
        <div class="postbox erp-zendesk-activity">
            <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'erp-pro' ); ?>"><br></div>
            <h3 class="erp-hndle"><span><?php _e( 'Recent tickets on Zendesk', 'erp-pro' ); ?></span></h3>
            <div class="inside loading">

            </div>
        </div><!-- .postbox -->
        <?php
    }

    /**
     * Delete people contact
     *
     * @param  integer $people_id
     * @param  array $data
     * @return bool
     */
    public function delete_contact( $people_id, $data ) {
        if ( ! current_user_can( 'erp_crm_delete_contact' ) ) {
            return;
        }
        erp_people_delete_meta( $people_id, 'zendesk_user_id' );
    }

    /**
     * Zendesk integration settings page
     *
     * @param $settings Main Settings Instance
     * @return array
     * @since 1.0.3
     */
    function erp_zendesk_settings_page( $settings ) {
        $settings['zendesk'] = new Settings();

        return $settings;
    }

    /* gets the data from a URL */
} // ERP_Zendesk
