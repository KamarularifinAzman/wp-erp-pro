<?php

use \WeDevs\ERP\HRM\Models\Leave_Approval_Status;

/**
 * Insert forward leave request
 *
 * @since 1.0.0
 *
 * @return bool
 */
function erp_pro_hr_leave_insert_forward_leave_request( $request_id, $forward_to, $comments ) {
        if ( current_user_can( 'erp_leave_manage' ) === false && erp_hr_is_current_user_dept_lead() === false ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $approval = Leave_Approval_Status::create( array(
        'leave_request_id'   => $request_id,
        'approval_status_id' => 4,
        'approved_by'        => get_current_user_id(),
        'forward_to'         => $forward_to,
        'message'            => $comments
    ) );

    return $approval->id ? true : false;
}

/**
 * Get users only by hr manager role
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_pro_hr_leave_get_only_hr_managers() {
    $dropdown  = array( 0 => esc_html__( '- Select HR Managers -', 'erp' ) );

    $args = array(
        'role'    => 'erp_hr_manager',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );

    $hrs = get_users( $args );

    if ( ! empty( $hrs ) ) {
        foreach ( $hrs as $hr ) {
            $dropdown[ $hr->ID ] = $hr->display_name;
        }
    }

    return $dropdown;
}
