<?php
namespace WeDevs\AdvancedLeave\Forward;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

use \WeDevs\ERP\ErpErrors;

class Forward {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {

        add_filter( 'erp_settings_hr_leave_section_fields', array( $this, 'leave_settings_fields' ) );


        // daily schedule to expire unused carry forward leaves
        add_action( 'erp_daily_scheduled_events', array( $this, 'expire_unused_carryover_leaves' ) );

        if ( get_option('erp_pro_carry_encash_leave') !== 'yes' ) {
            return;
        }

        $this->include_files();

        add_action( 'erp-hr-leave-policy-form-bottom', array( $this, 'policy_forward_fields' ) );
        add_filter( 'erp_hr_leave_insert_policy_extra', array( $this, 'prepare_insert_data' ) );

        add_action( 'admin_init', array( $this, 'apply_forward_leaves' ) );
        add_action( 'admin_init', array( $this, 'export_encash_requests' ) );
        add_action( 'admin_menu', array( $this, 'forward_admin_menu' ) );
    }

    /**
     * Expire unused carryover leaves
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function expire_unused_carryover_leaves() {
        global $wpdb;

        $current_f_year = erp_hr_get_financial_year_from_date();

        if ( null === $current_f_year ) {
            return;
        }

        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE f_year = %d and trn_type = %s and cast(description as unsigned ) > %d",
                array( $current_f_year->id, 'leave_encashment_requests', 0 )
            )
        );

        if ( ! is_array( $results ) || empty( $results ) ) {
            return;
        }

        $bg = new LeaveCarryForwardBgProcess();

        foreach ( $results as $entl_id ) {
            $bg->push_to_queue( $entl_id );
        }

        $bg->save();

        /**
         * Run the queue, starting with leave entitlements data
         */
        $bg->dispatch();
    }

    /**
     * Include files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function include_files() {
        require_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Forward/functions.php';
    }

    /**
     * Handle form submission
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function apply_forward_leaves() {
        $apply        = isset( $_POST['apply_forward_leaves'] ) ? true : false;
        $requests_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        if ( ! $apply ) {
            return;
        }

        if ( ! check_ajax_referer( 'bulk-leave_forwards' )) {
            wp_die( esc_html__( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $errors = new ErpErrors( 'leave_request_status_change' );

        $curr_f_year = erp_hr_get_financial_year_from_date();

        if ( ! isset( $curr_f_year ) ) {
            $errors->add(
                sprintf(
                    '<a href="?page=erp-settings#/erp-hr/financial">%s</a>',
                    esc_attr__( 'Please create a new leave year', 'erp-pro' )
                )
            );
        }

        if ( $errors->has_error() ) {
            $errors->save();
            $redirect = add_query_arg( array( 'error' => 'apply_forward_leaves' ), $requests_uri );
        } else {
            $items = erp_pro_hr_leave_get_users_available_leaves();

            $generated_items = erp_pro_hr_leave_generate_users_forward_leaves( $items );
            erp_pro_hr_leave_apply_users_forward_leaves( $generated_items );

            $redirect = add_query_arg( 'success', 'true', $requests_uri );
        }

        wp_redirect( $redirect );
        exit();
    }

    /**
     * Export encash requests
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function export_encash_requests() {
        if ( ! isset( $_GET['export-encash'] ) ) {
            return;
        }

        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $prev_f_year = erp_pro_hr_leave_get_prev_financial_year();

        if ( ! isset( $prev_f_year ) ) {
            return;
        }

        erp_pro_hr_leave_export_encash_requests( $prev_f_year->id );

        exit();
    }

    /**
     * Add forward menu
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function forward_admin_menu() {
        erp_add_submenu( 'hr', 'leave', array(
            'title'         =>  __( 'Forward Leaves', 'erp' ),
            'capability'    =>  'erp_leave_manage',
            'slug'          =>  'forward-leave',
            'callback'      =>  [ $this, 'leave_forward_page' ],
            'position'      =>  37,
        ) );
    }

    /**
     * Include menu view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function leave_forward_page() {
        new LeaveForwardListTable();
    }

    /**
     * Include view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function policy_forward_fields( $leave_policy ) {
        include_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Forward/form.php';
    }

    /**
     * Prepare data before insert
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function prepare_insert_data( $data ) {
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $carry_days      = ! empty( $_POST['carryover-days'] ) ? absint( wp_unslash( $_POST['carryover-days'] ) ) : 0;
        $uses_limit      = ! empty( $_POST['carryover-uses-limit'] ) ? absint( wp_unslash( $_POST['carryover-uses-limit'] ) ) : 0;
        $encash_days     = ! empty( $_POST['encashment-days'] ) ? absint( wp_unslash( $_POST['encashment-days'] ) ) : 0;
        $based_on        = ! empty( $_POST['encashment-based-on'] ) ? sanitize_text_field( wp_unslash( $_POST['encashment-based-on'] ) ) : '';
        $forward_default = ! empty( $_POST['forward-default'] ) ? sanitize_text_field( wp_unslash( $_POST['forward-default'] ) ) : '';

        $data['encashment_days']      = $encash_days;
        $data['encashment_based_on']  = $based_on;
        $data['carryover_days']       = $carry_days;
        $data['carryover_uses_limit'] = $uses_limit;
        $data['forward_default']      = $forward_default;

        return $data;
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
            'title' => esc_html__( 'Enable Carry / Encash', 'erp-pro' ),
            'type'  => 'checkbox',
            'id'    => 'erp_pro_carry_encash_leave',
            'desc'  => esc_html__( 'Convert unused leaves to cash or carry them over to the next financial year.', 'erp-pro' )
        ];

        return $fields;
    }

}
