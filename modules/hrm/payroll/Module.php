<?php
namespace WeDevs\ERP_PRO\HRM\Payroll;

// don't call the file directly
use WeDevs\Payroll\CLI;
use WeDevs\Payroll\Installer;
use WeDevs\Payroll\Admin\SetupWizard;
use WeDevs\Payroll\Updates;
use WeDevs\Payroll\Settings;
use WeDevs\Payroll\AdminMenu;
use WeDevs\Payroll\AjaxHandler;
use WeDevs\Payroll\FormHandler;
use WeDevs\Payroll\Emails\Emailer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Main class
 */
class Module {

    /**
     * plugin version
     *
     * @var string
     */
    public $version = '2.1.0';

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function __construct() {

        $this->define_constants();

        new Installer();

        add_action( 'erp_hrm_loaded', array(
            $this,
            'accounting_module_loaded'
        ) );
    }

    /**
     * Check accounting module loaded
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function accounting_module_loaded() {
        add_action( 'erp_accounting_loaded', array( $this, 'erp_hrm_loaded_hook' ) );
    }

    /**
     * Initialize necessary functions after hrm loading
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function erp_hrm_loaded_hook() {

        $this->includes();

        $this->init_classes();

        $this->actions();
    }

    /**
     * Define this plugin constant
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPERP_PAYROLL_VERSION', $this->version );
        define( 'WPERP_PAYROLL_FILE', __FILE__ );
        define( 'WPERP_PAYROLL_PATH', dirname( WPERP_PAYROLL_FILE ) );
        define( 'WPERP_PAYROLL_INCLUDES', WPERP_PAYROLL_PATH . '/includes' );
        define( 'WPERP_PAYROLL_URL', plugins_url( '', WPERP_PAYROLL_FILE ) );
        define( 'WPERP_PAYROLL_ASSETS', WPERP_PAYROLL_URL . '/assets' );
        define( 'WPERP_PAYROLL_VIEWS', WPERP_PAYROLL_INCLUDES . '/Admin/views' );
        define( 'WPERP_PAYROLL_JS_TMPL', WPERP_PAYROLL_VIEWS . '/js-templates' );
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
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
     * Include necessary files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        require_once WPERP_PAYROLL_INCLUDES . '/functions-payroll.php';

        if ( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }

        if ( ! empty( $_GET['page'] ) ) {
            if ( 'erp-payroll-setup' == sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
                new SetupWizard();
            }
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            new CLI();
        }
    }

    /**
     * Init classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_classes() {
        new AdminMenu();
        new AjaxHandler();
        new FormHandler();
        new Updates();
        new Emailer();
        Settings::init();
    }

    /**
     * Actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function actions() {

        add_action( 'admin_init', [ $this, 'admin_redirect' ] );
        add_action( 'admin_footer', [ $this, 'admin_payroll_js_templates' ] );
        add_action( 'erp_hr_employee_single_tabs', [ $this, 'create_payroll_tab' ] );

        // For dashboard widget
        add_action( 'erp_hr_dashboard_widgets_right', [ $this, 'widget_employee_payslip' ] );

        // Send payslip email notification
        add_action( 'after_approve_payrun', [ $this, 'queue_list_payslip' ] );
        add_action( 'erp_per_minute_scheduled_events', [ $this, 'send_queue_list_payslip_email' ] );

        // Action hook to add verview widget
        add_action( 'erp_payroll_overview_widget_left', 'erp_payroll_overview_widget_left_callback' );

        /**
         * Action hook to remove employee from payroll if status becomes inactive
         *
         * @since 2.0.0
         */
        add_action( 'erp_hr_employee_after_update_status', 'erp_payroll_remove_employee_from_payroll', 10, 3 );
    }

    /**
     * Creating payroll tab
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function create_payroll_tab($tabs) {

        /***** For showing employee payslip start ******/
        if ( current_user_can( 'erp_hr_manager' ) ||
            ( isset( $_GET['id'] ) && intval( $_GET['id'] ) == get_current_user_id() ||
                ( isset( $_GET['section'] ) && sanitize_text_field( wp_unslash( $_GET['section'] ) ) == 'my-profile' )
            ) ) {
            $tabs['payslip'] = [
                'title' => __('Payslip', 'erp-pro'),
                'callback' => [$this, 'erp_hr_employee_payslip_tab']
            ];
        }
        /***** For showing employee payslip start ******/

        if ( current_user_can( 'erp_hr_manager' ) ) {
            $tabs['payroll'] = [
                'title' => __( 'Payroll', 'erp-pro' ),
                'callback' => [ $this, 'erp_hr_employee_payroll_tab' ]
            ];
            return $tabs;
        }
        return $tabs;
    }

    /**
     * Include employee payroll page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function erp_hr_employee_payroll_tab() {
        require_once WPERP_PAYROLL_VIEWS . '/payroll-emp-settings.php';
    }

    /**
     * Include employee payslip page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function erp_hr_employee_payslip_tab() {
        require_once WPERP_PAYROLL_VIEWS . '/payroll-emp-payslip.php';
    }

    /**
     * Include settings page
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function erp_hr_payroll_add_settings_page( $settings = array() ) {
        $settings[] = include __DIR__ . '/includes/class-settings.php';
        return $settings;
    }

    /**
    * Handle redirects to setup/welcome page after install and updates.
    *
    * @since 1.0.0
    *
    * @return void
    */
    public function admin_redirect() {
        if ( apply_filters( 'erp_enable_setup_wizard', true ) ) {
            set_transient( '_erp_payroll_activation_redirect', 1, 30 );
        }

        if ( ! get_transient( '_erp_payroll_activation_redirect' ) ) {
            return;
        }

        delete_transient( '_erp_payroll_activation_redirect' );

        if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'erp-payroll-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'erp_hr_manager' ) ) {
            return;
        }

        // If it's the first time
        if ( get_option( 'erp_payroll_setup_wizard_ran' ) != '1' ) {
            wp_safe_redirect( add_query_arg( ['page' => 'erp-payroll-setup'], admin_url( 'index.php' ) ) );
            exit;
        }
    }

    /**
     * Print JS templates in footer
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_payroll_js_templates_old() {
        global $current_screen;

        switch ( $current_screen->base ) {
            case 'payroll_page_erp-hr-payroll-payitem-settings':
                erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-template.php', 'erp-payroll-item-template' );
                erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-new-template.php', 'erp-payroll-item-new-template' );
                erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-category-template.php', 'erp-payroll-item-category-template' );
                erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-category-new-template.php', 'erp-payroll-item-category-new-template' );
                break;
            case 'payroll_page_erp-hr-payroll-pay-run':
                erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/employee-filter-template.php', 'erp-payroll-employee-filter-template' );
                break;
            case 'hr-management_page_erp-hr-employee':
                //erp_get_vue_component_template( WPERP_PAYROLL_JS_TMPL . '/payitem-utility-template.php', 'erp-payroll-payitem-utility-template' );
                break;
            default:
                # code...
                break;
        }

    }

    /**
     * Print JS templates in footer
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function admin_payroll_js_templates() {

        if ( version_compare( WPERP_VERSION, "1.4.0", '<' ) ) {
            $this->admin_payroll_js_templates_old();
            return;
        }

        $is_hr_page = ( !empty( $_GET['page'] ) && ( 'erp-hr' == $_GET['page'] ) ) ? true : false;
        $is_settings_page = ( !empty( $_GET['page'] ) && ( 'erp-settings' == $_GET['page'] ) ) ? true : false;

        //bail out if not HR page or Settings page
        if ( !$is_hr_page && !$is_settings_page ) {
            return;
        }
        $is_payroll_page = ( !empty( $_GET['section'] ) && ( 'payroll' == $_GET['section'] ) ) ? true : false;
        $is_payroll_tab  = ( !empty( $_GET['tab'] ) && ( 'payroll' == $_GET['tab'] ) ) ? true : false;

        //bail out if not payroll tab
        if ( !$is_payroll_page && !$is_payroll_tab ) {
            return;
        }

        //Load settings JS
        if ( $is_payroll_tab || $is_payroll_page ) {
            erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-template.php', 'erp-payroll-item-template' );
            erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-new-template.php', 'erp-payroll-item-new-template' );
            erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-category-template.php', 'erp-payroll-item-category-template' );
            erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/pay-item-category-new-template.php', 'erp-payroll-item-category-new-template' );
        }

        $sub_section = !empty( $_GET['sub-section'] ) ? $_GET['sub-section'] : 'dashboard';

        switch ( $sub_section ) {
            case 'payrun':
                erp_get_js_template( WPERP_PAYROLL_JS_TMPL . '/employee-filter-template.php', 'erp-payroll-employee-filter-template' );
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Load payslip widget to dashboard
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function widget_employee_payslip() {
        erp_admin_dash_metabox( __( '<i class="fa fa-money"></i> Payslip', 'erp-pro' ), [ $this, 'widget_employee_payslip_view' ] );
    }

    /**
     * Load payslip widget to dashboard view
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function widget_employee_payslip_view() {
        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $currency_symbol = erp_acct_get_currency_symbol();
        } else {
            $currency_symbol = erp_ac_get_currency_symbol();
        }

        $localize_scripts = [
            'nonce'            => wp_create_nonce( 'payroll_nonce' ),
            'currency_symbol'  => $currency_symbol,
            'admin_url'        => admin_url( 'admin.php' ),
            'ajaxurl'          => admin_url( 'admin-ajax.php' ),
        ];

        wp_register_script( 'erp-app-payitem-widget', WPERP_PAYROLL_ASSETS . '/js/app-payrun-payslips.js', array( 'jquery' ), false, true );
        wp_localize_script( 'erp-app-payitem-widget', 'wpErpPayroll', $localize_scripts );
        wp_enqueue_script( 'erp-app-payitem-widget' );
        wp_enqueue_style( 'erp-payroll-style', WPERP_PAYROLL_ASSETS . '/css/stylesheet.css' );
        include WPERP_PAYROLL_VIEWS . '/dashboard-payslip-widget.php';
    }

    /**
     * Save payslip employees to the queue
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function queue_list_payslip( $payrun_id ) {
        global $wpdb;

        $payslip_queue_list = get_option( 'payslip_queue_list', [] );

        $sql = "
            SELECT
              empid
            FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
            WHERE payrun_id = {$payrun_id}
            GROUP BY empid
        ";
        $results = $wpdb->get_results( $sql );

        if ( count( $results ) > 0 ) {
            foreach ( $results as $result ) {
                $payslip_queue_list[] = array(
                    'payrun_id' => $payrun_id,
                    'emp_id'    => $result->empid
                );
            }

            update_option( 'payslip_queue_list', $payslip_queue_list );
        }
    }

    /**
     * Send email by payslip employees to the queue
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function send_queue_list_payslip_email() {

        $limit = 10;
        $payslip_queue_list = get_option( 'payslip_queue_list', [] );
        foreach ( $payslip_queue_list as $pql_key => $pql_value ) {
            send_queue_list_payslip_email_by_prid( $pql_value );
            unset( $payslip_queue_list[$pql_key] );
            if( $pql_key == $limit ){
                break;
            }
        }
        $payslip_queue_list = array_values( $payslip_queue_list );
        update_option( 'payslip_queue_list', $payslip_queue_list );
    }
} // Base_Plugin
