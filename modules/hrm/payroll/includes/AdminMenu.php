<?php

namespace WeDevs\Payroll;


class AdminMenu {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function __construct() {
        add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 2 );
        add_action( 'admin_menu', [ $this, 'admin_menu' ], 11 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_scripts'] );
        add_action( 'erp_hr_employee_single_bottom', [ $this, 'frontend_enqueue_scripts'] );
    }

    /**
     * Setting screen option.
     *
     * @param string $status, $option, $value
     *
     * @since 1.0.0
     *
     * @return string
     */
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    /**
     * Register the admin menu.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_menu() {
        /* payroll menu */
        $capability = 'erp_hr_manager';

        if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {
            $this->load_new_menu();
            return;
        }

        add_menu_page( __( 'Payroll', 'erp-pro' ), __( 'Payroll', 'erp-pro' ), $capability, 'erp-hr-payroll', array(
            $this,
            'dashboard_page'
        ), 'dashicons-money' );
        add_submenu_page( 'erp-hr-payroll', __( 'Overview', 'erp-pro' ), __( 'Overview', 'erp-pro' ), $capability, 'erp-hr-payroll', array(
            $this,
            'dashboard_page'
        ) );
        add_submenu_page( 'erp-hr-payroll', __( 'Pay Calendar', 'erp-pro' ), __( 'Pay Calendar', 'erp-pro' ), $capability, 'erp-hr-payroll-pay-calendar', array(
            $this,
            'pay_calendar_page'
        ) );
        add_submenu_page( 'erp-hr-payroll', __( 'Pay Run List', 'erp-pro' ), __( 'Pay Run List', 'erp-pro' ), $capability, 'erp-hr-payroll-pay-run', array(
            $this,
            'pay_run_page'
        ) );
        add_submenu_page( 'erp-hr-payroll', __( 'Reports', 'erp-pro' ), __( 'Reports', 'erp-pro' ), $capability, 'erp-hr-payroll-reports', array(
            $this,
            'reports_page'
        ) );
        add_submenu_page( 'erp-hr-payroll', __( 'Settings', 'erp-pro' ), __( 'Settings', 'erp-pro' ), $capability, 'admin.php?page=erp-settings&tab=erp-hr&section=payroll' );
    }

    /**
     * Load new menu
     */
    public function load_new_menu() {
        $capability = 'erp_hr_manager';

        erp_add_menu( 'hr', [
            'title'      => __( 'Payroll', 'erp-pro' ),
            'capability' => $capability,
            'slug'       => 'payroll',
            'callback'   => [ $this, 'dashboard_page' ],
            'position'   => 11
        ] );

        erp_add_submenu( 'hr','payroll', [
            'title'      => __( 'Dashboard', 'erp-pro' ),
            'capability' => $capability,
            'slug'       => 'dashboard',
            'callback'   => [ $this, 'dashboard_page' ],
            'position'   => 1
        ] );

        erp_add_submenu( 'hr','payroll', [
            'title'      =>  __( 'Pay Calendar', 'erp-pro' ),
            'capability' => $capability,
            'slug'       => 'calendar',
            'callback'   => [ $this, 'pay_calendar_page' ],
            'position'   => 5
        ] );

        erp_add_submenu( 'hr','payroll', [
            'title'      =>  __( 'Pay Run List', 'erp-pro' ),
            'capability' => $capability,
            'slug'       => 'payrun',
            'callback'   => [ $this, 'pay_run_page' ],
            'position'   => 10
        ] );

        erp_add_submenu( 'hr','payroll', [
            'title'      =>  __( 'Bulk pay item edit', 'erp-pro' ),
            'capability' => $capability,
            'slug'       => 'bulk-pay-item-edit',
            'callback'   => [ $this, 'bulk_edit_item' ],
            'position'   => 11
        ] );

        erp_add_submenu( 'hr','payroll', [
            'title'      =>  __( 'Reports', 'erp-pro' ),
            'capability' => $capability,
            'slug'       => 'reports',
            'callback'   => [ $this, 'reports_page' ],
            'position'   => 15
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'       => __( 'Settings', 'erp-pro' ),
            'capability'  => $capability,
            'direct_link' => admin_url('admin.php?page=erp-settings#/erp-hr/payroll'),
            'slug'        => 'settings',
            'callback'    => '',
            'position'    => 20
        ] );

    }

    /**
     * Show dashboard template page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function dashboard_page() {
        require_once WPERP_PAYROLL_VIEWS . '/payrun-overview.php';
    }

    /**
     * Show pay item settings page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function pay_calendar_page() {
        $action = isset( $_GET['subpage'] ) ? $_GET['subpage'] : '';
        $template = '';

        switch ( $action ) {
            case 'add-cal-form':
                $template = WPERP_PAYROLL_VIEWS . '/pay-calendar-creation-form.php';
                break;
            default:
                $template = WPERP_PAYROLL_VIEWS . '/pay-calendar.php';
                break;
        }

        require_once $template;
    }

    /**
     * Show salary inner part settings page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function pay_run_page() {
        $action   = isset($_GET['tab']) ? $_GET['tab'] : '';
        $template = '';

        switch ( $action ) {
            case 'employees':
                $template = WPERP_PAYROLL_VIEWS . '/tab-templates/employees-tab.php';
                break;
            case 'variable_input':
                $template = WPERP_PAYROLL_VIEWS . '/tab-templates/variable-input-tab.php';
                break;
            case 'payslips':
                $template = WPERP_PAYROLL_VIEWS . '/tab-templates/payslips-tab.php';
                break;
            case 'approve':
                $template = WPERP_PAYROLL_VIEWS . '/tab-templates/approve-tab.php';
                break;
            default:
                $template = WPERP_PAYROLL_VIEWS . '/payrun.php';
                break;
        }

        require_once $template;
    }

    /**
     * Show report template page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function reports_page() {
        $type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';

        switch ( $type ) {
            case 'payrun-employee':
                $template = WPERP_PAYROLL_VIEWS . '/reports/employee-report.php';
                break;
            case 'payrun-summary':
                $template = WPERP_PAYROLL_VIEWS . '/reports/summary-report.php';
                break;
            default:
                $template = WPERP_PAYROLL_VIEWS . '/reports.php';
                break;
        }

        include $template;
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

        if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {
            $this->new_enqueue_scripts();
            return;
        }

        $menu_titles = [
            'payroll'       => __( 'Payroll' ),
            'hr-management' => __( 'HR Management' ),
            'erp-settings'  => __( 'ERP Settings' ),
        ];

        foreach ( $menu_titles as $slug => $title ) {
            $sanitize_title = sanitize_title( $title );
            if ( strpos( $hook, $sanitize_title ) >= 0 ) {
                $hook = str_replace( $sanitize_title , $slug, $hook );
            }
        }

        $payroll_pages = [
            'toplevel_page_erp-hr-payroll',
            'payroll_page_erp-hr-payroll-pay-calendar',
            'payroll_page_erp-hr-payroll-pay-run',
            'payroll_page_erp-hr-payroll-reports',
            'hr-management_page_erp-hr-employee',
            'hr-management_page_erp-hr-my-profile',
            'erp-settings_page_erp-settings'
        ];

        if ( ! in_array( $hook, $payroll_pages ) ) {
            return;
        }
        /**
         * All styles goes here
         */
        wp_enqueue_style( 'erp-payroll-style', WPERP_PAYROLL_ASSETS . '/css/stylesheet.css' );
        wp_enqueue_style( 'erp-payroll-multiselect-style', WPERP_PAYROLL_ASSETS . '/css/multiselect.css' );
        wp_enqueue_style( 'erp-timepicker' );
        wp_enqueue_style( 'erp-fullcalendar' );
        wp_enqueue_style( 'erp-sweetalert' );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'erp-vuejs' );
        wp_enqueue_script( 'erp-vue-multiselect', WPERP_PAYROLL_ASSETS . '/js/vue-multiselect.js', array(), false, true );

        wp_enqueue_script( 'chart-js', WPERP_PAYROLL_ASSETS . '/js/Chartjs.js', array('jquery'), false, true );
        wp_enqueue_script( 'modal-js', WPERP_PAYROLL_ASSETS . '/js/modal.js', array('jquery'), false, true );

        wp_enqueue_script( 'erp-payroll-script', WPERP_PAYROLL_ASSETS . '/js/erp-payroll.js', array('jquery'), false, true );

        wp_enqueue_script( 'erp-payroll-overview', WPERP_PAYROLL_ASSETS . '/js/app-payroll-overview.js', array('jquery','erp-app-payitem'), false, true );
        wp_enqueue_script( 'erp-app-pay-basic-info', WPERP_PAYROLL_ASSETS . '/js/app-basic-payroll-info.js', array('jquery','erp-app-payitem'), false, true );
        wp_enqueue_script( 'erp-app-pay-run-reports', WPERP_PAYROLL_ASSETS . '/js/app-payroll-reports.js', array('jquery','erp-app-payitem'), false, true );

        wp_enqueue_script( 'erp-app-payitem', WPERP_PAYROLL_ASSETS . '/js/app-pay-calendar.js', array('jquery'), false, true );

        wp_enqueue_script( 'erp-app-payrun-employees', WPERP_PAYROLL_ASSETS . '/js/app-payrun-employees.js', array('jquery', 'erp-app-payitem'), false, true );
        wp_enqueue_script( 'erp-app-payrun-variable-input', WPERP_PAYROLL_ASSETS . '/js/app-payrun-variable-input.js', array('jquery','erp-app-payitem','erp-vue-multiselect'), false, true );
        wp_enqueue_script( 'erp-app-payrun-payslips', WPERP_PAYROLL_ASSETS . '/js/app-payrun-payslips.js', array('jquery'), false, true );
        wp_enqueue_script( 'erp-app-payrun-approved', WPERP_PAYROLL_ASSETS . '/js/app-payrun-approved.js', array('jquery'), false, true );
        wp_enqueue_script( 'erp-app-payrun-list', WPERP_PAYROLL_ASSETS . '/js/app-payrun-list.js', array('jquery'), false, true );
        wp_enqueue_script( 'erp-app-pay-calendar-add-edit', WPERP_PAYROLL_ASSETS . '/js/app-pay-calendar-add-edit.js', array('jquery'), false, true );

        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'erp-timepicker' );
        wp_enqueue_script( 'erp-fullcalendar' );
        wp_enqueue_script( 'erp-sweetalert' );

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $currency_symbol = erp_acct_get_currency_symbol();
        } else {
            $currency_symbol = erp_ac_get_currency_symbol();
        }

        $localize_scripts = [
            'nonce'                          => wp_create_nonce( 'payroll_nonce' ),
            'payitem_message'                => [
                'payitem_status_applicable'     => __( 'Applicable', 'erp-pro' ),
                'payitem_status_not_applicable' => __( 'Not Applicable', 'erp-pro' ),
            ],
            'currency_symbol'                => $currency_symbol,
            'popup'                          => [
                'payitemcategory_popup'      => [
                    'title'          => __( 'Edit pay item category', 'erp-pro' ),
                    'submit'         => __( 'Submit', 'erp-pro' ),
                    'del_confirm'    => __( 'Are you sure you want to delete this pay item category?', 'erp-pro' ),
                    'prompt_message' => __( 'Please enter pay item category', 'erp-pro' )
                ],
                'payitem_popup'               => [
                    'add_new_title'         => __( 'Add new pay item', 'erp-pro' ),
                    'title'                 => __( 'Edit pay item', 'erp-pro' ),
                    'submit'                => __( 'Submit', 'erp-pro' ),
                    'del_confirm'           => __( 'Are you sure you want to delete this pay item?', 'erp-pro' ),
                    'status_change_confirm' => __( 'Are you sure you want to change this status?', 'erp-pro' ),
                    'prompt_message'        => __( 'Please enter pay item', 'erp-pro' )
                ],
                'salary_settings'                => [
                    'title'          => __( 'Edit salary settings', 'erp-pro' ),
                    'submit'         => __( 'Submit', 'erp-pro' ),
                    'del_confirm'    => __( 'Are you sure you want to delete this salary settings?', 'erp-pro' ),
                    'prompt_message' => __( 'Please enter pay item', 'erp-pro' )
                ],
                'employee_filter'                => [
                    'title'  => __( 'Filter employee', 'erp-pro' ),
                    'submit' => __( 'Filter', 'erp-pro' )
                ],
                'applied_employee_list'          => [
                    'title'  => __( 'Applied employee list', 'erp-pro' ),
                    'submit' => __( 'Close', 'erp-pro' )
                ],
                'approve_warning'                => __( 'Are you sure you want to approve these payments?', 'erp-pro' ),
                'delete_payrun_warning'          => __( 'Are you sure you want to remove this payrun?', 'erp-pro' ),
                'delete_single_approved_warning' => __( 'Are you sure you want to remove this employee information from approved list?', 'erp-pro' ),
                'delete_payrun_success'          => __( 'Payrun deleted successfully', 'erp-pro' ),
                'copy_warning'                   => __( 'Are you sure you want to copy this payrun to new payrun?', 'erp-pro' ),
                'payrun_approve_single_popup'    => [
                    'email_confirm'      => __( 'Are you sure you want to send this payslip to email?', 'erp-pro' ),
                    'email_sent_message' => __( 'Payslip sent successfully', 'erp-pro' )
                ],
                'payrun_approve_bulk_popup'      => [
                    'email_confirm'      => __( 'Are you sure you want to send email to below employee(s)?', 'erp-pro' ),
                    'batch_pay_confirm'  => __( 'Are you sure you want to proceed batch pay to below employees?', 'erp-pro' ),
                    'email_sent_message' => __( 'Payslip sent successfully', 'erp-pro' )
                ]
            ],
            'validation_message'             => [
                'empty_payitemcategory_name'           => __( 'Please provide pay item category name.', 'erp-pro' ),
                'empty_select_payitemcategory_name'    => __( 'Please select pay item category name.', 'erp-pro' ),
                'only_number_payitemcategory_name'     => __( 'Please provide only letters in pay item category name.', 'erp-pro' ),
                'empty_payitem_name'                   => __( 'Please provide pay item name.', 'erp-pro' ),
                'empty_department_name'                => __( 'Please select a department at-least.', 'erp-pro' ),
                'only_number_payitem_name'             => __( 'Please provide only letters in pay item name.', 'erp-pro' ),
                'percentage_or_amount_empty'           => __( 'Percentage value and amount value cannot be empty. Please provide percentage or amount value.', 'erp-pro' ),
                'percentage_or_amount_have_both_value' => __( 'You cannot provide percentage and amount value simultaneously. Please provide percentage or amount value.', 'erp-pro' ),
                'percentage_isnan_check'               => __( 'Please provide number value in percentage value.', 'erp-pro' ),
                'ordinary_rate_isnan_check'            => __( 'Please provide number value in ordinary rate.', 'erp-pro' ),
                'amount_isnan_check'                   => __( 'Please provide number value in amount value.', 'erp-pro' ),
                'filter_empty_message'                 => __( 'Please select a department or a designation at-least.', 'erp-pro' ),
                'empty_payment_method'                 => __( 'Please select payment method.', 'erp-pro' ),
                'empty_departmen_n_desig'              => __( 'Please select any department or designation to bring employee in the below list.', 'erp-pro' ),
                'empty_cal_type'                       => __( 'Please select calendar type.', 'erp-pro' ),
                'empty_calendar_name'                  => __( 'Please enter calendar name.', 'erp-pro' ),
                'empty_emp'                            => __( 'You did not select any employee.', 'erp-pro' ),
            ],
            'remove_confirm_pay_cal'         => __( 'Are you sure you want to remove this pay calendar?', 'erp-pro' ),
            'remove_confirmation'            => __( 'Are you sure you want to remove?', 'erp-pro' ),
            'create_confirm_pay_cal'         => __( 'Are you sure you want to create this pay calendar?', 'erp-pro' ),
            'update_confirm_pay_cal'         => __( 'Are you sure you want to update this pay calendar?', 'erp-pro' ),
            'confirm_payrun_msg'             => __( 'Are you sure you want to start a new pay run for your selected calendar?', 'erp-pro' ),
            'confirm_payrun_approve_msg'     => __( 'Are you sure you want to approve this pay run?', 'erp-pro' ),
            'confirm_payrun_undoapprove_msg' => __( 'Are you sure you want to undo this approved pay run?', 'erp-pro' ),
            'admin_url'                      => admin_url( 'admin.php' ),
            'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
            'pay_calendar_url'               => add_query_arg( 'page', 'erp-hr-payroll-pay-calendar', admin_url( 'admin.php' ) ),
            'addon_home_url'                 => add_query_arg( 'page', 'erp-hr-payroll', admin_url( 'admin.php' ) ),
            'approved_page_url'              => add_query_arg( [
                'page' => 'erp-hr-payroll-pay-run',
                'view' => 'approved_list'
            ], admin_url( 'admin.php' ) ),
            'approved_single_emp_page_url'   => add_query_arg( [
                'page' => 'erp-hr-payroll-pay-run',
                'view' => 'single_payslip'
            ], admin_url( 'admin.php' ) ),
            'payrun_page_url'                => add_query_arg( [
                'page' => 'erp-hr-payroll-pay-run',
                'view' => 'approved_list'
            ], admin_url( 'admin.php' ) ),
            'payrun_calendar_page_url'       => add_query_arg( [
                'page' => 'erp-hr-payroll-pay-calendar'
            ], admin_url( 'admin.php' ) ),
            'payrun_employees_page_url'       => add_query_arg( [
                'page' => 'erp-hr-payroll-pay-run',
                'tab' => 'employees'
            ], admin_url( 'admin.php' ) ),
            'payrun_url'                     => add_query_arg( [
                'page' => 'erp-hr-payroll-pay-run'
            ], admin_url( 'admin.php' ) ),
            'scriptDebug'                    => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false
        ];

        wp_localize_script( 'erp-app-payitem', 'wpErpPayroll', $localize_scripts );
    }

    /**
     * Load scripts new strcture
     */
    public function new_enqueue_scripts() {

        $is_hr_page = ( !empty( $_GET['page'] ) && ( 'erp-hr' == $_GET['page'] ) ) ? true : false;
        $is_settings_page = ( !empty( $_GET['page'] ) && ( 'erp-settings' == $_GET['page'] ) ) ? true : false;

        //bail out if not HR page or Settings page
        if ( !$is_hr_page && !$is_settings_page ) {
            return;
        }
        $is_payroll_page = ( !empty( $_GET['section'] ) && ( 'payroll' == $_GET['section'] ) ) ? true : false;
        $is_payroll_tab  = ( !empty( $_GET['tab'] ) && ( 'payroll' == $_GET['tab'] || 'payslip' == $_GET['tab'] ) ) ? true : false;

        //bail out if not payroll tab
        if ( !$is_payroll_page && !$is_payroll_tab ) {
            return;
        }

        $payroll_url = add_query_arg( [ 'page'  => 'erp-hr', 'section' => 'payroll' ], admin_url('admin.php') );

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'erp-payroll-style', WPERP_PAYROLL_ASSETS . '/css/stylesheet.css' );
        wp_enqueue_style( 'erp-payroll-multiselect-style', WPERP_PAYROLL_ASSETS . '/css/multiselect.css' );
        wp_enqueue_style( 'erp-timepicker' );
        wp_enqueue_style( 'erp-fullcalendar' );
        wp_enqueue_style( 'erp-sweetalert' );
        wp_enqueue_style( 'erp-daterangepicker' );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'erp-vuejs' );
        wp_enqueue_script( 'erp-vue-multiselect', WPERP_PAYROLL_ASSETS . '/js/vue-multiselect.js', array(), false, true );

        wp_enqueue_script( 'chart-js', WPERP_PAYROLL_ASSETS . '/js/Chartjs.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'modal-js', WPERP_PAYROLL_ASSETS . '/js/modal.js', array( 'jquery' ), false, true );

        wp_enqueue_script( 'erp-payroll-script', WPERP_PAYROLL_ASSETS . '/js/erp-payroll.js', array( 'jquery' ), false, true );

        wp_enqueue_script( 'erp-payroll-overview', WPERP_PAYROLL_ASSETS . '/js/app-payroll-overview.js', array( 'jquery', 'erp-app-payitem' ), false, true );
        wp_enqueue_script( 'erp-app-pay-basic-info', WPERP_PAYROLL_ASSETS . '/js/app-basic-payroll-info.js', array( 'jquery', 'erp-app-payitem' ), false, true );
        wp_enqueue_script( 'erp-app-pay-run-reports', WPERP_PAYROLL_ASSETS . '/js/app-payroll-reports.js', array( 'jquery', 'erp-app-payitem' ), false, true );

        wp_enqueue_script( 'erp-app-payitem', WPERP_PAYROLL_ASSETS . '/js/app-pay-calendar.js', array( 'jquery' ), false, true );

        wp_enqueue_script( 'erp-app-payrun-employees', WPERP_PAYROLL_ASSETS . '/js/app-payrun-employees.js', array( 'jquery', 'erp-app-payitem' ), false, true );
        wp_enqueue_script( 'erp-app-payrun-variable-input', WPERP_PAYROLL_ASSETS . '/js/app-payrun-variable-input.js', array( 'jquery', 'erp-app-payitem', 'erp-vue-multiselect' ), false, true );
        wp_enqueue_script( 'erp-app-payrun-payslips', WPERP_PAYROLL_ASSETS . '/js/app-payrun-payslips.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'erp-app-payrun-approved', WPERP_PAYROLL_ASSETS . '/js/app-payrun-approved.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'erp-app-payrun-list', WPERP_PAYROLL_ASSETS . '/js/app-payrun-list.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'erp-app-pay-calendar-add-edit', WPERP_PAYROLL_ASSETS . '/js/app-pay-calendar-add-edit.js', array( 'jquery' ), false, true );

        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'erp-timepicker' );
        wp_enqueue_script( 'erp-fullcalendar' );
        wp_enqueue_script( 'erp-sweetalert' );
        wp_enqueue_script( 'erp-daterangepicker' );

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $currency_symbol = erp_acct_get_currency_symbol();
        } else {
            $currency_symbol = erp_ac_get_currency_symbol();
        }

        $localize_scripts = [
            'nonce'                          => wp_create_nonce( 'payroll_nonce' ),
            'attendace_active'               => wp_erp_pro()->module->is_active( 'attendance' ),
            'payitem_message'                => [
                'payitem_status_applicable'     => __( 'Applicable', 'erp-pro' ),
                'payitem_status_not_applicable' => __( 'Not Applicable', 'erp-pro' ),
            ],
            'currency_symbol'                => $currency_symbol,
            'popup'                          => [
                'payitemcategory_popup'          => [
                    'title'          => __( 'Edit pay item category', 'erp-pro' ),
                    'submit'         => __( 'Submit', 'erp-pro' ),
                    'del_confirm'    => __( 'Are you sure you want to delete this pay item category?', 'erp-pro' ),
                    'prompt_message' => __( 'Please enter pay item category', 'erp-pro' )
                ],
                'payitem_popup'                  => [
                    'add_new_title'         => __( 'Add new pay item', 'erp-pro' ),
                    'title'                 => __( 'Edit pay item', 'erp-pro' ),
                    'submit'                => __( 'Submit', 'erp-pro' ),
                    'del_confirm'           => __( 'Are you sure you want to delete this pay item?', 'erp-pro' ),
                    'status_change_confirm' => __( 'Are you sure you want to change this status?', 'erp-pro' ),
                    'prompt_message'        => __( 'Please enter pay item', 'erp-pro' )
                ],
                'salary_settings'                => [
                    'title'          => __( 'Edit salary settings', 'erp-pro' ),
                    'submit'         => __( 'Submit', 'erp-pro' ),
                    'del_confirm'    => __( 'Are you sure you want to delete this salary settings?', 'erp-pro' ),
                    'prompt_message' => __( 'Please enter pay item', 'erp-pro' )
                ],
                'employee_filter'                => [
                    'title'  => __( 'Filter employee', 'erp-pro' ),
                    'submit' => __( 'Filter', 'erp-pro' )
                ],
                'applied_employee_list'          => [
                    'title'  => __( 'Applied employee list', 'erp-pro' ),
                    'submit' => __( 'Close', 'erp-pro' )
                ],
                'approve_warning'                => __( 'Are you sure you want to approve these payments?', 'erp-pro' ),
                'delete_payrun_warning'          => __( 'Are you sure you want to remove this payrun?', 'erp-pro' ),
                'delete_single_approved_warning' => __( 'Are you sure you want to remove this employee information from approved list?', 'erp-pro' ),
                'delete_payrun_success'          => __( 'Payrun deleted successfully', 'erp-pro' ),
                'copy_warning'                   => __( 'Are you sure you want to copy this payrun to new payrun?', 'erp-pro' ),
                'payrun_approve_single_popup'    => [
                    'email_confirm'      => __( 'Are you sure you want to send this payslip to email?', 'erp-pro' ),
                    'email_sent_message' => __( 'Payslip sent successfully', 'erp-pro' )
                ],
                'payrun_approve_bulk_popup'      => [
                    'email_confirm'      => __( 'Are you sure you want to send email to below employee(s)?', 'erp-pro' ),
                    'batch_pay_confirm'  => __( 'Are you sure you want to proceed batch pay to below employees?', 'erp-pro' ),
                    'email_sent_message' => __( 'Payslip sent successfully', 'erp-pro' )
                ]
            ],
            'validation_message'             => [
                'empty_payitemcategory_name'           => __( 'Please provide pay item category name.', 'erp-pro' ),
                'empty_select_payitemcategory_name'    => __( 'Please select pay item category name.', 'erp-pro' ),
                'only_number_payitemcategory_name'     => __( 'Please provide only letters in pay item category name.', 'erp-pro' ),
                'empty_payitem_name'                   => __( 'Please provide pay item name.', 'erp-pro' ),
                'empty_department_name'                => __( 'Please select a department at-least.', 'erp-pro' ),
                'only_number_payitem_name'             => __( 'Please provide only letters in pay item name.', 'erp-pro' ),
                'percentage_or_amount_empty'           => __( 'Percentage value and amount value cannot be empty. Please provide percentage or amount value.', 'erp-pro' ),
                'percentage_or_amount_have_both_value' => __( 'You cannot provide percentage and amount value simultaneously. Please provide percentage or amount value.', 'erp-pro' ),
                'percentage_isnan_check'               => __( 'Please provide number value in percentage value.', 'erp-pro' ),
                'ordinary_rate_isnan_check'            => __( 'Please provide number value in ordinary rate.', 'erp-pro' ),
                'amount_isnan_check'                   => __( 'Please provide number value in amount value.', 'erp-pro' ),
                'filter_empty_message'                 => __( 'Please select a department or a designation at-least.', 'erp-pro' ),
                'empty_payment_method'                 => __( 'Please select payment method.', 'erp-pro' ),
                'empty_departmen_n_desig'              => __( 'Please select any department or designation to bring employee in the below list.', 'erp-pro' ),
                'empty_cal_type'                       => __( 'Please select calendar type.', 'erp-pro' ),
                'empty_calendar_name'                  => __( 'Please enter calendar name.', 'erp-pro' ),
                'empty_emp'                            => __( 'You did not select any employee.', 'erp-pro' ),
                'attendance_required'                  => __( 'Attendance extension is required for Hourly Calender. Please activate the Attendance extension first or choose another calender type.', 'erp-pro' ),
                'atts_input_required'                  => __( 'Both pay rate and date range are required for attendance based payments', 'erp-pro' ),
                'date_range'                           => [
                    'hourly'   => __( 'Date range difference should be 1 to 31 days', 'erp-pro' ),
                    'weekly'   => __( 'Date range difference should be 7 to 9 days', 'erp-pro' ),
                    'biweekly' => __( 'Date range difference should be 14 to 17 days', 'erp-pro' ),
                    'monthly'  => __( 'Date range difference should be 28 to 31 days', 'erp-pro' ),
                ],
                'pay_date'                             => __( 'Payment date cannot be less than from date, Please fix your payment date!', 'erp-pro' ),
                'null_pay_date'                        => __( 'Payment date cannot be empty!', 'erp-pro' ),
                'null_from_date'                       => __( 'From date cannot be empty!', 'erp-pro' ),
                'null_to_date'                         => __( 'To date cannot be empty!', 'erp-pro' ),
                'cal_type_exist'                       => __( 'This type of caledar already exists! Please choose another one.', 'erp-pro' ),
            ],
            'remove_confirm_pay_cal'         => __( 'Are you sure you want to remove this pay calendar?', 'erp-pro' ),
            'remove_confirmation'            => __( 'Are you sure you want to remove?', 'erp-pro' ),
            'create_confirm_pay_cal'         => __( 'Are you sure you want to create this pay calendar?', 'erp-pro' ),
            'update_confirm_pay_cal'         => __( 'Are you sure you want to update this pay calendar?', 'erp-pro' ),
            'confirm_payrun_msg'             => __( 'Are you sure you want to start a new pay run for your selected calendar?', 'erp-pro' ),
            'confirm_payrun_approve_msg'     => __( 'Are you sure you want to approve this pay run?', 'erp-pro' ),
            'confirm_payrun_undoapprove_msg' => __( 'Are you sure you want to undo this approved pay run?', 'erp-pro' ),
            'success'                        => __( 'Success', 'erp-pro' ),
            'oops'                           => __( 'Oops', 'erp-pro' ),
            'unable_to_revert'               => __( "You won't be able to revert this!", 'erp-pro' ),
            'select_date_range'              => __( "Please select date range!", 'erp-pro' ),
            'admin_url'                      => admin_url( 'admin.php' ),
            'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
            'pay_calendar_url'               => add_query_arg( 'sub-section', 'calendar', $payroll_url ),
            'addon_home_url'                 => $payroll_url,
            'approved'                       => __( 'Approved', 'erp-pro' ),
            'not_approved'                   => __( 'Not Approved', 'erp-pro' ),
            'approved_page_url'              => add_query_arg( [
                'sub-section' => 'payrun',
                'view'        => 'approved_list'
            ], $payroll_url ),
            'approved_single_emp_page_url'   => add_query_arg( [
                'sub-section' => 'payrun',
                'view'        => 'single_payslip'
            ], $payroll_url ),
            'payrun_page_url'                => add_query_arg( [
                'sub-section' => 'payrun',
                'view'        => 'approved_list'
            ], $payroll_url ),
            'payrun_calendar_page_url'       => add_query_arg( [
                'sub-section' => 'calendar'
            ], $payroll_url ),
            'payrun_employees_page_url'      => add_query_arg( [
                'sub-section' => 'payrun',
                'tab'         => 'employees'
            ], $payroll_url ),
            'payrun_url'                     => add_query_arg( [
                'sub-section' => 'payrun',
            ], $payroll_url ),
            'scriptDebug'                    => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false
        ];

        wp_localize_script( 'erp-app-payitem', 'wpErpPayroll', $localize_scripts );
    }

    /**
     * Register & Enqueue settings scripts
     *
     * @since 2.0.2
     *
     * @return void
     */
    public function enqueue_settings_scripts() {
        if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'erp-settings' ) {
            wp_register_script( 'erp-payroll-settings', WPERP_PAYROLL_ASSETS . '/js/settings.js', [ 'erp-settings' ], false, true );
            wp_enqueue_script( 'erp-payroll-settings' );
        }
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
    public function frontend_enqueue_scripts() {
        if ( ! is_admin() ) {
            $this->enqueue_scripts( 'hr-management_page_erp-hr-employee' );
        }
    }

    /**
     * Show bulk edit item template page
     *
     * @since 1.4.0
     *
     * @return void
     */
    public function bulk_edit_item() {
        require_once WPERP_PAYROLL_VIEWS . '/tab-templates/bulk-edit-item.php';
    }

}
