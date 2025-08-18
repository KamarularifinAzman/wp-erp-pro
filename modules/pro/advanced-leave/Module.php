<?php
namespace WeDevs\ERP_PRO\PRO\AdvancedLeave;

// don't call the file directly
use WeDevs\AdvancedLeave\Accrual\Accrual;
use WeDevs\AdvancedLeave\Accrual\AccrualBgProcess;
use WeDevs\AdvancedLeave\Forward\Forward;
use WeDevs\AdvancedLeave\Forward\LeaveCarryForwardBgProcess;
use WeDevs\AdvancedLeave\Halfday\Halfday;
use WeDevs\AdvancedLeave\Multilevel\Multilevel;
use WeDevs\AdvancedLeave\Segregation\Segregation;
use WeDevs\AdvancedLeave\Unpaid\Unpaid;

if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Leave Class
 */
final class Module {

    /**
     * Add-on Version
     *
     * @since 1.0.0
     * @var  string
     */
    public $version = '1.1.0';

    /**
     * @var
     *
     * @since 1.0.0
     */
    private static $instance = null;


    /**
     * Get instance
     *
     * @since 1.0.0
     *
     * @return object
     */
    public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Private constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct() {
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_advanced_leave', array( $this, 'activate' ) );

        add_action( 'erp_hrm_loaded', array( $this, 'plugin_init' ) );
    }

    /**
     * On activation callback
     * @since 1.0.0
     */
    public function activate() {
        // nothing added here
    }

    /**
     * Initialize plugin
     *
     * @since 1.0.0
     */
    public function plugin_init() {
        // load files
        $this->include_files();

        // Initialize the action hooks
        $this->init_actions();

        // Initialize the filter hooks
        $this->init_filters();
    }

    /**
     * include files
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function include_files() {
        require_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/common.php';
        new Accrual();
        new Forward();
        new Halfday();
        new Multilevel();
        new Unpaid();
        new Segregation();
        new AccrualBgProcess();
        new LeaveCarryForwardBgProcess();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    public function init_actions() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Initialize filters
     *
     * @since 1.0.0
     */
    public function init_filters() {
        add_filter( 'erp_settings_hr_leave_section_fields', array( $this, 'leave_settings_fields' ) );
    }

    /**
     * enqueue scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'pro-leave-css',
            ERP_PRO_MODULE_URL . '/pro/advanced-leave/assets/css/leave.css',
            array(),
            ERP_PRO_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'pro-leave-js',
            ERP_PRO_MODULE_URL . '/pro/advanced-leave/assets/js/leave.js',
            array(),
            ERP_PRO_PLUGIN_VERSION,
            true
        );

        wp_localize_script( 'pro-leave-js', 'wpErpPro', array(
            'nonce'                => wp_create_nonce('erp-pro-nonce'),
            'export'               => esc_html__( 'Export', 'erp-pro' ),
            'calculate'            => esc_html__( 'Calculate', 'erp-pro' ),
            'calculate_title'      => esc_html__( 'Calculate Unpaid Leaves', 'erp-pro' ),
            'process'              => esc_html__( 'Process', 'erp-pro' ),
            'forward_modal_title'  => esc_html__( 'Process Forward', 'erp-pro' ),
            'forward_confirmation' => esc_html__( 'Please be careful, you can\'t undo this action.', 'erp-pro' ),
            'select_employee'      => esc_html__( 'Please select an employee.', 'erp-pro' ),
            'segregation_policy_error'    => esc_html__( 'Segregation value needs to be smaller than the policy value.', 'erp-pro'),
            'segregation_negative_error'    => esc_html__( 'Segregation value can\'t be a negative number.', 'erp-pro'),
            'req_forward_table'    => array(
                'approved_by'    => esc_html__( 'Approved By', 'erp-pro' ),
                'date'           => esc_html__( 'Date', 'erp-pro' ),
                'forward_status' => esc_html__( 'Forward Status', 'erp-pro' ),
                'forward_to'     => esc_html__( 'Forward To', 'erp-pro' ),
                'reason'         => esc_html__( 'Reason', 'erp-pro' ),
            ),
        ) );
    }

    /**
     * leave settings fields
     *
     * @since 1.0.0
     *
     * @param $fields array
     *
     * @return array
     */
    public function leave_settings_fields($fields) {
        $fields['leave'][] = [
            'title' => esc_html__( 'Enable Sandwich Rule', 'erp-pro' ),
            'type'  => 'checkbox',
            'id'    => 'erp_pro_sandwich_leave',
            'desc'  => esc_html__( 'Leave will be deducted for even weekly off days or holidays when in case applying for leaves on previous day to it and post that day.', 'erp-pro' )
        ];

        return $fields;
    }
}
