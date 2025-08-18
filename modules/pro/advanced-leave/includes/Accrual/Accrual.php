<?php
namespace WeDevs\AdvancedLeave\Accrual;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

class Accrual {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'erp_settings_hr_leave_section_fields', array( $this, 'leave_settings_fields' ) );

        if ( get_option('erp_pro_accrual_leave') !== 'yes' ) {
            return;
        }

        add_action( 'erp-hr-leave-policy-form-bottom', array( $this, 'policy_accrual_fields' ) );
        add_filter( 'erp_hr_leave_insert_policy_extra', array( $this, 'prepare_insert_data' ) );

        add_action( 'erp_daily_scheduled_events', array( $this, 'calculate_accrual' ) );
    }

    public function calculate_accrual() {
        global $wpdb;

        $erp_pro_accrual_bg = new AccrualBgProcess();

        // 1. get current financial year
        $financial_year = erp_hr_get_financial_year_from_date();

        if ( empty( $financial_year ) ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'No financial year data found.',
                    ),
                    true
                )
            );
            return;
        }

        // 2. get policies only for current financial year
        $policies = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_hr_leave_policies WHERE accrued_amount > 0  AND f_year = %d",
                array( $financial_year->id )
            )
        );

        if ( is_array( $policies ) && ! empty( $policies ) ) {

            foreach ( $policies as $key => $policy_id ) {
                $erp_pro_accrual_bg->push_to_queue(
                    array(
                        'task' => 'task_process_policy',
                        'policy_id'   => $policy_id,
                    )
                );
            }

            $erp_pro_accrual_bg->save();

            /**
             * Run the queue, starting with leave entitlements data
             */
            $erp_pro_accrual_bg->dispatch();
        }
    }

    /**
     * Include view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function policy_accrual_fields( $leave_policy ) {
        include_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Accrual/form.php';
    }

    /**
     * Prepare data before insert
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function prepare_insert_data( $data ) {
        if ( current_user_can( 'erp_leave_manage' ) === false && erp_hr_is_current_user_dept_lead() === false ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $days     = ! empty( $_POST['accrued-amount'] ) ? abs( floatval( wp_unslash( $_POST['accrued-amount'] ) ) ) : 0;
        $max_days = ! empty( $_POST['accrued-max-days'] ) ? absint( wp_unslash( $_POST['accrued-max-days'] ) ) : 0;

        $data['accrued_amount']   = $days;
        $data['accrued_max_days'] = $max_days;

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
            'title' => esc_html__( 'Enable Accrual', 'erp-pro' ),
            'type'  => 'checkbox',
            'id'    => 'erp_pro_accrual_leave',
            'desc'  => esc_html__( 'Enable accrual leave.', 'erp-pro' )
        ];

        return $fields;
    }

}
