<?php
namespace WeDevs\AdvancedLeave\Segregation;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\LeavePoliciesSegregation;
use WeDevs\ERP\HRM\Models\LeavePolicy;

class Segregation {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'erp_settings_hr_leave_section_fields', array( $this, 'leave_settings_fields' ) );

        if ( get_option( 'erp_pro_seg_leave' ) !== 'yes' ) {
            return;
        }

        add_action( 'erp-hr-leave-policy-form-bottom', array( $this, 'policy_segregation_fields' ) );
        add_filter( 'erp_hr_leave_update_policy', array( $this, 'prepare_update_data' ) );

        add_filter( 'erp_hr_leave_before_insert_new_entitlement', array( $this, 'apply_segregation' ) );
    }

    /**
     * Apply segregation
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function apply_segregation( $fields ) {
        // check we are assigning leave policies
        if ( $fields['trn_type'] !== 'leave_policies' ) {
            return $fields;
        }

        // check segregation is enabled
        if ( get_option( 'erp_pro_seg_leave' ) !== 'yes' ) {
            return $fields;
        }

        // get employee from user id
        $employee = new Employee( $fields['user_id'] );

        // check if this user is a valid employee
        if ( ! $employee->is_employee() ) {
            return new \WP_Error( 'invalid-employee-' . $fields['user_id'], esc_attr__( 'Error: Invalid Employee. No employee found with given ID: ', 'erp' ) . $fields['user_id'] );
        }

        // get policy data
        $policy = LeavePolicy::find( $fields['trn_id'] );
        if ( ! $policy ) {
            return new \WP_Error( 'invalid-policy-' . $fields['trn_id'], esc_attr__( 'Error: Invalid Policy. No leave policy found with given ID: ', 'erp' ) . $fields['trn_id'] );
        }

        // we need hiring date in order to apply segregation
        if ( empty( $employee->get_hiring_date() ) || ! erp_is_valid_date( $employee->get_hiring_date() ) ) {
            return new \WP_Error( 'invalid-joining-date', esc_attr__( 'Error: Employee joining date is invalid: ', 'erp' ) . $fields['user_id'] );
        }

        // get hiring date
        $hiring_date = erp_current_datetime()->modify( $employee->get_hiring_date() )->setTime( 0, 0, 0 );

        // get current date
        $today = erp_current_datetime()->setTime( 0, 0, 0 );

        // check if hiring date in the future
        $interval = date_diff( $hiring_date, $today );

        if ( $interval->invert == 1 ) {
            return new \WP_Error( 'invalid-joining-date', esc_attr__( 'Error: Employee joining date is in the future: ', 'erp' ) . $fields['user_id'] );
        }

        $compare_with = $hiring_date->modify( '+ ' . $policy->applicable_from_days . ' days' );

        if ( $compare_with > $today ) {
            return new \WP_Error( 'invalid-joining-date', esc_attr__( 'Error: Employee is not eligible for this leave policy yet: ', 'erp' ) . $fields['user_id'] );
        }

        // check if this a new employee and then apply segregation rule
        if ( $compare_with <= $today ) {
            $interval = date_diff( $compare_with, $today );

            // segregation  will apply max 30 days after applicable_form days.
            if ( $interval->days <= 30 ) {
                // check if segregation assigned for this policy.
                $current_month = strtolower( $today->format( 'M' ) );
                $current_month = $current_month === 'dec' ? 'decem' : $current_month;
                $segregation = $policy->segregation->toArray();

                if ( array_key_exists( $current_month, $segregation ) && $segregation[ $current_month ] != 0 ) {
                    $fields['day_in'] = $segregation[ $current_month ];
                }
            }
        }

        return $fields;
    }

    /**
     * Include view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function policy_segregation_fields() {
        $id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;

        if ( $id ) {
            $segregation = LeavePoliciesSegregation::where( 'leave_policy_id', $id )->first();
        }

        include_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Segregation/form.php';
    }

    /**
     * Prepare data update
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function prepare_update_data( $id ) {
        $segre = isset( $_POST['segre'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['segre'] ) ) : [];

        LeavePoliciesSegregation::where( 'leave_policy_id', $id )->update( $segre );
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
    public function leave_settings_fields( $fields ) {
        $fields['leave'][] = [
            'title' => __( 'Enable Segregation', 'erp-pro' ),
            'type'  => 'checkbox',
            'id'    => 'erp_pro_seg_leave',
            'desc'  => esc_html__( 'Enable leave segregation.', 'erp-pro' ),
        ];

        return $fields;
    }

}
