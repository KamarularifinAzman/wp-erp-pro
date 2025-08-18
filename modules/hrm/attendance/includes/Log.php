<?php
namespace WeDevs\Attendance;

use \WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Log handler
 *
 * @since 2.0.6
 *
 * @package WP-ERP-ASSETS
 */
class Log {

    use Hooker;

    /**
     * Load autometically when class instantiate
     *
     * @since 2.0.6
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'erp_hr_log_self_att_add', 'add_self_att' );
        $this->action( 'erp_hr_log_check_in_out', 'add_check_in_out', 10, 2 );
        $this->action( 'erp_hr_log_att_by_hr', 'add_att_by_hr', 10, 2 );
        $this->action( 'erp_hr_log_att_shift_add', 'add_shift' );
        $this->action( 'erp_hr_log_att_shift_edit', 'update_shift', 10, 2 );
        $this->action( 'erp_hr_log_att_shift_del', 'delete_shift' );
        $this->action( 'erp_hr_log_assign_to_shift', 'assign_to_shift', 10, 2 );
        $this->action( 'erp_hr_log_remove_from_shift', 'remove_from_shift', 10, 2 );
    }

    /**
     * Add log when new self attendance created
     *
     * @since 2.0.6
     *
     * @param  int $emp_id
     *
     * @return void
     */
    public function add_self_att( $emp_id ) {
        $employee  = new \WeDevs\ERP\HRM\Employee( $emp_id );

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance',
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> just gave attendance', 'erp-pro' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when new self attendance created
     *
     * @since 2.0.6
     *
     * @param int $emp_id
     * @param string $action
     *
     * @return void
     */
    public function add_check_in_out( $emp_id, $action ) {
        $employee = new \WeDevs\ERP\HRM\Employee( $emp_id );
        $action   = $action === 'checkin' ? 'in': 'out';

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance',
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> just checked %2$s', 'erp-pro' ), $employee->get_full_name(), $action ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when new attendance created by hr
     *
     * @since 2.0.6
     *
     * @param string $shift_name
     * @param string $emp_name
     *
     * @return void
     */
    public function add_att_by_hr( $shift_name, $emp_name ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance',
            'changetype'    => 'add',
            'message'       => sprintf( __( 'Attendance of <strong>%1$s</strong> has been added for <strong>%2$s</strong> shift', 'erp-pro' ), $emp_name, $shift_name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when new shift created
     *
     * @since 2.0.6
     *
     * @param string $shift_name
     *
     * @return void
     */
    public function add_shift( $shift_name ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance Shift',
            'changetype'    => 'add',
            'message'       => sprintf( __( 'A new shift titled <strong>%1$s</strong> has been created', 'erp-pro' ), $shift_name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when a shift updated
     *
     * @since 2.0.6
     *
     * @param object $shift_old
     * @param int $shift_id
     *
     * @return void
     */
    public function update_shift( $shift_old, $shift_id ) {
        $shift_new = erp_attendance_get_shift( absint( $shift_id ) );

        $array_diff = erp_get_array_diff( (array) $shift_new, (array) $shift_old );

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance Shift',
            'changetype'    => 'edit',
            'message'       => sprintf( __( 'A shift has been updated', 'erp-pro' ) ),
            'created_by'    => get_current_user_id(),
            'old_value'     => $array_diff['old_value'],
            'new_value'     => $array_diff['new_value'],
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when a shift is deleted
     *
     * @since 2.0.6
     *
     * @param object $shift
     *
     * @return void
     */
    public function delete_shift( $shift ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance Shift',
            'changetype'    => 'delete',
            'message'       => sprintf( __( 'A shift titled <strong>%1$s</strong> has been removed', 'erp-pro' ), $shift->name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when a employee is deleted from a shift
     *
     * @since 2.0.6
     *
     * @param int $shift_id
     * @param int $emp_id
     *
     * @return void
     */
    public function remove_from_shift( $shift_id, $emp_id ) {
        $shift    = erp_attendance_get_shift( absint( $shift_id ) );
        $employee = new \WeDevs\ERP\HRM\Employee( absint( $emp_id ) );

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance Shift',
            'changetype'    => 'delete',
            'message'       => sprintf( __( '<strong>%1$s</strong> has been removed from <strong>%2$s</strong> shift', 'erp-pro' ), $employee->get_full_name(), $shift->name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when a employee is assigned a shift
     *
     * @since 2.0.6
     *
     * @param int $shift_id
     * @param int $emp_id
     *
     * @return void
     */
    public function assign_to_shift( $shift_id, $emp_id ) {
        $shift    = erp_attendance_get_shift( absint( $shift_id ) );
        $employee = new \WeDevs\ERP\HRM\Employee( absint( $emp_id ) );

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Attendance Shift',
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> has been assigned to <strong>%2$s</strong> shift', 'erp-pro' ), $employee->get_full_name(), $shift->name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }
}
