<?php

namespace WeDevs\Attendance\Api;

use WeDevs\ERP\API\Rest_Controller;
use WeDevs\ERP\HRM\Employee;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use DateTime;
use parseCSV;

class AttendanceController extends Rest_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'hrm/attendance';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/shifts', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_shifts' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_shift' ],
                'args'                => [
                    'name'       => [ 'required' => true ],
                    'start_time' => [ 'required' => true ],
                    'end_time'   => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/shifts/(?P<shift_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_shift' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_shift' ],
                'args'                => [
                    'name'       => [ 'required' => true ],
                    'start_time' => [ 'required' => true ],
                    'end_time'   => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_shift' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/shifts/(?P<shift_id>[\d]+)/assign', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_assigned_employees_from_shift' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'assign_to_shift' ],
                'args'                => [
                    'users_id'   => [ 'required' => true ],
                    'shift_id'   => [ 'required' => true ],
                    'start_date' => [ 'required' => true ],
                    'end_date'   => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/shifts/delete/(?P<shifts_id>[\d,?]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'bulk_remove_shift' ],
                'args'                => [
                    'shifts_id'   => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/shifts/(?P<shift_id>[\d]+)/assign/delete/(?P<users_id>[\d,?]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'bulk_remove_assigned' ],
                'args'                => [
                    'users_id' => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/logs', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'employee_check_in_out' ],
                'args'                => [
                    'user_id'    => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/logs/(?P<user_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_log' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/logs/(?P<user_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_log' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/reports/(?P<user_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_single_employee_attendances' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/hrentry', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_all_date_attendance' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/hrentry/(?P<date>[\w\-]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_single_date_attendance' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_attendance_hr_entry' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'save_attendance_hr_entry' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/hrentry/delete/(?P<dates>[\w\-?,?]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'bulk_remove_attendance' ],
                'args'                => [
                    'dates'   => [ 'required' => true ]
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        // others
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/employees_user_id', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_all_employees_user_id' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/dept_employees_user_id', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_dept_employees_user_id' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/degn_employees_user_id', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_degn_employees_user_id' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/get_employees', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employees' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/bulk_shift_assign', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'bulk_shift_assign' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/att_data_migration', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'att_data_migration' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/att_data_migration_total', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'att_data_migration_total' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/update_grace_time', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'update_grace_time' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        /*********** HR Frontend API Start **************/

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/self-attendance', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'self_attendance' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_attendance' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );


        /*********** HR Frontend API End **************/

        /*********** Export Import Start **********/

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/import_export_file', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'export_file' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'import_file' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        /*********** Export Import Start **********/

    }

    /**
     * Create shift
     *
     * @since 2.0.0
     *
     * @param $request
     *
     * @return WP_Error
     */
    public function create_shift( \WP_REST_Request $request ) {
        $name     = ! empty( $request['name'] ) ? $request['name'] : null;
        $holidays = ! empty( $request['holidays'] ) ? $request['holidays'] : [];

        $shift_id = erp_attendance_insert_shift( $request['start_time'], $request['end_time'], $name, $holidays );

        if ( is_wp_error( $shift_id ) ) {
            return $shift_id;
        }

        do_action( 'erp_hr_log_att_shift_add', $name );

        $response = rest_ensure_response( ['id' => $shift_id] );

        return $response;
    }

    /**
     * View all shifts
     *
     * @since 2.0.0
     *
     * @param $request
     *
     * @return WP_Error
     */
    public function get_shifts( \WP_REST_Request $request ) {
        $args = [
            'limit' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $results = erp_attendance_get_shifts($args);

        $args['count'] = true;
        $total_items   = erp_attendance_get_shifts( $args );

        $shifts = [];
        foreach ( $results as $result ) {
            $result->holidays = \unserialize($result->holidays);

            $shifts[] = $result;
        }

        $response = rest_ensure_response( $shifts );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        return $response;
    }

    /**
     * View a shift
     *
     * @since 2.0.0
     *
     * @param $request
     *
     * @return WP_Error
     */
    public function get_shift( \WP_REST_Request $request ) {
        $shift = (array) erp_attendance_get_shift( intval($request['shift_id']) );
        $shift = (object) array_map( 'maybe_unserialize', $shift );

        $response = rest_ensure_response( $shift );

        return $response;
    }

    /**
     * Update a shift
     *
     * @since 2.0.0
     *
     * @param $request
     *
     * @return WP_Error
     */
    public function update_shift( \WP_REST_Request $request ) {
        $name     = ! empty( $request['name'] ) ? $request['name'] : null;
        $holidays = ! empty( $request['holidays'] ) ? $request['holidays'] : [];
        $status   = ! empty( $request['status'] ) ? intval( $request['status'] ) : 0;

        $old_data = erp_attendance_get_shift( absint( $request['shift_id'] ) );

        $shift = erp_attendance_update_shift( $request['shift_id'], $request['start_time'], $request['end_time'], $name, $holidays, $status );

        if ( is_wp_error( $shift ) ) {
            return $shift;
        }

        do_action( 'erp_hr_log_att_shift_edit', $old_data, $request['shift_id'] );

        $response = rest_ensure_response( $shift );

        return $response;
    }

    public function delete_shift( \WP_REST_Request $request ) {
        $shift_id = (int) $request['shift_id'];
        $shift    = erp_attendance_get_shift( $shift_id );

        erp_att_delete_shift( $shift_id );

        do_action( 'erp_hr_log_att_shift_del', $shift );

        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    public function bulk_remove_shift( \WP_REST_Request $request ) {
        $shifts_id = $request['shifts_id'];

        foreach ( explode( ',', $shifts_id ) as $sid ) {
            $shifts[] = erp_attendance_get_shift( absint( $sid ) );
        }

        erp_att_delete_shifts( $shifts_id );

        foreach ( $shifts as $shift ) {
            do_action( 'erp_hr_log_att_shift_del', $shift );
        }

        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    public function bulk_remove_assigned( \WP_REST_Request $request ) {
        $shift_id = $request['shift_id'];
        $users_id = $request['users_id'];

        erp_att_remove_employees_from_shift( $shift_id, $users_id );

        foreach ( explode( ',', $users_id ) as $uid ) {
            do_action( 'erp_hr_log_remove_from_shift', $shift_id, $uid );
        }

        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    public function bulk_remove_attendance( \WP_REST_Request $request ) {
        $dates = $request['dates'];

        erp_att_remove_hr_entry_attendances( $dates );

        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    function get_assigned_employees_from_shift( \WP_REST_Request $request ) {
        $args = [
            'shift_id' => (int) $request['shift_id'],
            'limit'    => $request['per_page'],
            'offset'   => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $results = erp_attendance_get_shift_users($args);

        $args['count'] = true;
        $total_items   = erp_attendance_get_shift_users( $args );

        $response = rest_ensure_response( $results );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        return $response;
    }

    function assign_to_shift( \WP_REST_Request $request ) {
        $overwrite  = (bool) ! empty( $request['overwrite'] ) ? $request['overwrite'] : false;
        $shift_id   = $request['shift_id'];
        $start_date = $request['start_date'];
        $end_date   = $request['end_date'];

        $errors = [];

        foreach ( $request['users_id'] as $user_id ) {
            erp_attendance_insert_shifting_for_user( $shift_id, $user_id, $start_date, $end_date, $overwrite );
            erp_attendance_assign_shift( $user_id, $shift_id, $overwrite );

            do_action( 'erp_hr_log_assign_to_shift', $shift_id, $user_id );
        }

        $response = rest_ensure_response( true );

        return $response;
    }

    function employee_check_in_out( \WP_REST_Request $request ) {
        $user_id   = (int) $request['user_id'];
        $timestamp = ! empty( $request['timestamp'] ) ? $request['timestamp'] : null;

        if ( erp_att_has_restriction() ) {
            $client_ip = erp_get_client_ip();

            if ( ! erp_att_is_ip_allowed( $client_ip ) ) {
                return new WP_Error( 'rest_unauthorized_ip',  sprintf( __( 'You are not allowed to %s from this IP', 'erp-pro' ),$type), array( 'status' => 404 ) );
            }
        }

        $entry_id = erp_attendance_punch( $user_id, $timestamp );

        $response = rest_ensure_response( $entry_id );

        return $response;
    }

    function get_employee_log( \WP_REST_Request $request ) {
        $user_id = (int) $request['user_id'];

        $log = erp_attendance_get_single_user_log( $user_id );

        $response = rest_ensure_response( $log );

        return $response;
    }

    function get_single_employee_attendances( \WP_REST_Request $request ) {
        $user_id    = (int) $request['user_id'];
        $start_date = ! empty( $request['start_date'] ) ? $request['start_date'] : date('Y-m-01');
        $end_date   = ! empty( $request['end_date'] ) ? $request['end_date'] : null;

        $report = erp_get_emp_attendance_report( $user_id, $start_date, $end_date );

        $response = rest_ensure_response( $report['attendances'] );

        return $response;
    }

    public function get_all_date_attendance( \WP_REST_Request $request ) {
        $args = [
            'limit'      => $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'order'      => 'DESC',
            'start_date' => $request['start_date'],
            'end_date'   => $request['end_date']
        ];

        $results = erp_att_get_all_attendance($args);

        $args['count'] = true;
        $total_items   = erp_att_get_all_attendance( $args );

        $response = rest_ensure_response( $results );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        return $response;
    }

    public function get_single_date_attendance( \WP_REST_Request $request ) {
        //$report = erp_att_get_single_attendance( $request );

        $report             = erp_att_get_single_day_attendance( $request );

        $report_arr = [];

        foreach( $report as $rprt ) {
            $rprt['employee_name'] = erp_hr_get_employee_name( $rprt['user_id'] );
            $report_arr[] = $rprt;
        }

        $report = $report_arr;

        $request['count']   = true;
        $total_items        = count( erp_att_get_single_day_attendance( $request ) );
        $response           = rest_ensure_response( $report );
        $response           = $this->format_collection_response( $response, $request, (int) $total_items );

        return $response;
    }

    public function save_attendance_hr_entry( \WP_REST_Request $request ) {

        $attendances = $request['attendances'];
        $date = $request['date'];
        save_attendance_by_hr_mnanager( $attendances, $date );
    }

    // others
    public function get_all_employees_user_id( \WP_REST_Request $request ) {
        global $wpdb;
        $users_id = erp_hr_get_employees(['number' => -1]);

        $response = rest_ensure_response( $users_id );

        $table   = "{$wpdb->prefix}erp_attendance_shift_user";
        $result  = $wpdb->get_results( "SELECT * FROM {$table} WHERE shift_id = {$request['shift_id']}" );
        $result  = wp_list_pluck( $result, 'user_id' );

        $filter_data = [];

        foreach ( $response->data as $res){
            if ( in_array( $res->user_id, $result ) ) {
                $filter_data[] = $res;
            }
        }

        $response->data = $filter_data;

        return $response;
    }

    public function get_dept_employees_user_id( \WP_REST_Request $request ) {
        $data = [];
        $departments = ! empty( $request['ids'] ) ? (array) $request['ids'] : null;

        foreach ( $departments as $department ) {
            $data[] = erp_hr_get_employees([
                 'department' => $department,
                 'number' => '-1'
            ]);
        }

        $response = rest_ensure_response( erp_array_flatten($data) );

        return $response;
    }

    public function get_degn_employees_user_id( \WP_REST_Request $request ) {
        $data = [];
        $designations = ! empty( $request['ids'] ) ? (array) $request['ids'] : null;

        foreach ( $designations as $designation ) {
            $data[] = erp_hr_get_employees([
                 'designation' => $designation,
                 'number' => '-1'
            ]);
        }

        $response = rest_ensure_response( erp_array_flatten($data) );

        return $response;
    }

    public function prepare_item_for_response( Employee $item, \WP_REST_Request $request = null, $additional_fields = [] ) {
        $default = [
            'user_id'         => '',
            'employee_id'     => '',
            'first_name'      => '',
            'middle_name'     => '',
            'last_name'       => '',
            'full_name'       => '',
            'location'        => '',
            'date_of_birth'   => '',
            'pay_rate'        => '',
            'pay_type'        => '',
            'hiring_source'   => '',
            'hiring_date'     => '',
            'type'            => '',
            'status'          => '',
            'other_email'     => '',
            'phone'           => '',
            'work_phone'      => '',
            'mobile'          => '',
            'address'         => '',
            'gender'          => '',
            'marital_status'  => '',
            'nationality'     => '',
            'driving_license' => '',
            'hobbies'         => '',
            'user_url'        => '',
            'description'     => '',
            'street_1'        => '',
            'street_2'        => '',
            'city'            => '',
            'country'         => '',
            'state'           => '',
            'postal_code'     => '',
        ];

        $data = wp_parse_args( $item->get_data( array(), true ), $default );

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) && ! empty( $item->get_department() ) ) {
                $data['department'] = Department::find( $item->get_department() );
            }

            if ( in_array( 'designation', $include_params ) && ! empty( $item->get_designation() ) ) {
                $data['designation'] = Designation::find( $item->get_designation() );
            }

            if ( in_array( 'reporting_to', $include_params ) && $item->get_reporting_to() ) {
                $reporting_to = new Employee( $item->get_reporting_to() );
                if ( $reporting_to->is_employee() ) {
                    $data['reporting_to'] = $this->prepare_item_for_response( $reporting_to );
                }
            }

            if ( in_array( 'avatar', $include_params ) ) {
                $data['avatar_url'] = $item->get_avatar_url( 80 );
            }

            if ( in_array( 'roles', $include_params ) ) {
                $data['roles'] = $item->get_roles();
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    public function get_employees( \WP_REST_Request $request ) {
        global $wpdb;

        $args = [
            'number'      => $request['per_page'],
            'offset'      => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'status'      => ( $request['status'] ) ? $request['status'] : 'active',
            'department'  => ( $request['department'] ) ? $request['department'] : '-1',
            'designation' => ( $request['designation'] ) ? $request['designation'] : '-1',
            'location'    => ( $request['location'] ) ? $request['location'] : '-1',
            'type'        => ( $request['type'] ) ? $request['type'] : '-1',
            's'           => ( $request['s'] ) ? $request['s'] : '',
            'shift_id'    => ( $request['shift_id'] ) ? $request['shift_id'] : '',
        ];

        if( isset ( $request['find'] ) && $request['find'] == 'all' ) {
            $args['number'] = -1;
        }

        $items = erp_hr_get_employees( $args );

        $args['count'] = true;
        $total_items   = erp_hr_get_employees( $args );

        $formatted_items = [];
        foreach ( $items as $item ) {

            $get_shift = $wpdb->get_row( "SELECT easu.shift_id as shift_id, easu.user_id as user_id, eas.name as shift_name, eas.status as shift_status FROM `{$wpdb->prefix}erp_attendance_shift_user` as easu LEFT JOIN {$wpdb->prefix}erp_attendance_shifts as eas ON easu.shift_id = eas.id WHERE easu.user_id = {$item->user_id}" );

            $additional_fields = [
                'shift' => ! empty( $get_shift->shift_name ) ? ( (int) $get_shift->shift_status === 1 ? $get_shift->shift_name : "{$get_shift->shift_name} (inactive)" ) : '-'
            ];

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }
        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        if ( isset( $request['shift_id'] ) ) {
            $table = "{$wpdb->prefix}erp_attendance_shift_user";
            $result = $wpdb->get_results("SELECT * FROM {$table} WHERE shift_id = {$request['shift_id']}");
            $result = wp_list_pluck($result, 'user_id');
            $filter_data = [];
            foreach ($response->data as $res) {
                if (in_array($res['user_id'], $result)) {
                    $filter_data[] = $res;
                }
            }
            $response->data = $filter_data;
        }

        return $response;
    }

    public function bulk_shift_assign ( \WP_REST_Request $request ) {
        global $wpdb;

        $params =  $request->get_params();
        $employee_ids       = $params['params']['employee_ids'];
        $selected_shift     = $params['params']['selected_shift'];

        $table              = $wpdb->prefix . 'erp_attendance_shift_user';

        foreach ( $employee_ids as $emp_id ) {

            $wpdb->delete( $table, [
                'user_id' => $emp_id
            ] );

            $wpdb->insert( $table, [
                'user_id'   => $emp_id,
                'shift_id'  => $selected_shift
            ] );

            do_action( 'erp_hr_log_assign_to_shift', $selected_shift, $emp_id );
        }
    }

    public function att_data_migration ( \WP_REST_Request $request ) {
        global $wpdb;

        $items_per_page             = 100;
        $page                       = $request->get_param( 'params' )['page'];

        $offset                     = ($page - 1) * $items_per_page;
        $sql                        = "SELECT * FROM {$wpdb->prefix}erp_attendance ORDER BY id DESC LIMIT " . $offset . "," . $items_per_page ;
        $previous_data              = $wpdb->get_results( $sql );
        $previuos_data_ids          = wp_list_pluck( $previous_data, 'id' );
        $previous_data_option_ids   = get_option( 'previous_attr_data_option_ids', [] );

        $if_exists                  = count ( array_intersect( $previuos_data_ids, $previous_data_option_ids ) );

        if ( $if_exists == 0 ) {
            $previous_data_merge        = array_merge( $previous_data_option_ids, $previuos_data_ids );
            update_option( 'previous_attr_data_option_ids', $previous_data_merge );
            foreach ( $previous_data as $pd ) {

                $get_shift = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}erp_attendance_shifts WHERE start_time = '" .$pd->shift_start_time."' AND end_time = '". $pd->shift_end_time . "'" );

                $date       = $pd->date;
                $user_id    = $pd->user_id;
                $shift_id   = $get_shift->id;
                $start_time = $date . ' ' . $pd->shift_start_time;
                $end_time   = $date . ' ' . $pd->shift_end_time;
                $present    = ( $pd->present == 'yes' ) ? 1 : null;
                $checkin    = ( !empty( $pd->checkin ) ) ? $date . ' ' . $pd->checkin : null;
                $checkout   = ( !empty( $pd->checkout ) ) ? $date . ' ' . $pd->checkout : null;
                $time       = ( !empty( $pd->checkin ) && !empty( $pd->checkout ) ) ? strtotime( $pd->checkout ) - strtotime( $pd->checkin ) : null;


                $d_checkin      = new DateTime( $pd->checkin );
                $d_checkout     = new DateTime( $pd->checkout );
                $d_start_time   = new DateTime( $pd->shift_start_time );
                $d_end_time     = new DateTime( $pd->shift_end_time );



                $wpdb->insert( $wpdb->prefix . 'erp_attendance_date_shift', [
                    'date'          => $date,
                    'user_id'       => $user_id,
                    'shift_id'      => $shift_id,
                    'start_time'    => $start_time,
                    'end_time'      => date('Y-m-d H:i:s', strtotime( $start_time . ' + 23 hours' ) ),
                    'present'       => $present,
                    'late'          => ( $d_checkin > $d_start_time && $present != null ) ? 1 : null,
                    'early_left'    => ( $d_end_time > $d_checkout  && $present != null ) ? 1000 : null,
                ]);

                if ( $present != null ) {
                    $wpdb->insert( $wpdb->prefix . 'erp_attendance_log', [
                        'user_id'       => $user_id,
                        'date_shift_id' => $wpdb->insert_id,
                        'checkin'       => $checkin,
                        'checkout'      => ( $checkout != null ) ? $checkout : '0000-00-00 00:00:00',
                        'time'          => $time,
                    ]);
                }
            }

            return $previous_data;
        }
        return $previuos_data_ids;
    }

    public function att_data_migration_total ( \WP_REST_Request $request ) {
        global $wpdb;

        $check_if_mig_table_exist = $wpdb->get_var( "SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '". DB_NAME ."') AND (TABLE_NAME = '". $wpdb->prefix ."erp_attendance')" );

        // Check if table exist before migration
        if ( $check_if_mig_table_exist == 0 ) {
            return [];
        }

        $sql    = "SELECT COUNT(*) as total FROM {$wpdb->prefix}erp_attendance";
        $previous_data = $wpdb->get_results( $sql );
        $previous_shift_sql = "SELECT shift_title ,shift_start_time, shift_end_time, TIME_TO_SEC(TIMEDIFF(shift_end_time,shift_start_time)) as duration FROM {$wpdb->prefix}erp_attendance group by shift_start_time, shift_end_time";
        $previous_shift_data = $wpdb->get_results( $previous_shift_sql );
        foreach( $previous_shift_data as $psd ) {
            $data = [
                'name' => ( !empty( $psd->shift_title ) ) ? $psd->shift_title : $psd->shift_start_time . '-' . $psd->shift_end_time . '-Shift',
                'start_time' => $psd->shift_start_time,
                'end_time' => $psd->shift_end_time,
                'duration' => $psd->duration,
                'holidays' => '',
            ];

            $check_if_exist_data_sql = "
                  SELECT COUNT(*) FROM {$wpdb->prefix}erp_attendance_shifts
                    WHERE
                    name = '" . $data['name'] . "' AND
                    start_time = '" . $data['start_time'] . "' AND
                    end_time = '" . $data['end_time'] . "' AND
                    duration = " . $data['duration'];

            $if_exist = $wpdb->get_var( $check_if_exist_data_sql );
            if ( $if_exist == 0 ) {
                $wpdb->insert( $wpdb->prefix.'erp_attendance_shifts', $data );
            }
        }

        $get_users_shift_sql = "SELECT * FROM `{$wpdb->prefix}erp_attendance` WHERE id IN (SELECT MAX(id) FROM `{$wpdb->prefix}erp_attendance` GROUP BY `user_id`)";
        $get_users_shift = $wpdb->get_results( $get_users_shift_sql );
        foreach ( $get_users_shift as $gus ) {
            $user_id            = $gus->user_id;
            $shift_start_time   = $gus->shift_start_time;
            $shift_end_time     = $gus->shift_end_time;
            if ( !empty( $gus->shift_title ) ) {
                $shift_title = $gus->shift_title;
            } else {
                $shift_title = $gus->shift_start_time . '-' . $gus->shift_end_time . '-Shift';
            }

            $get_shift_data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}erp_attendance_shifts WHERE name = '{$shift_title}' AND start_time='{$shift_start_time}' AND end_time='{$shift_end_time}'" );
            $wpdb->replace(
                $wpdb->prefix.'erp_attendance_shift_user',
                array(
                    'shift_id'   => $get_shift_data->id,
                    'user_id'    => $user_id
                )
            );

        }

        return $previous_data;
    }

    public function migration_revert( \WP_REST_Request $request ) {

        // Note :: Only for testing purpose and use test data . Use this if needs to re-run migration .

        global $wpdb;

        if( isset( $request['migration_revert'] ) && $request['migration_revert'] == 'SUCC355' ) {
            $sqls = [
                "{$wpdb->prefix}erp_attendance_date_shift",
                "{$wpdb->prefix}erp_attendance_log",
                "{$wpdb->prefix}erp_attendance_shifts",
                "{$wpdb->prefix}erp_attendance_shift_user",
                "{$wpdb->prefix}erp_attendence_shift_generated_to"
            ];
            foreach( $sqls as $sql ) {
                $wpdb->get_results("TRUNCATE TABLE " . $sql );
            }
            delete_option('previous_attr_data_option_ids');
            return 'Log table is set to initial state';
        }

        if( isset( $request['get_prev_version_att_data'] ) && $request['get_prev_version_att_data'] == 'SUCC355' ) {
            $date = $request['date'];
            $sql = "SELECT * FROM {$wpdb->prefix}erp_attendance WHERE date='{$date}'";
            $results = $wpdb->get_results( $sql );
            return $results;
        }

        if( isset( $request['get_current_log_data'] ) && $request['get_current_log_data'] == 'SUCC355' ) {
            $start_date = $request['start_date'] . ' 00:00:00';
            $end_date   = $request['end_date'] . ' 23:59:59';
            $insert = $request['insert'];
            $sql = "SELECT
                    user_id,
                    MIN(checkin) as checkin,
                    MAX(checkout) as checkout
                    FROM `{$wpdb->prefix}erp_attendance_log`
                    WHERE
                    checkin
                    BETWEEN
                    '{$start_date}' AND '{$end_date}'
                    GROUP BY date_shift_id";
            $results = $wpdb->get_results( $sql );
            $total_results = [];
            foreach ( $results as $result ) {
                $cur_date = date('Y-m-d', strtotime( $result->checkin ));
                $data = [
                    'user_id'           => $result->user_id,
                    'shift_title'       => '',
                    'date'              => $cur_date,
                    'shift_start_time'  => '08:00:00',
                    'shift_end_time'    => '17:00:00',
                    'present'           => 'yes',
                    'checkin'           => date('H:i:s', strtotime( $result->checkin )),
                    'checkout'          => ( date('H:i:s', strtotime( $result->checkout ) ) != '00:00:00' ) ? date('H:i:s', strtotime( $result->checkout ) ) : null,
                ];

                $total_results[] = $data;

                if( isset( $insert ) && $insert == 'true' ) {
                    $result = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_attendance WHERE date = '{$cur_date}' AND user_id={$result->user_id}" );
                    if( $result == 0 ){
                        $wpdb->insert( $wpdb->prefix . 'erp_attendance', $data );
                    }
                }
            }
            return $total_results;
        }




        return 'Sorry ! Try again later';

    }

    public function update_grace_time( \WP_REST_Request $request ) {
        if( isset( $request['params']['grace_before_checkin'] ) && !empty( $request['params']['grace_before_checkin'] ) ) {
             $grace_before_checkin = $request['params']['grace_before_checkin'];
             $grace_after_checkin  = $request['params']['grace_after_checkin'];
             update_option( 'grace_before_checkin' , $grace_before_checkin );
             update_option( 'grace_after_checkin' , $grace_after_checkin );
             return [ $grace_before_checkin, $grace_after_checkin ];
        }
        if( isset( $request['params']['get_grace_before_checkin'] ) && !empty( $request['params']['get_grace_before_checkin'] ) && $request['params']['get_grace_before_checkin'] == true ) {
            $get_grace_before_checkin   = get_option( 'grace_before_checkin' );
            $get_grace_after_checkin    = get_option( 'grace_after_checkin' );
            return [ $get_grace_before_checkin, $get_grace_after_checkin ];
        }

    }

    /******** HR Frontend API function Start *********/

    public function self_attendance( \WP_REST_Request $request ) {

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

        $log = erp_attendance_get_single_user_log();

        return [
            'attendance'    => $attendance,
            'log'           => $log
        ];

    }

    public function save_attendance( \WP_REST_Request $request ) {

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
            wp_send_json_success( $attendance );
        } else {
            wp_send_json_error( $attendance->get_error_message() );
        }
    }

    /******** HR Frontend API function End *********/


    /****** Export import start *******/

    public function export_file( \WP_REST_Request $request ){
        return WPERP_ATTEND_ASSETS . '/sample/import.csv';
    }

    public function import_file( \WP_REST_Request $request ){
        global $wpdb;

        $file       = $request->get_file_params()['file'];
        $csv        = new parseCSV( $file['tmp_name'] );
        $shift_id   = $request->get_param( 'shift_id' );
        $shift      = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}erp_attendance_shifts WHERE id={$shift_id}" );

        if ( ! empty( $csv->data ) ) {

            $counter = 0;

            foreach ( $csv->data as $line ) {
                $date       = $line['date'];
                $user_id    = $line['user_id'];
                $shift_id   = $shift->id;
                $start_time = $date . ' ' . $shift->start_time;
                $end_time   = $date . ' ' . $shift->end_time;
                $present    = $line['present'];
                $checkin    = ( !empty( $line['checkin'] ) ) ? $date . ' ' . $line['checkin'] : null;
                $checkout   = ( !empty( $line['checkout'] ) ) ? $date . ' ' . $line['checkout'] : null;
                $time       = ( !empty( $line['checkin'] ) && !empty( $line['checkout'] ) ) ? strtotime( $line['checkout'] ) - strtotime( $line['checkin'] ) : null;


                $d_checkin      = new DateTime( $line['checkin'] );
                $d_checkout     = new DateTime( $line['checkout'] );
                $d_start_time   = new DateTime( $shift->start_time );
                $d_end_time     = new DateTime( $shift->end_time );



                $wpdb->insert( $wpdb->prefix . 'erp_attendance_date_shift', [
                    'date'          => $date,
                    'user_id'       => $user_id,
                    'shift_id'      => $shift_id,
                    'start_time'    => $start_time,
                    'end_time'      => date('Y-m-d H:i:s', strtotime( $start_time . ' + 23 hours' ) ),
                    'present'       => ( $present == 'yes' ) ? 1 : null,
                    'late'          => ( $d_checkin > $d_start_time && $present != null ) ? 1 : null,
                    'early_left'    => ( $d_end_time > $d_checkout  && $present != null ) ? 1000 : null,
                ]);

                if ( $wpdb->insert_id ) {
                    $counter++ ;
                } else {
                    wp_send_json_error( __( 'Data import faild. Please provide a csv file with correct format', 'erp-pro' ) );
                }

                if ( $present != null ) {
                    $wpdb->insert( $wpdb->prefix . 'erp_attendance_log', [
                        'user_id'       => $user_id,
                        'date_shift_id' => $wpdb->insert_id,
                        'checkin'       => $checkin,
                        'checkout'      => ( $checkout != null ) ? $checkout : '0000-00-00 00:00:00',
                        'time'          => $time,
                    ]);
                }
            }

            wp_send_json_success( __( 'Total ' . $counter . ' data imported sucecssfully', 'erp-pro' ) );
        }


    }

    /****** Export import end *******/


}
