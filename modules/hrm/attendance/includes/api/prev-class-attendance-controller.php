<?php

namespace WeDevs\Attendance\Api;

use WeDevs\ERP\API\Rest_Controller;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class Attendance_Controller extends Rest_Controller {
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
    }

    /**
     * Get attendance status
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function self_attendance( $request ) {
        $user_id = ! empty( $request['user_id'] ) ? absint( $request['user_id'] ) : get_current_user_id();

        if ( empty( $user_id ) ) {
            return new WP_Error( 'invalid_employee_user_id',  __( 'Invalid Employee user ID received', 'erp-pro' ), array( 'status' => 404 ) );
        }

        global $wpdb;

        $today = current_time( 'Y-m-d' );
        $sql   = "SELECT
                    min(al.checkin) AS checkin,
                    max(al.checkout) AS checkout,
                    SUM(al.time) AS worktime,
                    ds.id AS dshift_id,
                    ds.date,
                    ds.user_id,
                    shifts.name AS shift_title,
                    ds.start_time,
                    ds.end_time
                FROM {$wpdb->prefix}erp_attendance_date_shift AS ds
                INNER JOIN {$wpdb->prefix}erp_attendance_shifts as shifts ON ds.shift_id = shifts.id
                INNER JOIN {$wpdb->prefix}erp_attendance_log AS al ON ds.id = al.date_shift_id
                WHERE ds.date = '{$today}' AND ds.user_id = {$user_id}";

        $attendance = $wpdb->get_row( $sql );

        return rest_ensure_response( $attendance );
    }

    /**
     * Save attendance in/out
     *
     * @since 1.2.9
     *
     * @param $request
     *
     * @return WP_Error
     */
    public function save_attendance( \WP_REST_Request $request ) {
        $user_id = ! empty( $request['user_id'] ) ? absint( $request['user_id'] ) : get_current_user_id();

        if ( empty( $user_id ) ) {
            return new WP_Error( 'invalid_employee_user_id',  __( 'Invalid Employee user ID received', 'erp-pro' ), array( 'status' => 404 ) );
        }

        if ( erp_att_has_restriction() ) {
            $client_ip = erp_get_client_ip();

            if ( ! erp_att_is_ip_allowed( $client_ip ) ) {
                return new WP_Error( 'rest_unauthorized_ip',  sprintf( __( 'You are not allowed to %s from this IP', 'erp-pro' ),$type), array( 'status' => 404 ) );
            }
        }

        $attendance = erp_attendance_punch( $user_id );
        $response   = rest_ensure_response( $attendance );

        return $response;
    }
}
