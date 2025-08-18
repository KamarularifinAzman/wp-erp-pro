<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Org_Chart;

use WeDevs\ERP\Framework\Traits\Hooker;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main class for employee organogram
 *
 * @since 1.2.1
 */
final class Org_Chart {

    use Hooker;

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.2.1
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
     * @since 1.2.1
     */
    private function __construct() {
        $this->init_classes();
        $this->load_assets();
        $this->hooks();
    }

    /**
     * Adds required hooks
     *
     * @since 1.2.1
     *
     * @return void
     */
    public function load_assets() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
    }

    /**
     * Instantiates required classes
     *
     * @since 1.2.1
     *
     * @return void
     */
    public function init_classes() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Ajax();
        }
    }

    /**
     * Adds necessary hooks
     *
     * @since 1.2.1
     *
     * @return void
     */
    public function hooks() {
        $this->filter( 'erp_hr_people_menu_items', 'add_org_chart_section' );
        $this->action( 'erp_hr_org-chart_page', 'org_chart_page' );
    }

    /**
     * Registers Org chart section in people submenu
     *
     * @since 1.2.1
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_org_chart_section( $sections ) {
      // Only show to users with HR Manager capability

        	if ( ! current_user_can( 'erp_hr_manager' ) ) {

            		return $sections;

         	}
        $index = array_search( 'announcement', array_keys( $sections ) );

        if ( false === $index ) {
            $index = count( $sections );
        }

        $sections = array_slice( $sections, 0, $index ) + [
            'org-chart' => [
                'title' => esc_html__( 'Org Chart', 'erp-pro' ),
                'cap'   => 'erp_hr_manager' //more secure
            ],
        ] + array_slice( $sections, $index );

		return $sections;
	}

    /**
     * Renders org chart page
     *
     * @since 1.2.1
     *
     * @return string
     */
    public function org_chart_page() {
        erp_verify_page_access_permission( 'erp_hr_manager' );

        $departments = Helpers::get_dept_dropdown_raw();

        return require_once ERP_PRO_FEATURE_DIR . '/HRM/Org_Chart/views/org-chart.php';
    }

    /**
     * Enqueues necessary scripts
     *
     * @since 1.2.1
     *
     * @param string $hook_suffix
     *
     * @return void
     */
    public function admin_scripts( $hook_suffix ) {
        $section     = isset( $_GET['section'] )     ? sanitize_text_field( wp_unslash( $_GET['section'] ) )     : '';
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : '';

        if ( 'wp-erp_page_erp-hr' === $hook_suffix && 'people' === $section && 'org-chart' === $sub_section ) {
            wp_enqueue_style( 'erp-hr-org-chart-library', ERP_PRO_PLUGIN_ASSEST . '/vendor/orgchart/jquery.orgchart.min.css', [], ERP_PRO_PLUGIN_VERSION );
            wp_enqueue_script( 'erp-hr-org-chart-library', ERP_PRO_PLUGIN_ASSEST . '/vendor/orgchart/jquery.orgchart.min.js', [ 'erp-script' ], ERP_PRO_PLUGIN_VERSION, true );
            wp_enqueue_style( 'erp-pro-org-chart', ERP_PRO_FEATURE_URL . '/HRM/Org_Chart/assets/css/styles.css', [], ERP_PRO_PLUGIN_VERSION );
            wp_enqueue_script( 'erp-pro-org-chart', ERP_PRO_FEATURE_URL . '/HRM/Org_Chart/assets/js/script.js', [ 'erp-script' ], ERP_PRO_PLUGIN_VERSION, true );
        }

        wp_localize_script( 'erp-pro-org-chart', 'erpOrgChart', [
            'nonce'   => wp_create_nonce( 'erp-hr-org-chart' )
        ] );
    }
}
