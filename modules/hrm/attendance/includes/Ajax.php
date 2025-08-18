<?php
namespace WeDevs\Attendance;

use WeDevs\ERP\Framework\Traits\Ajax as ERP_Ajax;

class Ajax {

    use ERP_Ajax ;

    public function __construct() {
        add_action( 'wp_ajax_erp_att_save_self_attendance', [ $this, 'save_self_attendance' ] );
        add_action( 'wp_ajax_erp_att_get_attendance_data', [ $this, 'get_attendance_data'] );
        add_action( 'wp_ajax_erp_att_check_single_working_time', [ $this, 'check_single_working_time'] );
        add_action( 'wp_ajax_erp_get_att_by_date', [ $this, 'get_attendance_by_date'] );
        add_action( 'wp_ajax_erp_att_save_hr_input', [ $this, 'attendance_save_by_hr'] );
        add_action( 'wp_ajax_erp_att_get_employee_attendance_data', [ $this, 'get_employee_attendance_data'] );
        add_action( 'wp_ajax_erp_get_att_by_date_for_edit', [ $this, 'get_attendance_by_date_for_edit'] );
        add_action( 'wp_ajax_erp_att_delete_single_shift', [ $this, 'delete_single_shift'] );
        add_action( 'wp_ajax_erp_att_get_shifts', [ $this, 'get_shift_list'] );
        add_action( 'wp_ajax_erp_att_bulk_action', [ $this, 'manage_bulk_action'] );
        add_action( 'wp_ajax_erp_new_shift_save', [ $this, 'save_new_shift' ] );
        add_action( 'wp_ajax_erp_edit_shift_save', [ $this, 'save_edit_shift' ] );
        add_action( 'wp_ajax_erp_get_shift_users', [ $this, 'get_shift_users' ] );
        add_action( 'wp_ajax_erp_get_shift_users_count', [ $this, 'get_shift_users_count' ] );
        add_action( 'wp_ajax_erp_att_get_attendance_user_log', [ $this, 'get_attendance_user_log' ] );
        add_action( 'erp_hr_employee_new', [$this, 'save_employee_shift_data'], 10, 2 );

        add_action( 'wp_ajax_erp_hr_get_employee_log', [$this, 'erp_hr_get_employee_log'] );

        add_action( 'wp_ajax_erp_hr_set_employee_log', [$this, 'erp_hr_set_employee_log'] );

        add_filter( 'erp_hr_get_employee_fields', [$this, 'get_employee_shift_fields'], 10, 3 );

        add_filter( 'modify_days', [ $this, 'modify_attendance_active_days' ], 10, 2 );
    }

    /**
     * Save self attendance data
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function save_self_attendance() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        if ( erp_att_has_restriction() ) {
            $client_ip = erp_get_client_ip();

            if ( ! erp_att_is_ip_allowed( $client_ip ) ) {
                wp_send_json_error( sprintf( __( 'You are not allowed to %s from this IP', 'erp-pro' ), $type ) );
            }
        }

        if ( ! if_check_in_out_valid() ) {
            wp_send_json_error( __( 'You have take leave in this shift', 'erp-pro' ) );
        }

        $attendance = erp_attendance_punch( get_current_user_id() );

        if ( ! is_wp_error( $attendance ) )  {
            do_action( 'erp_hr_log_self_att_add', get_current_user_id() );

            wp_send_json_success( $attendance );
        } else {
            wp_send_json_error( $attendance->get_error_message() );
        }
    }

    /**
     * Get attendance status data
     *
     * @since 1.0
     *
     * @return void
     */
    public function get_attendance_data() {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        $query           = isset( $_GET['query'] ) ? $_GET['query'] : 0;
        $duration        = erp_att_get_start_end_date( $query );
        $user_id         = get_current_user_id();
        $attendance_data = erp_att_get_attendance_data( $duration['start'], $duration['end'], $user_id, true );

        if ( ! empty( $attendance_data ) ) {
            $data = [
                [
                    'label' => __( 'On-time', 'erp-pro' ),
                    'data' => $attendance_data['proper_checkins'],
                    'color' => '#4CAF50'
                ],
                [
                    'label' => __( 'Late', 'erp-pro' ),
                    'data' => $attendance_data['late_checkins'],
                    'color' => '#FF9800'
                ],
                [
                    'label' => __( 'Absent', 'erp-pro' ),
                    'data' => $attendance_data['absents'],
                    'color' => '#F44336'
                ],
            ];

        } else {
            $data = [];
        }

        wp_send_json_success( $data );
    }

    /**
     * get_single_attendance_data
     *
     * @since 1.1.3
     *
     * @return void
     */
    public function check_single_working_time() {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        $data = erp_attendance_punch( get_current_user_id() );

        wp_send_json_success( $data );
    }

    /**
     * Get attendance details for HR input
     *
     * @return void
     */
    public function get_attendance_by_date() {

        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        $date = sanitize_text_field( $_REQUEST['date'] );

        $args = [
            'date' => $date,
            'orderby' => 'employee_id'
        ];

        $attendance_data = erp_att_get_single_attendance( $args );

        wp_send_json_success( $attendance_data );
    }

    /**
     * Save attendance by HR Manager
     *
     * @return void
     */
    // public function attendance_save_by_hr() {
    //     if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-erp-attendance' ) ) {
    //         wp_send_json_error( 'Invalid operation', 'erp-pro' );
    //     }

    //     if ( ! ( current_user_can( 'administrator' ) || current_user_can( erp_hr_get_manager_role() ) ) ) {
    //         wp_send_json_error( 'You do not have permission for this operation', 'erp-pro' );
    //     }

    //     $date        = sanitize_text_field( $_POST['date'] );
    //     $attendance  = wp_unslash( $_POST['attendance'] );
    //     $attendance  = json_decode( $attendance, true );

    //     if( ! $date ) {
    //         wp_send_json_error( 'Date is required', 'erp-pro' );
    //     }

    //     foreach ( $attendance as $att ) {
    //         if ( ! empty( $att['checkin'] ) ) {
    //             $checkin  = $date . ' ' . $att['checkin'];
    //             $checkout = empty( $att['checkout'] ) ? '' : ( $date . ' ' . $att['checkout'] );

    //             if ( ! empty( $att['checkin_id'] ) ) {
    //                 update_new_attendance_entry_by_hr( $att['user_id'], $att['checkin_id'], $att['checkout_id'], $checkin, $checkout );
    //             } else {
    //                 save_new_attendance_entry_by_hr( $date, $att['user_id'], $att['dshift_id'], $checkin, $checkout );
    //             }
    //         }
    //     }

    //     wp_send_json_success( __( 'Successfully added attendance data', 'erp-pro' ) );
    // }

    /**
     * Get single attendance by employee
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function get_employee_attendance_data() {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        global $wpdb;

        $user_id    = ! empty( $_GET['employee_id'] ) ? absint( $_GET['employee_id'] ) : get_current_user_id();
        $today      = current_time( 'Y-m-d H:i:s' );
        $attendance = [
            'ds_id'        => '',
            'log_id'       => '',
            'shift_title'  => '',
            'min_checkin'  => '',
            'max_checkout' => ''
        ];
        $shift_time = '';

        $result     = erp_attendance_get_single_user_log( $user_id, $today );

        $grace_checking_time    = erp_attendance_get_grace_checkin_time();
        $shift_validity_start   = date('Y-m-d h:i A',strtotime('-'. $grace_checking_time .' seconds',strtotime($result->ds_start_time)));
        $shift_validity_end     = date('Y-m-d h:i A',strtotime('-'. ( $grace_checking_time + 60 ) .' seconds',strtotime($result->ds_end_time)));


        if ( ! empty( $result ) ) {
            $attendance = [
                'ds_id'          => $result->ds_id,
                'log_id'         => $result->log_id,
                'shift_title'    => $result->shift_title,
                'ds_start_time'  => $shift_validity_start,
                'ds_end_time'    => $shift_validity_end,
                'log_time'       => $result->log_time,
                'min_checkin'    => date( 'H:i:s', strtotime( $result->min_checkin ) ),
                'max_checkout'   => date( 'H:i:s', strtotime( $result->max_checkout ) )
            ];

            $start_time = date( 'h:i A', strtotime( $result->start_time ) );
            $end_time   = date( 'h:i A', strtotime( $result->end_time ) );

            $shift_time = $start_time . ' - ' . $end_time;
        }

        wp_send_json_success( [
            'attendance'        => $attendance,
            'shift_time'        => $shift_time
        ] );
    }

    /**
     * Get attendance details for HR input for Edit
     *
     * @return void
     */
    public function get_attendance_by_date_for_edit() {

        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        $date = sanitize_text_field( $_REQUEST['date'] );

        $args = [
            'date' => $date,
            'orderby' => 'employee_id'
        ];

        $attendance_data = erp_att_get_single_attendance( $args );

        foreach ( $attendance_data as &$attendance ) {
            $attendance->present = ( 'yes' === $attendance->present ) ? 'yes' : 'no';
            $attendance->checkin = $attendance->checkin ? date( 'H:i', strtotime( $attendance->checkin ) ) : null;
            $attendance->checkout = $attendance->checkout ? date( 'H:i', strtotime( $attendance->checkout ) ) : null;
        }

        wp_send_json_success( $attendance_data );
    }

    /**
     * Delete a single shift
     *
     * @return void
     */
    public function delete_single_shift() {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are not allowed' );
        }

        $shift_id = isset( $_REQUEST['shift_id'] ) && $_REQUEST['shift_id'] ? intval( $_REQUEST['shift_id'] ) : '';

        if ( $shift_id ) {
            erp_att_delete_shift( $shift_id );
            wp_send_json_success();
        }
    }

    /**
     * Manages bulk action in shift assign table
     *
     * @since 1.2
     * @return void
     */
    public function manage_bulk_action() {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are no allowed' );
        }

        $trigger   = isset( $_REQUEST['trigger'] ) && $_REQUEST['trigger'] ? $_REQUEST['trigger'] : '';
        $employees = isset( $_REQUEST['employees'] ) && $_REQUEST['employees'] ? $_REQUEST['employees'] : '';

        if( $trigger ) {
            if( 'delete' == $trigger ) {
                foreach( $employees as $employee ) {
                    if( "true" == $employee['selected'] ) {
                        foreach( $employee['dates'] as $date ) {
                            if( isset( $date['shifts'] ) ) {
                                foreach( $date['shifts'] as $shift ) {
                                    $shift = erp_attendance_get_shift( $shift['id'] );
                                    erp_att_delete_shift( $shift['id'] );
                                    do_action( 'erp_hr_log_att_shift_del', $shift );
                                }
                            }
                        }
                    }
                }

                wp_send_json_success();
            }
        }

    }

    /**
     * Save new shift
     *
     * @return void
     */
    public function save_new_shift() {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are no allowed' );
        }

        $start_time = ! empty( $_REQUEST['startTime'] ) ? sanitize_text_field( $_REQUEST['startTime'] ) : '00:00:00';
        $end_time   = ! empty( $_REQUEST['endTime'] ) ? sanitize_text_field( $_REQUEST['endTime'] ) : '00:00:00';
        $shift_name = ! empty( $_REQUEST['shiftName'] ) ? sanitize_text_field( $_REQUEST['shiftName'] ) : null;
        $holidays   = ! empty( $_REQUEST['holidays'] ) ? $_REQUEST['holidays'] : [];

        $shift_id = erp_attendance_insert_shift( $start_time, $end_time, $shift_name, $holidays );
    }

    public function get_shift_users() {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are no allowed' );
        }

        $shift_users = [];
        $shift_users_info = erp_attendance_get_shift_users(
            absint( $_REQUEST['shift_id'] ),
            absint( $_REQUEST['limit'] ),
            absint( $_REQUEST['offset'] )
        );

        foreach ( $shift_users_info as $user ) {
            $shift_users[$user->user_id] = erp_hr_get_employee_name( $user->user_id );
        }

        wp_send_json_success( $shift_users );
    }

    public function save_edit_shift() {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are no allowed' );
        }

        $users_id        = [];
        $shift_id        = absint( $_REQUEST['shift_id'] );
        $shift           = erp_attendance_get_shift( $shift_id );

        $start_date      = $_REQUEST['start_date'];
        $end_date        = $_REQUEST['end_date'];

        $assign_ids      = (array) $_REQUEST['assign_type_ids'];
        $overwrite       = (bool) $_REQUEST['overwrite'];

        switch ( $_REQUEST['assign_type'] ) {

            case 'all_employees':
                $employees = erp_hr_get_employees([ 'number' => -1 ]);
                $users_id  = wp_list_pluck( $employees, 'user_id' );
                break;

            case 'selected_employees':
                $users_id = $assign_ids;
                break;

            case 'by_department':
                foreach ( $assign_ids as $department_id ) {
                    $employees = erp_hr_get_employees(array(
                        'department' => $department_id
                    ));

                    $users_id[] = wp_list_pluck( $employees, 'user_id' );
                }
                break;

            case 'by_designation':
                foreach ( $assign_ids as $designation_id ) {
                    $employees = erp_hr_get_employees(array(
                        'designation' => $designation_id
                    ));

                    $users_id[] = wp_list_pluck( $employees, 'user_id' );
                }
                break;

            case 'by_shift':
                foreach ( $assign_ids as $shift_id ) {
                    $users_id[] = erp_attendance_get_shift_users_id( $shift_id );
                }
                break;

        }

        $users_id = erp_array_flatten( $users_id );

        foreach ( $users_id as $user_id ) {
            erp_attendance_insert_shifting_for_user( $shift_id, $user_id, $start_date, $end_date, $overwrite );
            erp_attendance_assign_shift( $user_id, $shift_id, $overwrite );
        }

        wp_send_json_success();
    }

    public function get_shift_users_count() {
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp-erp-attendance' ) ) {
            die( 'You are no allowed' );
        }

        $users_count = erp_attendance_get_shift_users_count( absint( $_REQUEST['shift_id'] ) );

        wp_send_json_success( $users_count );
    }

    public function get_attendance_user_log() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp-erp-attendance' ) ) {
            die ( 'You are no allowed' );
        }

        $log = erp_attendance_get_single_user_log();

        wp_send_json_success( $log );
    }

    public function save_employee_shift_data( $user_id, $data ) {
        global $wpdb;

        if ( isset( $data['work']['shift'] ) && $data['work']['shift'] != -1 ) {
            $shift  = $data['work']['shift'] ;
            $table  = "{$wpdb->prefix}erp_attendance_shift_user";
            $result = $wpdb->get_row( "SELECT * FROM {$table} WHERE user_id = {$user_id}" );
            if ( ! $result) {
                $insert_id = $wpdb->insert( $table,[ 'shift_id' => $shift, 'user_id' => $user_id ], [ '%d', '%d' ] );
            } else {
                $insert_id = $wpdb->update( $table,[ 'shift_id' => $shift, 'user_id' => $user_id ],[ 'user_id' => $user_id ], [ '%d', '%d' ], [ '%d' ] );
            }
        }
    }

    public function get_employee_shift_fields( $fields, $id, $user ){
        global $wpdb;

        if ( isset( $fields['user_id'] ) ) {
            $user_id = $fields['user_id'];
            $table   = "{$wpdb->prefix}erp_attendance_shift_user";
            $result  = $wpdb->get_row( "SELECT * FROM {$table} WHERE user_id = {$user_id}" );

            if ( $result ) {
                $fields['work']['shift'] = $result->shift_id;
            }
        }
        return $fields;
    }

    public function erp_hr_get_employee_log() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-nonce' ) ) {
            die( 'You are not allowed' );
        }

        $user_id = ( $_POST['user_id'] ) ? $_POST['user_id'] : '';
        $date    = ( $_POST['date'] ) ? $_POST['date'] : '';

        if ( empty( $user_id ) || empty( $date ) ) {
            return ;
        }

        global $wpdb;
        $sql     = "SELECT al.id as id,al.checkin as checkin, al.checkout as checkout, al.time as log_time  FROM {$wpdb->prefix}erp_attendance_date_shift as ds LEFT JOIN {$wpdb->prefix}erp_attendance_log as al ON ds.id = al.date_shift_id AND ds.user_id = al.user_id WHERE ds.date = '{$date}' AND ds.user_id={$user_id}";
        $results = $wpdb-> get_results($sql);

        if ( empty( $results[0]->checkin ) ) {
            wp_send_json( [] );
        }

        $results = array_map( function( $data ) {
            return [
                'id'           => $data->id,
                'checkin'      => ( $data->checkin != '0000-00-00 00:00:00' ) ? date( 'M d, h:i:s A', strtotime( $data->checkin ) ) : '-',
                'checkin_raw'  => ( $data->checkin != '0000-00-00 00:00:00' ) ? date( 'H:i', strtotime( $data->checkin ) ) : '-',
                'checkout'     => ( $data->checkout != '0000-00-00 00:00:00' ) ? date( 'M d, h:i:s A', strtotime( $data->checkout ) ) : '-',
                'checkout_raw' => ( $data->checkout != '0000-00-00 00:00:00' ) ? date( 'H:i', strtotime( $data->checkout ) ) : '-',
                'log_time'     => ( $data->log_time != null ) ? date( 'H:i:s', $data->log_time ) : '-',
            ];
        }, $results );

        wp_send_json( $results );
    }

    public function erp_hr_set_employee_log() {

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-nonce' ) ) {
            die( 'You are not allowed' );
        }

        global $wpdb;

        $bulk_data_parsed = array();

        $bulk_data        = ( isset( $_POST['bulk_data'] ) ) ? $_POST['bulk_data'] : null;
        $date             = ( isset( $_POST['date'] ) ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : null;

        parse_str( $bulk_data, $bulk_data_parsed );

        $error = [];
        $single_error = false;

        $last_time = 0;
        $last_time_str = '';

        $total_checkin_log =  count( $bulk_data_parsed['data_checkin'] );

        foreach ( $bulk_data_parsed['data_checkin'] as $key => $value ) {

            if ( $total_checkin_log > 1 ) {
                if ( ( $total_checkin_log - 1 ) != $key ) {
                    $checkout_log = $bulk_data_parsed['data_checkout'][$key];
                    if ( empty( $checkout_log ) ) {
                        $error[] =  esc_html__( $checkout_log . 'Checkout can not be blanked where checkin is ' . $value, 'erp-pro' ) ;
                        $single_error = true;
                    }
                }
            }


            $serilize_checkin  = strtotime( $value ) ;
            $serilize_checkout = strtotime( $bulk_data_parsed['data_checkout'][$key] ) ;
            if ( $serilize_checkin < $last_time ) {
                $val1    = date("g:i a", $serilize_checkin );
                $val2    = date("g:i a", strtotime($last_time_str));
                $error[] =  esc_html__( $val1 . ' should be greater from previous checkout ' . $val2, 'erp-pro' ) ;
                $single_error = true;
            } else {
                $last_time     = $serilize_checkout;
                $last_time_str = $bulk_data_parsed['data_checkout'][$key];
            }
        }


        for ( $i = 0; $i < count( $bulk_data_parsed['data_id'] ); $i++ ) {

            $id       = $bulk_data_parsed['data_id'][$i] ;
            $checkin  = ( isset( $bulk_data_parsed['data_checkin'][$i] ) ) ? $bulk_data_parsed['data_checkin'][$i] : '';
            $checkout = ( isset( $bulk_data_parsed['data_checkout'][$i] ) ) ? $bulk_data_parsed['data_checkout'][$i] : '';


            $checkin_with_date  = ( ! empty( $checkin ) ) ? $date . ' ' . $checkin : '0000-00-00 00:00:00';
            $checkout_with_date = ( ! empty( $checkout ) ) ? $date . ' ' . $checkout : '0000-00-00 00:00:00';

            if ( ! empty( $checkin ) && ! empty( $checkout ) ) {
                $log_time_checkin  = strtotime( $checkin_with_date );
                $log_time_checkout = strtotime( $checkout_with_date );
                if ( $log_time_checkout > $log_time_checkin ) {
                    $total_time = $log_time_checkout - $log_time_checkin;
                } else {
                    $error[]      = esc_html__( 'Checkout time must be larger where checkin is ' . $checkin , 'erp-pro' );
                    $single_error = true;
                    $total_time = null;
                }
            } else {
                $total_time = null;
            }

            if ( empty( $checkin ) && ! empty( $checkout ) ) {
                $error[]      = esc_html__( 'Checkin time can not be blanked where checkout is ' . $checkout , 'erp-pro' );
                $single_error = true;
            }

            if ( empty( $checkin ) ) {
                $error[]      = esc_html__( 'Checkin time can not be blanked ' , 'erp-pro' );
                $single_error = true;
            }

            if ( ! $single_error ) {
                $wpdb->update(
                  $wpdb->prefix. 'erp_attendance_log',
                  array(
                      'checkin'  => $checkin_with_date,
                      'checkout' => $checkout_with_date,
                      'time'     => $total_time
                  ),
                  array(
                      'id' => $id
                  )
                );
            }

        }

        if ( count( $error ) > 0 ) {
            wp_send_json_error( $error );
        } else {
            erp_attendance_purge_cache( ['list' => 'attendance'] );

            wp_send_json_success( esc_html__( 'Updated Successfully', 'erp-pro' ) );
        }
    }


    public function modify_attendance_active_days( $days, $id ) {
        $user_shift    = erp_attendance_get_user_shift( $id );
        if ( ! empty( $user_shift ) ) {

            $user_off_days  = ( property_exists( $user_shift, 'holidays' ) ) ? $user_shift->holidays : [];
            $days_container = [];

            foreach ( $days as $key => $value  ) {
                if ( in_array( $key, $user_off_days ) ) {
                    $days_container[$key] = 0;
                } else {
                    $days_container[$key] = 8;
                }
            }

            return $days_container;
        }
        return $days;
    }
}
