<?php

/**
 * Inserts employee resign requests
 * 
 * @since 1.2.0
 *
 * @param array $args
 * 
 * @return mixed
 */
function erp_hr_employee_insert_resign_request( $args = [] ) {
    global $wpdb;

    $defaults = [
        'user_id'    => null,
        'reason'     => '',
        'date'       => erp_current_datetime()->format( 'Y-m-d' ),
        'status'     => 'pending',
        'created_at' => erp_current_datetime()->format( 'Y-m-d H:i:s' )
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['user_id'] ) ) {
        return new \WP_Error( 'invalid-user', __( 'Invalid request. User not found!', 'erp-pro' ) );
    }

    $insert_id = $wpdb->insert( $wpdb->prefix . 'erp_hr_employee_resign_requests', $args, [ '%d', '%s', '%s', '%s', '%s' ] );

    return $insert_id;
}

/**
 * Updates resign requests
 * 
 * @since 1.2.0
 *
 * @param int|string|array $req_id
 * @param array $args
 * 
 * @return mixed
 */
function erp_hr_employee_update_resign_request( $req_id, $args = [] ) {
    global $wpdb;
    
    $editable = [ 'reason', 'date', 'status' ];

    foreach ( $args as $key => $arg ) {
        if ( ! in_array( $key, $editable, true ) ) {
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
            "SELECT id, `status`
            FROM {$wpdb->prefix}erp_hr_employee_resign_requests
            WHERE id IN ('{$req_id}')
            AND `status` = 'pending'",
            ARRAY_A
        );

        foreach ( $items as $key => $item ) {
            if ( $item['status'] !== 'pending' ) {
                unset( $items[ $key ] );
            }
        }

        if ( ! empty( $items ) ) {
            foreach ( $items as $item ) {
                $wpdb->update( "{$wpdb->prefix}erp_hr_employee_resign_requests", $args, [ 'id' => $item['id'] ] );

                if ( ! is_wp_error( $wpdb->update_id ) ) {
                    $updated[] = $wpdb->update_id;
                } 
            }
        }
    }

    return $updated;
}

/**
 * Retrieves employee resign requests
 * 
 * @since 1.2.0
 *
 * @param array $args
 * 
 * @return array
 */
function erp_hr_employee_get_resign_requests( $args = [] ) {
    global $wpdb;

    $defaults = [
        'user_id'  => null,
        'status'   => '',
        'date'     => '',
        'order_by' => 'id',
        'order'    => 'DESC',
        'number'   => 20,
        'offset'   => 1
    ];

    $args  = wp_parse_args( $args, $defaults );

    $query = "SELECT SQL_CALC_FOUND_ROWS req.*, req.* FROM {$wpdb->prefix}erp_hr_employee_resign_requests AS req";

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

        $sql['where'][] = "req.date BETWEEN %s AND %s";
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
                'title' => erp_hr_get_resignation_reason( $res['reason'] )
            ];
        }

        $request_data[] = [
            'id'          => $res['id'],
            'type'        => [
                'id'    => 'resigned',
                'title' => __( 'Resignation', 'erp-pro' )
            ],
            'status'      => $status,
            'reason'      => $reason,
            'employee'    => $emp_data,
            'updated_by'  => $updated_by,
            'date'        => $res['date'],
            'created'     => $res['created_at']
        ];
    }

    return [
        'data'        => $request_data,
        'total_items' => $total_rows
    ];
}

/**
 * Deletes resign request
 * 
 * @since 1.2.0
 *
 * @param int|string|array $id
 * @param string $colname
 * 
 * @return mixed
 */
function erp_hr_delete_resign_request_by( $id, $colname = 'id' ) {
    if ( 'id' !== $colname && 'user_id' !== $colname ) {
        return [];
    }
    
    global $wpdb;

    $query = "DELETE FROM {$wpdb->prefix}erp_hr_employee_resign_requests";

    if ( ! empty( $id ) ) {
        if ( is_array( $id ) ) {
            $id = implode( "','", $id );
        }

        $sql['where'][] = "{$colname} IN ('{$id}')";
    }

    if ( ! empty( $sql['where'] ) ) {
        $query .= ' WHERE ' . implode( ' AND ', $sql['where'] );
    }

    return $wpdb->query( $query );
}

/**
 * Retrieves a specific request
 * 
 * @since 1.2.0
 *
 * @param int|string $id
 * 
 * @return mixed
 */
function erp_hr_employee_get_resign_request( $id ) {
    global $wpdb;

    $request = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
            FROM {$wpdb->prefix}erp_hr_employee_resign_requests
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

            if ( $request['status'] != 'pending' ) {
                $user        = get_userdata( $request['updated_by'] );

                $updated_by  = [
                    'id'   => $request['updated_by'],
                    'name' => $user->display_name
                ];
            }
        }

        if ( ! empty( $request['reason'] ) ) {
            $reason = [
                'id'    => $request['reason'],
                'title' => erp_hr_get_resignation_reason( $request['reason'] )
            ];
        }

        $request_data = [
            'id'          => $request['id'],
            'type'        => [
                'id'      => 'resigned',
                'title'   => __( 'Resignation', 'erp-pro' )
            ],
            'status'      => $status,
            'reason'      => $reason,
            'employee'    => $emp_data,
            'updated_by'  => $updated_by,
            'date'        => $request['date'],
            'created'     => $request['created_at']
        ];
    }

    return $request_data;
}

/**
 * Checks if resign request exists for a certain employee and status
 *
 * @since 1.2.0
 * 
 * @param int|string $user_id
 * @param int|string $status
 * 
 * @return boolean
 */
function erp_hr_employee_exists_resign_request( $user_id, $status = 'pending' ) {
    global $wpdb;

    $request = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id
            FROM {$wpdb->prefix}erp_hr_employee_resign_requests
            WHERE `user_id` = %d
            AND `status` = %s",
            [ $user_id, $status ]
        )
    );

    return ! empty( $request );
}

/**
 * Retrieves default email body for resignation
 * 
 * @since 1.2.0
 * 
 * @param array $args
 *
 * @return string
 */
function erp_hr_get_default_resign_email_body( $args = [] ) {
    $company  = new \WeDevs\ERP\Company();

    $defaults = [
        'designation' => 'Employee',
        'date'        => erp_current_datetime()->format( 'Y-m-d' ),
        'company'     => $company->name,
        'contact'     => ''
    ];

    $args     = wp_parse_args( $args, $defaults );

    $position = ! empty( $args['designation'] ) ? $args['designation'] : 'my job';
    $company  = ! empty( $args['company'] ) ? $args['company'] : 'your company';

    $body     = "</br>Please accept this message as formal notice that I am resigning from the position of {$position}. My last day of employment will be {$args['date']}.</br></br>
            I appreciate all the opportunities I have been given working at {$company}. Working on your team has allowed me to develop my professional skills. I am happy to provide assistance during this transition.</br></br>
            I wish you and the company the very best going forward.</br></br>";

    if ( ! empty( $args['contact'] ) ) {
        $body .= " If you need anything please don’t hesitate to reach me at {$args['contact']}.";
    }

    return $body;
}

/**
 * Get resignation reasons
 * 
 * @since 1.2.1
 *
 * @return array
 */
function erp_hr_get_resignation_reason( $selected = null ) {
    $reason = apply_filters( 'erp_hr_resignation_reason', [
        'better_employment'     => __( 'Better Employment Conditions', 'erp' ),
        'career_prospect'       => __( 'Career Prospect', 'erp' ),
        'dissatisfaction'       => __( 'Dissatisfaction with the job', 'erp' ),
        'higher_pay'            => __( 'Higher Pay', 'erp' ),
        'better_opportunity'    => __( 'Better Opportunity', 'erp' ),
        'other_employement'     => __( 'Other Employment', 'erp' ),
        'career_path'           => __( 'Need Change in career Path', 'erp' ),
        'personality_conflicts' => __( 'Personality Conflicts', 'erp' ),
        'relocation'            => __( 'Relocation', 'erp' ),
        'retirement'            => __( 'Retirement', 'erp' ),
        'personal'              => __( 'Personal Reason' )
    ] );

    if ( $selected ) {
        return ( isset( $reason[ $selected ] ) ) ? $reason[ $selected ] : '';
    }

    return $reason;
}