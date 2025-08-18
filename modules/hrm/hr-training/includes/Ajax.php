<?php
namespace WeDevs\HrTraining;

/**
 * Class Ajax
 */
class Ajax {
    /**
     * Construct Function
     */
    function __construct() {
        add_action( 'wp_ajax_erp_training_employee_count', array( $this, 'erp_training_employee_count' ) );

        add_action( 'wp_ajax_erp_assign_new_training', array( $this, 'assgin_new_employee_training' ) );

        add_action( 'wp_ajax_erp_get_training', array( $this, 'erp_get_training' ) );

        add_action( 'wp_ajax_erp_delete_training', array( $this, 'erp_delete_training' ) );

        add_action( 'wp_ajax_erp_training_incompleted', array( $this, 'check_incompleted_training' ) );
    }

    /**
     * Employee Count
     *
     * @return aray
     */
    public function erp_training_employee_count() {

        $departments  = !empty( $_POST['departments'] ) ? $_POST['departments'] : array();
        $designations = !empty( $_POST['designations'] ) ? $_POST['designations'] : array();
        $designations = !empty( $_POST['designations'] ) ? $_POST['designations'] : array();
        $employee     = !empty( $_POST['employees'] ) ? $_POST['employees'] : array();
        $employees    = [];
        if ( $departments ) {
            foreach ( $departments as $department ) {
                $employees[] = erp_hr_get_employees( array(
                    'no_object'  => true,
                    'department' => $department
                ) );
            }
        }

        if ( $designations ) {
            foreach ( $designations as $designation ) {
                $employees[] = erp_hr_get_employees( array(
                    'no_object'  => true,
                    'department' => $designation
                ) );
            }
        }

        $data     = format_data_as_employee( $employees );
        $new_data = array_merge( $data, $employee );
        $count    = count( array_unique( $new_data ) );

        wp_send_json_success( array( 'count' => $count ) );
    }

    /**
     * Assign new employee training
     *
     * @return object
     */
    public function assgin_new_employee_training() {
        if ( ! current_user_can( 'erp_list_employee' ) ) {
            return;
        }

        $post_meta = array();
        $post_meta['erp_training_completed_date']    = ( isset( $_POST['training-completed-date'] ) ) ? $_POST['training-completed-date'] : '';
        $post_meta['erp_training_trainer']           = ( isset( $_POST['training-trainer'] ) ) ? $_POST['training-trainer'] : '';
        $post_meta['erp_trainer_phone']              = ( isset( $_POST['trainer-phone'] ) ) ? $_POST['trainer-phone'] : '';
        $post_meta['erp_training_cost']              = ( isset( $_POST['training-cost'] ) ) ? $_POST['training-cost'] : '';
        $post_meta['erp_training_credit']            = ( isset( $_POST['training-credit'] ) ) ? $_POST['training-credit'] : '';
        $post_meta['erp_training_hours']             = ( isset( $_POST['training-hours'] ) ) ? $_POST['training-hours'] : '';
        $post_meta['erp_training_note']              = ( isset( $_POST['training-notes'] ) ) ? $_POST['training-notes'] : '';
        $post_meta['erp_training_rate']              = ( isset( $_POST['training-rate'] ) ) ? $_POST['training-rate'] : '';
        $post_meta['completed']                      = ( isset( $_POST['completed'] ) ) ? 'yes' : 'no';
        $erp_training_id                             = ( isset( $_POST['training-id'] ) ) ? $_POST['training-id'] : '';
        $erp_training_user_id                        = ( isset( $_POST['erp_training_user_id'] ) ) ? $_POST['erp_training_user_id'] : '';
        $training_meta                               = get_user_meta( $erp_training_user_id, 'erp_employee_training', true );
        $user_meta                                   = is_array( $training_meta ) ? $training_meta : array();
        $training_users_copleted                     = get_post_meta( $erp_training_id, 'erp_training_completed_employee', true );
        $training_users_incomplete                   = get_post_meta( $erp_training_id, 'erp_training_incompleted_employee', true );
        if ( empty( $training_users_copleted ) ) {
            $training_users_copleted = [];
        }

        $user_meta[ $erp_training_id ] =   $post_meta;
        $training_users_copleted[]     =   $erp_training_user_id;
        $training_employee             =   array_unique( $training_users_copleted );
        foreach ( $training_users_incomplete as $key => $value) {
            if ( in_array( $value,  $training_users_copleted  ) ) {
                unset( $training_users_incomplete[ $key ] );
            }
        }

        update_post_meta( $erp_training_id, 'erp_training_incompleted_employee', $training_users_incomplete );
        update_post_meta( $erp_training_id, 'erp_training_completed_employee', $training_users_copleted );
        update_user_meta( $erp_training_user_id, 'erp_employee_training', $user_meta );

        do_action( 'erp_hr_log_assign_employee_training', $erp_training_id, $erp_training_user_id );

        erp_training_purge_cache( ['list' => 'training'] );

        wp_send_json_success();
    }

    /**
     * Get training
     *
     * @return object
     */
    public function erp_get_training() {
        if ( !current_user_can( 'erp_list_employee' ) ) {
            return;
        }

        $post_id            = ( isset( $_POST['post_id'] ) ) ? intval( $_POST['post_id'] ) : 0;
        $user_id            = ( isset( $_POST['user_id'] ) ) ? intval( $_POST['user_id'] ) : 0;
        $post               = get_post( $post_id );
        $training_meta      = get_user_meta( $user_id, 'erp_employee_training', true );
        $user_meta          = is_array( $training_meta ) ? $training_meta : array();
        $data               = ( ! empty( $user_meta[ $post_id ] ) ) ? $user_meta[ $post_id ] : array();
        $data['post_id']    = $post->ID;
        $data['post_title'] = $post->post_title;
        wp_send_json_success( $data );
    }

    /**
     * Delete employee training
     *
     * @return object
     */
    public function erp_delete_training() {
        if ( ! current_user_can( 'erp_list_employee' ) ) {
            return;
        }
        $user_meta          = array();
        $post_id            = ( isset( $_POST['post_id'] ) ) ? intval( $_POST['post_id'] ) : 0;
        $user_id            = ( isset( $_POST['user_id'] ) ) ? intval( $_POST['user_id'] ) : 0;

        $training_users     = get_post_meta( $post_id, 'erp_training_completed_employee', true );
        $user_meta          = get_user_meta( $user_id, 'erp_employee_training', true );

        if ( $training_users ) {
            foreach ( $training_users as $key => $user ) {
                if ( $user == $user_id ) {
                    unset($training_users[$key]);
                }
            }
        }

        if ( is_array( $user_meta ) &&  isset( $user_meta[ $post_id ] ) ) {
            unset( $user_meta[ $post_id ] );
        }


        update_post_meta( $post_id, 'erp_training_completed_employee', $training_users );
        update_user_meta( $user_id, 'erp_employee_training', $user_meta );

        do_action( 'erp_hr_log_delete_employee_training', $post_id, $user_id );

        erp_training_purge_cache( ['list' => 'training'] );

        wp_send_json_success();
    }

    /**
     * Remove the training from completed training list
     *
     * @return object
     */
    public function check_incompleted_training() {
        if ( !current_user_can( 'erp_list_employee' ) ) {
            return;
        }
        $post_id            = ( isset( $_POST['post_id'] ) ) ? intval( $_POST['post_id'] ) : 0;
        $user_id            = ( isset( $_POST['user_id'] ) ) ? intval( $_POST['user_id'] ) : 0;

        $training_users_copleted     = get_post_meta( $post_id, 'erp_training_completed_employee', true );
        $training_users_incopleted   = get_post_meta( $post_id, 'erp_training_incompleted_employee', true );
        $user_meta                   = get_user_meta( $user_id, 'erp_employee_training', true );

        $training_users_incopleted[] = $user_id;
        if ( $training_users_copleted ) {
            foreach ( $training_users_copleted as $key => $user ) {
                if ( $user == $user_id ) {
                    unset($training_users_copleted[$key]);
                }
            }
        }

        if ( is_array( $user_meta ) && isset( $user_meta[ $post_id ] ) ) {
            unset( $user_meta[ $post_id ] );
        }

        update_post_meta( $post_id, 'erp_training_completed_employee', $training_users_copleted );
        update_post_meta( $post_id, 'erp_training_incompleted_employee', $training_users_incopleted );
        update_user_meta( $user_id, 'erp_employee_training', $user_meta );

        erp_training_purge_cache( ['list' => 'training'] );

        wp_send_json_success();
    }
}
