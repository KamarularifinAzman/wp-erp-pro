<?php
namespace WeDevs\HrTraining;

use \WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Log handler
 *
 * @since 1.1.3
 */
class Log {

    use Hooker;

    /**
     * Load autometically when class instantiate
     *
     * @since 1.1.3
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'transition_post_status', 'create_training', 10, 3 );
        $this->action( 'erp_hr_log_assign_employee_training', 'assign_employee_training', 10, 2 );
        $this->action( 'erp_hr_log_delete_employee_training', 'delete_employee_training', 10, 2 );
    }

    /**
     * Add log when a training is created or edited
     *
     * @since 1.1.3
     *
     * @param string $new_status
     * @param string $old_status
     * @param object $post
     *
     * @return void
     */
    public function create_training( $new_status, $old_status, $post ) {
        if ( 'erp_hr_training' != $post->post_type ) {
            return;
        }

        if ( 'publish' !== $new_status ) {
            return;
        }

        if ( 'publish' === $old_status ) {
            $message     = sprintf( __( '<strong>%s</strong> training has been updated', 'erp-pro' ), $post->post_title );
            $change_type = 'edit';
        } else {
            $message     = sprintf( __( '<strong>%s</strong> training has been created', 'erp-pro' ), $post->post_title );
            $change_type = 'add';
        }

        erp_log()->add( [
            'sub_component' => 'training',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => $change_type,
        ] );
    }

    /**
     * Add log when a employee is assigned to a training
     *
     * @since 1.1.3
     *
     * @param int $training_id
     * @param int $emp_id
     *
     * @return void
     */
    public function assign_employee_training( $training_id, $emp_id ) {
        $training = \get_post( $training_id );
        $employee = new \WeDevs\ERP\HRM\Employee( (int) $emp_id );

        erp_log()->add( [
            'sub_component' => 'training',
            'message'       => sprintf(
                __( '<strong>%1$s</strong> employee has been assigned to training titled <strong>%2$s</strong>', 'erp-pro' ),
                $employee->get_full_name(),
                $training->post_title
            ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ] );
    }

    /**
     * Add log when a employee is removed a training
     *
     * @since 1.1.3
     *
     * @param int $training_id
     * @param int $emp_id
     *
     * @return void
     */
    public function delete_employee_training( $training_id, $emp_id ) {
        $training = \get_post( $training_id );
        $employee = new \WeDevs\ERP\HRM\Employee( (int) $emp_id );

        erp_log()->add( [
            'sub_component' => 'training',
            'message'       => sprintf(
                __( '<strong>%1$s</strong> employee has been removed from training titled <strong>%2$s</strong>', 'erp-pro' ),
                $employee->get_full_name(),
                $training->post_title
            ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }
}
