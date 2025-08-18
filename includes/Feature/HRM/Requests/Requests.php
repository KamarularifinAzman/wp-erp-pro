<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests;

use WeDevs\ERP\Framework\Traits\Hooker;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main class for employee requests
 * 
 * @since 1.2.0
 */
final class Requests {

    use Hooker;

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.2.0
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
     * Class constructor
     * 
     * @since 1.2.0
     */
    private function __construct() {
        $this->init_classes();
        $this->load_assets();
    }

    /**
     * Adds required hooks
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function load_assets() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'admin_footer', 'admin_templates' );
    }

    /**
     * Instantiates required classes
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function init_classes() {
        new Resignation\Main();
        new Remote_Work\Main();
    }

    /**
     * Enqueues necessary scripts
     *
     * @since 1.2.0
     * 
     * @param string $hook_suffix
     * 
     * @return void
     */
    public function admin_scripts( $hook_suffix ) {
        $section     = isset( $_GET['section'] )     ? sanitize_text_field( wp_unslash( $_GET['section'] ) )     : 'dashboard';
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : '';

        if ( 'wp-erp_page_erp-hr' === $hook_suffix ) {
            if ( 'my-profile' === $section || 'dashboard' === $section ) {
                wp_enqueue_style( 'erp-pro-requests', ERP_PRO_FEATURE_URL . '/HRM/Requests/assets/css/styles.css', [], ERP_PRO_PLUGIN_VERSION );
                wp_enqueue_script( 'erp-pro-requests', ERP_PRO_FEATURE_URL . '/HRM/Requests/assets/js/script.js', [ 'erp-script' ], ERP_PRO_PLUGIN_VERSION, true );
            } else if ( 'people' === $section && 'requests' === $sub_section ) {
                wp_enqueue_style( 'erp-pro-requests', ERP_PRO_FEATURE_URL . '/HRM/Requests/assets/css/styles.css', [], ERP_PRO_PLUGIN_VERSION );
            }
        }

        wp_localize_script( 'erp-pro-requests', 'erpProReq', [
            'cancel'   => __( 'No, Thanks', 'erp-pro' ),
            'withdraw' => __( 'Yes, Withdraw', 'erp-pro' ),
            'delete'   => __( 'Yes, Delete', 'erp-pro' ),
            'update'   => __( 'Update', 'erp-pro' ),
            'resign'   => __( 'Resign', 'erp-pro' ),
            'submit'   => __( 'Submit', 'erp-pro' ),
        ] );
    }

    /**
     * Loads templates
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function admin_templates() {
        $page        = isset( $_GET['page'] )        ? sanitize_text_field( wp_unslash( $_GET['page'] ) )        : '';
        $section     = isset( $_GET['section'] )     ? sanitize_text_field( wp_unslash( $_GET['section'] ) )     : 'dashboard';
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : '';

        if ( 'erp-hr' === $page ) {
            if ( 'my-profile' === $section ) {
                erp_get_js_template( ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/resign-request.php', 'erp-employee-resign' );
                erp_get_js_template( ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/remote-work-request.php', 'erp-employee-remote-work-request' );
            } else if ( 'dashboard' === $section ) {
                erp_get_js_template( ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/remote-work-request.php', 'erp-employee-remote-work-request' );
            } else if ( 'people' === $section && 'requests' === $sub_section ) {
                erp_get_js_template( ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/request-single.php', 'erp-employee-resquest' );
            }
        }
    }
}