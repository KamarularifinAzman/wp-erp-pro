<?php

namespace WeDevs\ERP_PRO\Feature\Accounting\Api;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\API\REST_Controller;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class for handling APIs of reports
 *
 * @since 1.2.3
 */
class ReportsController extends Rest_Controller {

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
    protected $rest_base = 'accounting/v1/reports';

    /**
     * Register the routes for the objects of the controller.
     *
     * @since 1.2.3
     *
     * @return void
     */
    public function register_routes() {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/sales/return',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_return_report' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purchase/return',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchase_return_report' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purchase-vat',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchase_vat_report' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );
    }

    /**
     * Retrieves sales return report
     *
     * @since 1.2.3
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales_return_report( $request ) {
        $args     = [
            'start_date' => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'   => ! empty( $request['end_date'] )   ? $request['end_date']   : null
        ];

        $data     = erp_acct_get_sales_return_report( $args );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Retrieves purchase return report
     *
     * @since 1.2.3
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchase_return_report( $request ) {
        $args     = [
            'start_date' => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'   => ! empty( $request['end_date'] )   ? $request['end_date']   : null
        ];

        $data     = erp_acct_get_purchase_retuen_report( $args );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Retrieves purchase vat reports
     *
     * @since 1.2.3
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchase_vat_report( $request ) {
        $args = [
            'start_date'  => ! empty( $request['start_date'] )  ? $request['start_date']  : null,
            'end_date'    => ! empty( $request['end_date'] )    ? $request['end_date']    : null,
            'agency_id'   => ! empty( $request['agency_id'] )   ? $request['agency_id']   : null,
            'category_id' => ! empty( $request['category_id'] ) ? $request['category_id'] : null,
            'vendor_id'   => ! empty( $request['vendor_id'] )   ? $request['vendor_id']   : null,
        ];

        $data = erp_acct_get_purchase_vat_report( $args );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }
}
