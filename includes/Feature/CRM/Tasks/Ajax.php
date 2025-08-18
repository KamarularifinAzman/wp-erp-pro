<?php
namespace WeDevs\ERP_PRO\Feature\CRM\Tasks;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\Traits\Ajax as Trait_Ajax;

/**
 * Ajax class for tasks
 *
 * @since 1.0.1
 */
class Ajax {

    use Hooker;
    use Trait_Ajax;

    /**
     * The class constructor
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_crm_get_tasks', 'get_tasks' );
        $this->action( 'wp_ajax_erp_crm_search_user', 'search_user' );
        $this->action( 'wp_ajax_erp_crm_search_contact', 'search_contact' );
        $this->action( 'wp_ajax_erp_crm_update_task_status', 'update_task_status' );
    }

    /**
     * Retrieves all tasks info
     *
     * @since 1.0.1
     *
     * @return array
     */
    public function get_tasks() {
        global $wpdb;

        $args     = [];
        $data     = [];
        $activity = [];

        $this->verify_nonce( 'erp-crm-tasks' );

        if ( ! current_user_can( 'manage_options' ) && ! erp_crm_is_current_user_manager() && ! erp_crm_is_current_user_crm_agent() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( isset( $_GET['tab'] ) ) {
            $tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
        }

        if ( isset( $_GET['status'] ) ) {
            $args['status'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
        }

        if ( isset( $_GET['contact'] ) ) {
            $args['contact_id'] = intval( wp_unslash( $_GET['contact'] ) );
        }

        if ( isset( $_GET['user'] ) ) {
            $args['user_id'] = intval( wp_unslash( $_GET['user'] ) );
        }

        if ( isset( $_GET['q'] ) ) {
            $args['q'] = sanitize_text_field( wp_unslash( $_GET['q'] ) );
        }

        if ( isset( $_GET['date'] ) ) {
            $date = $_GET['date'];

            array_walk( $date, function( &$value, $key ) {
                $value = sanitize_text_field( wp_unslash( $value ) );
            });

            $args['date'] = $date;
        }

        if ( isset( $_GET['page'] ) ) {
            $page_no = intval( wp_unslash( $_GET['page'] ) );
        } else {
            $page_no = 1;
        }

        if ( isset( $_GET['number'] ) ) {
            $args['number'] = intval( wp_unslash( $_GET['number'] ) );
        } else {
            $args['number'] = 10;
        }

        $args['offset'] = ( $page_no - 1 ) * $args['number'];

        if ( 'all' !== $tab ) {
            $args['user_id'] = get_current_user_id();
        }

        $tasks       = Helpers::get_crm_tasks( $args );

        $total_page  = ceil( $tasks['total'] / $args['number'] );

        foreach ( $tasks['data'] as $index => $task ) {

            $task_data['id']     = intval( $task->id );
            $task_data['title']  = esc_attr( $task->title );
            $task_data['desc']   = wp_kses_post( esc_attr( wp_unslash( $task->task ) ) );

            $people              = erp_get_people( $task->contact_id );
            $contact_type        = in_array( 'company', $people->types ) ? 'companies' : 'contacts';
            $contact_url         = esc_url_raw( add_query_arg( [ 'page' => 'erp-crm', 'section' => 'contact', 'sub-section' => $contact_type, 'action' => 'view', 'id' => $people->id ], admin_url( 'admin.php' ) ) );
            $user                = get_user_by( 'ID', $task->assigned_by );
            $assigned_by['id']   = intval( $user->ID );
            $assigned_by['name'] = esc_attr( $user->display_name );

            $assigned_to_id      = [];
            $assigned_to_name    = [];
            $user_ids            = explode( ',', $task->assigned_to );

            foreach ( $user_ids as $id ) {
                $user               = get_user_by( 'ID', intval( $id ) );
                $assigned_to_id[]   = intval( $user->ID );
                $assigned_to_name[] = esc_attr( $user->display_name );
            }

            $assigned_to['id']   = implode( ', ', $assigned_to_id );
            $assigned_to['name'] = implode( ', ', $assigned_to_name );

            $activity[ $index ]['task']        = $task_data;
            $activity[ $index ]['contact']     = '<a href="' . $contact_url . '">' . $people->first_name . ' ' . $people->last_name . '</a>';
            $activity[ $index ]['assigned_to'] = $assigned_to;
            $activity[ $index ]['assigned_by'] = $assigned_by;
            $activity[ $index ]['done_at']     = $task->done_at;
            $activity[ $index ]['created_at']  = date( 'M d, Y | h:i A', strtotime( $task->created_at ) );
            $activity[ $index ]['due_at']      = date( 'M d, Y | h:i A', strtotime( $task->due_date ) );

            if ( $task->done_at ) {
                $activity[ $index ]['status'] = '<span class="stts stts-done">' . __( 'Done', 'erp-pro' ) . '</span>';
            } else {
                $curr_date = date( 'Y-m-d h:i:s', strtotime( current_time( 'mysql' ) ) );
                $due_date  = date( 'Y-m-d h:i:s', strtotime( $task->due_date ) );

                if ( $curr_date > $due_date ) {
                    $activity[ $index ]['status'] = '<span class="stts stts-due">' . __( 'Due', 'erp-pro' ) . '</span>';
                } else {
                    $activity[ $index ]['status'] = '<span class="stts stts-pend">' . __( 'Pending', 'erp-pro' ) . '</span>';
                }
            }
        }

        $data['total_page'] = $total_page;
        $data['activity']   = $activity;

        $this->send_success( $data );
    }

    /**
     * Updates task status
     *
     * @since 1.0.1
     *
     * @return array
     */
    public function update_task_status() {
        global $wpdb;
        $success = [];
        $errors  = [];

        $this->verify_nonce( 'erp-crm-tasks' );

        if ( ! current_user_can( 'manage_options' ) && ! erp_crm_is_current_user_manager() && ! erp_crm_is_current_user_crm_agent() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( isset( $_POST['task_ids'] ) ) {
            $task_ids = $_POST['task_ids'];

            array_walk( $task_ids, function( &$value, $key ) {
                $value = intval( wp_unslash( $value ) );
            });
        }

        if ( isset( $_POST['status'] ) && 'done' === $_POST['status'] ) {

            $msg         = __( 'Task Marked Incomplete', 'erp-pro' );
            $done        = false;
            $data        = [ 'done_at' => null ];
            $data_format = [];

        } else {

            $msg         = __( 'Task Marked Complete', 'erp-pro' );
            $done        = true;
            $data        = [ 'done_at' => current_time( 'mysql' ) ];
            $data_format = [ '%s' ];
        }

        $table = $wpdb->prefix . 'erp_crm_customer_activities';

        foreach( $task_ids as $task_id ) {

            $where        = [ 'id' => $task_id ];
            $where_format = [ '%d' ];
            $update_id    = $wpdb->update( $table, $data, $where, $data_format, $where_format );

            if ( ! is_wp_error( $update_id ) ) {
                $success['update'][] = $update_id;

            } else {
                $errors['general'] = __( 'Something went wrong, please try again', 'erp-pro' );
                $this->send_error( $errors );
            }
        }

        $success['message'] = $msg;
        $success['done']    = $done;

        $this->send_success( $success );
    }

    /**
     * Search crm contacts
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function search_contact() {

        $this->verify_nonce( 'erp-crm-tasks' );

        if ( ! current_user_can( 'manage_options' ) && ! erp_crm_is_current_user_manager() && ! erp_crm_is_current_user_crm_agent() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $term  = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
        $types = isset( $_REQUEST['types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['types'] ) ) : [];

        if ( empty( $term ) ) {
            die();
        }

        if ( empty( $types ) ) {
            die();
        }

        $matched_contact = [];
        $type            = ( count( $types ) > 1 ) ? $types : reset( $types );
        $crm_contacts    = erp_get_peoples( [ 's' => $term, 'type' => $type ] );

        if ( ! empty( $crm_contacts ) ) {
            foreach ( $crm_contacts as $user ) {
                $matched_contact[ $user->id ] = $user->first_name . ' ' . $user->last_name;
            }
        }

        $this->send_success( $matched_contact );
    }

    /**
     * Search crm users
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function search_user() {

        $this->verify_nonce( 'erp-crm-tasks' );

        if ( ! current_user_can( 'manage_options' ) && ! erp_crm_is_current_user_manager() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $term = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';

        if ( empty( $term ) ) {
            die();
        }

        $matched_users = [];
        $crm_users     = erp_crm_get_crm_user( [ 's' => $term ] );

        if ( ! empty( $crm_users ) ) {
            foreach ( $crm_users as $user ) {
                $matched_users[ $user->ID ] = $user->display_name;
            }
        }

        $this->send_success( $matched_users );
    }
}
