<?php

/**
 * Inserts employee remote work requests
 * 
 * @since 1.2.0
 *
 * @param array $args
 * 
 * @return mixed
 */
function erp_hr_employee_insert_remote_work_request( $args = [] ) {
    global $wpdb;

    $defaults = [
        'user_id'      => null,
        'reason'       => '',
        'other_reason' => '',
        'start_date'   => erp_current_datetime()->format( 'Y-m-d' ),
        'end_date'     => erp_current_datetime()->format( 'Y-m-d' ),
        'days'         => 0,
        'status'       => 'pending',
        'created_at'   => erp_current_datetime()->format( 'Y-m-d H:i:s' )
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['user_id'] ) ) {
        return new \WP_Error( 'invalid-user', __( 'Invalid request. User not found!', 'erp-pro' ) );
    }

    if ( empty( $args['start_date'] ) || empty( $args['end_date'] ) ) {
        return new \WP_Error( 'empty-date', __( 'Start date and end date cannot be empty.', 'erp-pro' ) );
    } else {
        $args['start_date'] = erp_current_datetime()->modify( $args['start_date'] )->format( 'Y-m-d' );
        $args['end_date']   = erp_current_datetime()->modify( $args['end_date'] )->format( 'Y-m-d' );

        $work_days          = erp_hr_get_work_days_duration( $args['start_date'], $args['end_date'] );

        if ( is_wp_error( $work_days ) ) {
            return $work_days;
        }

        $args['days']       = $work_days;
    }

    $exist_request = erp_hr_exists_remote_work_date_range( $args['user_id'], $args['start_date'], $args['end_date'] );

    if ( $exist_request ) {
        return new \WP_Error( 'invalid-date', __( 'You already have a request within the date range. Please select different dates.', 'erp-pro' ) );
    }

    $insert_id = $wpdb->insert(
        $wpdb->prefix . 'erp_hr_employee_remote_work_requests',
        [
            'user_id'    => $args['user_id'],
            'reason'     => $args['reason'],
            'start_date' => $args['start_date'],
            'end_date'   => $args['end_date'],
            'days'       => $args['days'],
            'status'     => $args['status'],
            'created_at' => $args['created_at'],
        ],
        [ '%d', '%s', '%s', '%s', '%d', '%s', '%s' ]
    );

    if ( ! is_wp_error( $insert_id ) && 'other' === $args['reason'] && ! empty( $args['other_reason'] ) ) {
        $other_reason = get_user_meta( $args['user_id'], 'erp_remote_work_other_reason', true );

        if ( empty( $other_reason ) ) {
            $other_reason = [];
        }

        $request_id= $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                FROM {$wpdb->prefix}erp_hr_employee_remote_work_requests
                WHERE `user_id` = %d
                AND `start_date` = %s
                AND `end_date` = %s",
                [ $args['user_id'], $args['start_date'], $args['end_date'] ]
            )
        );

        $other_reason[ $request_id ] = $args['other_reason'];

        update_user_meta( $args['user_id'], 'erp_remote_work_other_reason', $other_reason );
    }

    return $insert_id;
}

/**
 * Updates remote work requests
 * 
 * @since 1.2.0
 *
 * @param int|string|array $req_id
 * @param array $args
 * 
 * @return mixed
 */
function erp_hr_employee_update_remote_work_request( $req_id, $args = [] ) {
    global $wpdb;
    
    $editable     = [ 'start_date', 'end_date', 'status', 'reason' ];
    $other_reason = '';

    foreach ( $args as $key => $value ) {
        if ( ! in_array( $key, $editable, true ) ) {
            if ( 'other_reason' === $key ) {
                $other_reason = $value;
            }

            unset( $args[ $key ] );
        }
    }

    if ( isset( $args['status'] ) ) {
        if ( ! in_array( $args['status'], [ 'approved', 'rejected' ] ) ) {
            unset( $args['status'] );
        }
    }

    if ( empty( $args ) ) {
        return new \WP_Error( 'invalid-args', __( 'Update Unsuccessful', 'erp-pro' ) );
    }

    if ( isset( $args['start_date'] ) || isset( $args['end_date'] ) ) {
        if ( empty( $args['start_date'] ) || empty( $args['end_date'] ) ) {
            return new \WP_Error( 'empty-date', __( 'Start date and end date cannot be empty.', 'erp-pro' ) );
        } else {
            $args['start_date'] = erp_current_datetime()->modify( $args['start_date'] )->format( 'Y-m-d' );
            $args['end_date']   = erp_current_datetime()->modify( $args['end_date'] )->format( 'Y-m-d' );
    
            $date_validation    = erp_extract_dates( $args['start_date'], $args['end_date'] );
    
            if ( is_wp_error( $date_validation ) ) {
                return $date_validation;
            }

            $args['days']       = erp_hr_get_work_days_duration( $args['start_date'], $args['end_date'] );
        }
    }

    $defaults = [
        'updated_at' => erp_current_datetime()->format( 'Y-m-d H:i:s' ),
        'updated_by' => get_current_user_id()
    ];

    $args     = wp_parse_args( $args, $defaults );
    $updated  = [];

    if ( ! empty( $req_id ) ) {
        if ( is_array( $req_id) ) {
            $req_id   = implode( "','", $req_id );
        }

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, `user_id`, `status`, reason
                FROM {$wpdb->prefix}erp_hr_employee_remote_work_requests
                WHERE id IN ('{$req_id}')
                AND `status` = 'pending'"
            ),
            ARRAY_A
        );

        if ( ! empty( $items ) ) {
            foreach ( $items as $item ) {
                $wpdb->update( "{$wpdb->prefix}erp_hr_employee_remote_work_requests", $args, [ 'id' => $item['id'] ] );

                if ( 'other' === $args['reason'] ) {
                    if ( ! empty( $other_reason ) ) {
                        $reason = get_user_meta( $args['user_id'], 'erp_remote_work_other_reason', true );
                
                        if ( empty( $reason ) ) {
                            $reason = [];
                        }
    
                        $reason[ $item['id'] ] = $other_reason;
                
                        update_user_meta( $item['user_id'], 'erp_remote_work_other_reason', $reason );
                    }
                } else {
                    $reason = get_user_meta( $args['user_id'], 'erp_remote_work_other_reason', true );

                    if ( ! empty( $reason ) && isset( $reason[ $item['id'] ] ) ) {
                        unset( $reason[ $item['id'] ] );

                        update_user_meta( $item['user_id'], 'erp_remote_work_other_reason', $reason );
                    }
                }

                $updated[] = $wpdb->update_id;
            }
        }
    }

    return $updated;
}

/**
 * Retrieves employee remote work requests
 * 
 * @since 1.2.0
 *
 * @param array $args
 * 
 * @return array
 */
function erp_hr_employee_get_remote_work_requests( $args = [] ) {
    global $wpdb;

    $defaults = [
        'user_id'    => null,
        'status'     => '',
        'date'       => '',
        'order_by'   => 'id',
        'order'      => 'DESC',
        'number'     => 20,
        'offset'     => 1
    ];

    $args  = wp_parse_args( $args, $defaults );

    $query = "SELECT SQL_CALC_FOUND_ROWS req.*, req.* FROM {$wpdb->prefix}erp_hr_employee_remote_work_requests AS req";

    if ( ! empty( $args['user_id'] ) ) {
        $user_id = $args['user_id'];

        if ( is_array( $user_id ) ) {
            $user_id    = implode( "','", $user_id );
        }

        $sql['where'][] = "req.user_id IN ('{$user_id}')";
    }

    if ( ! empty( $args['status'] ) ) {
        $sql['where'][] = "req.status = %s";
        $values[]       = $args['status'];
    }

    if ( ! empty( $args['date'] ) && ! empty( $args['date']['start'] ) ) {
        $start_date     = erp_current_datetime()->modify( $args['date']['start'] )->format( 'Y-m-d' );

        $end_date       = ! empty( $args['date']['end'] ) 
                          ? erp_current_datetime()->modify( $args['date']['end'] )->format( 'Y-m-d' ) 
                          : erp_current_datetime()->format( 'Y-m-d' );

        $sql['where'][] = "(req.start_date BETWEEN %s AND %s OR req.end_date BETWEEN %s AND %s)";
        $values[]       = $start_date;
        $values[]       = $end_date;
        $values[]       = $start_date;
        $values[]       = $end_date;
    }

    if ( ! empty( $sql['where'] ) ) {
        $query .= ' WHERE ' . implode( ' AND ', $sql['where'] );
    }

    $query .= " ORDER BY req.{$args['order_by']} {$args['order']}";

    if ( ! empty( $args['number'] ) && -1 != $args['number'] ) {
        $query .= " LIMIT %d, %d";

        $values[] = ! empty( $args['offset'] ) ? $args['offset'] : 0;
        $values[] = $args['number'];
    }

    $results      = $wpdb->get_results( $wpdb->prepare( $query, $values ), ARRAY_A );
    $total_rows   = absint( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
    $request_data = [];

    foreach ( $results as $res ) {
        $employee    = new \WeDevs\ERP\HRM\Employee( $res['user_id'] );

        $emp_data    = [
            'id'   => $res['user_id'],
            'name' => $employee->get_full_name(),
            'url'  => add_query_arg(
                [ 'id' => $res['user_id'] ],
                admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view' )
            )
        ];

        $updated_by = [];

        if ( ! empty( $res['status'] ) ) {
            switch( $res['status'] ) {
                case 'pending' :
                    $status      = [
                        'id'    => 'pending',
                        'title' => __( 'Pending', 'erp-pro' )
                    ];

                    break;

                case 'approved' :
                case 'rejected' :
                    $status      = [
                        'id'    => $res['status'] ,
                        'title' => sprintf( __( '%s', 'erp-pro' ), ucfirst( $res['status'] ) )
                    ];

                    $user        = get_userdata( $res['updated_by'] );

                    $updated_by  = [
                        'id'   => $res['updated_by'],
                        'name' => $user->display_name
                    ];
                
                    break;
            }
        }

        if ( ! empty( $res['reason'] ) ) {
            $reason = [
                'id'    => $res['reason'],
                'title' => erp_hr_get_remote_work_reason( $res['reason'] )
            ];
        }

        $request_data[] = [
            'id'          => $res['id'],
            'type'        => [
                'id'      => 'remote_work',
                'title'   => __( 'Remote Work', 'erp-pro' )
            ],
            'status'      => $status,
            'duration'    => $res['days'],
            'reason'      => $reason,
            'employee'    => $emp_data,
            'updated_by'  => $updated_by,
            'start_date'  => erp_format_date( $res['start_date'] ),
            'end_date'    => erp_format_date( $res['end_date'] ),
            'created'     => erp_format_date( $res['created_at'] )
        ];
    }

    return [
        'data'        => $request_data,
        'total_items' => $total_rows
    ];
}

/**
 * Retrieves a specific remote work request
 * 
 * @since 1.2.0
 *
 * @param int|string $id
 * 
 * @return mixed
 */
function erp_hr_employee_get_remote_work_request( $id ) {
    global $wpdb;

    if ( empty( $id ) ) {
        return [];
    }

    $request = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
            FROM {$wpdb->prefix}erp_hr_employee_remote_work_requests
            WHERE id = %d",
            [ $id ]
        ),
        ARRAY_A
    );

    if ( is_wp_error( $request ) ) {
        return $request;
    }

    $request_data = [];

    if ( ! empty( $request ) ) {
        $employee    = new \WeDevs\ERP\HRM\Employee( $request['user_id'] );

        $emp_data    = [
            'id'   => $request['user_id'],
            'name' => $employee->get_full_name(),
            'url'  => add_query_arg(
                [ 'id' => $request['user_id'] ],
                admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view' )
            )
        ];

        $updated_by = [];

        if ( ! empty( $request['status'] ) ) {
            $status      = [
                'id'    => $request['status'] ,
                'title' => sprintf( __( '%s', 'erp-pro' ), ucfirst( $request['status'] ) )
            ];

            if ( $request['status'] != 'pending' )

            $user        = get_userdata( $request['updated_by'] );

            $updated_by  = [
                'id'   => $request['updated_by'],
                'name' => $user->display_name
            ];
        }

        if ( ! empty( $request['reason'] ) ) {
            $reason_details = get_user_meta( $request['user_id'], 'erp_remote_work_other_reason', true );
            
            $reason         = [
                'id'     => $request['reason'],
                'title'  => erp_hr_get_remote_work_reason( $request['reason'] ),
                'others' => ( 'other' === $request['reason'] && ! empty( $reason_details[ $request['id'] ] ) ) ? sprintf( __( '%s', 'erp-pro' ), $reason_details[ $request['id'] ] ) : ''
            ];
        }

        $request_data = [
            'id'          => $request['id'],
            'type'        => [
                'id'      => 'remote_work',
                'title'   => __( 'Remote Work', 'erp-pro' )
            ],
            'status'      => $status,
            'duration'    => $request['days'],
            'reason'      => $reason,
            'employee'    => $emp_data,
            'updated_by'  => $updated_by,
            'start_date'  => $request['start_date'],
            'end_date'    => $request['end_date'],
            'created'     => $request['created_at']
        ];
    }

    return $request_data;
}

/**
 * Deletes remote work request
 * 
 * @since 1.2.0
 *
 * @param int|string|array $id
 * @param string $colname
 * 
 * @return mixed
 */
function erp_hr_delete_remote_work_request_by( $id, $colname = 'id' ) {
    if ( 'id' !== $colname && 'user_id' !== $colname ) {
        return [];
    }
    
    global $wpdb;

    $args  = [];
    $query = "DELETE FROM {$wpdb->prefix}erp_hr_employee_remote_work_requests";

    if ( ! empty( $id ) ) {
        if ( is_array( $id ) ) {
            $id = implode( "','", $id );
        }

        $sql['where'][] = "{$colname} IN ('{$id}')";
    }

    if ( ! empty( $sql['where'] ) ) {
        $query .= ' WHERE ' . implode( ' AND ', $sql['where'] );
    }

    return $wpdb->query( $wpdb->prepare( $query, $args ) );
}

/**
 * Retrieves remote work request reasons
 * 
 * @since 1.2.0
 *
 * @param string $selected
 * 
 * @return string|array
 */
function erp_hr_get_remote_work_reason( $selected = false ) {
    $reasons = apply_filters( 'erp_hr_remote_work_reason', [
        'sickness'          => __( 'Personal health reason', 'erp-pro' ),
        'social_distancing' => __( 'To keep social distancing', 'erp-pro' ),
        'outdoor_work'      => __( 'Visiting outdoor client', 'erp-pro' ),
        'family_time'       => __( 'To take care of family members', 'erp-pro' ),
        'other'             => __( 'Others', 'erp-pro' ),
    ] );

    return ( $selected && array_key_exists( $selected, $reasons ) ) ? $reasons[ $selected ] : $reasons;
}

/**
 * Determines the work days duration between two dates
 * 
 * @since 1.2.0
 *
 * @param string $start_date
 * @param string $end_date
 * 
 * @return int
 */
function erp_hr_get_work_days_duration( $start_date, $end_date ) {
    $between_dates = erp_extract_dates( $start_date, $end_date );

    if ( is_wp_error( $between_dates ) ) {
        return $between_dates;
    }

    $work_days = erp_hr_get_work_days();
    $holidays  = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );
    $duration  = 0;

    foreach ( $between_dates as $date ) {
        $key       = strtolower( date( 'D', strtotime( $date ) ) );
        $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

        if ( ! $is_holidy && ! in_array( $date, $holidays ) ) {
            $duration++;
        }
    }

    return $duration;
}

/**
 * Checks if an employee is active
 * 
 * @since 1.2.0
 *
 * @param int|string $user_id
 * 
 * @return boolean
 */
function erp_hr_is_employee_active( $user_id = null ) {
    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    if ( current_user_can( 'employee' ) ) {
        $employee = \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id )->where( 'status', 'active' )->first();

        if ( ! empty( $employee ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Checks if any remote work request
 * by a specific employee exits 
 * between a specific date range
 * 
 * @since 1.2.0
 *
 * @param int|string $user_id
 * @param string $start_date
 * @param string $end_date
 * @param int|string $req_id
 * 
 * @return boolean
 */
function erp_hr_exists_remote_work_date_range( $user_id, $start_date, $end_date, $req_id = null ) {
    global $wpdb;

    $start_date = erp_current_datetime()->modify( $start_date )->format( 'Y-m-d' );
    $end_date   = erp_current_datetime()->modify( $end_date )->format( 'Y-m-d' );

    $query      = "SELECT id
                    FROM {$wpdb->prefix}erp_hr_employee_remote_work_requests
                    WHERE `user_id` = %d
                    AND (
                        `start_date` BETWEEN %s AND %s
                        OR `end_date` BETWEEN %s AND %s
                    )";

    $args       = [ $user_id, $start_date, $end_date, $start_date, $end_date ];

    if ( ! empty( $req_id ) ) {
        $query .= " AND id != %d";
        $args[] = $req_id;
    }

    $request    = $wpdb->get_var( $wpdb->prepare( $query, $args ) );

    return ! empty( $request );
}