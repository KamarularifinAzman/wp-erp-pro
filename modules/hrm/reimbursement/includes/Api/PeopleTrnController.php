<?php

namespace WeDevs\Reimbursement\Api;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PeopleTrnController extends \WeDevs\ERP\API\Rest_Controller {
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
    protected $rest_base = 'accounting/v1/people-transactions';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_all_people_trns' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_people_trn' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_invoice' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/balances', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_all_people_balances' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/report', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_people_trns_report' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
        ] );


        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_people_trn' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/chart-requests',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_requests_chart_data' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/chart-status',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_requests_chart_status' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

    }

    /**
     * Create people transaction
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_people_trn( $request ) {
        $people_trn_data = $this->prepare_item_for_database( $request );

        $people_trn_data['attachments'] = maybe_serialize( $request['attachments'] );
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $people_trn_data = erp_acct_insert_people_trn( $people_trn_data );

        $people_trn_data = $this->prepare_item_for_response( $people_trn_data, $request, $additional_fields );

        $response = rest_ensure_response( $people_trn_data );

        $this->add_log( $people_trn_data->data, 'add' );

        $response->set_status( 201 );

        return $response;
    }

    /**
     * Get all people transactions
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_all_people_trns( $request ) {
        $args = [
            'number' => ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $people_trn_data = erp_acct_get_all_people_trns( $args );
        $total_items     = erp_acct_get_all_people_trns( [ 'count' => true, 'number' => - 1 ] );

        foreach ( $people_trn_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get all people balances
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_all_people_balances( $request ) {
        $args = [
            'number' => ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $people_trn_data = erp_acct_get_all_people_balances( $args );
        $total_items     = erp_acct_get_all_people_balances( [ 'count' => true, 'number' => - 1 ] );

        foreach ( $people_trn_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get people transactions report
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_people_trns_report( $request ) {
        $people_id  = (int) $request['people_id'];
        $start_date = empty( $request['start_date'] ) ? date( 'Y-m-d' ) : $request['start_date'];
        $end_date   = empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'];

        $data = erp_acct_get_people_trn_report( $people_id, $start_date, $end_date );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a single people transaction
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_people_trn( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_people_trn( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get chart data of requests
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_requests_chart_data( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_data = erp_acct_reimb_chart_data( $args );

        $response = rest_ensure_response( $chart_data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Chart status
     */
    public function get_requests_chart_status( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_status = erp_acct_reimb_chart_status( $args );

        $response = rest_ensure_response( $chart_status );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Prepare a single people trn item for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }
        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }
        if ( isset( $request['ledger_id'] ) ) {
            $prepared_item['ledger_id'] = $request['ledger_id'];
        }
        if ( isset( $request['people_id'] ) ) {
            $prepared_item['people_id'] = $request['people_id'];
        }
        if ( isset( $request['request_id'] ) ) {
            $prepared_item['request_id'] = $request['request_id'];
        }
        if ( isset( $request['voucher_type'] ) ) {
            $prepared_item['voucher_type'] = $request['voucher_type'];
        }
        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'           => $item->id,
            'people_id'    => $item->people_id,
            'name'         => erp_acct_get_people_name_by_people_id( $item->people_id ),
            'voucher_no'   => isset( $item->voucher_no ) ? $item->voucher_no : $item->trn_no,
            'balance'      => isset( $item->balance ) ? $item->balance : $item->amount,
            'trn_date'     => $item->trn_date,
            'trn_by'       => 'people',
            'voucher_type' => $item->voucher_type,
            'particulars'  => $item->particulars,
            'debit'        => $item->debit,
            'credit'       => $item->credit
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'customer',
            'type'       => 'object',
            'properties' => [
                'id'           => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'people_id'    => [
                    'description' => __( 'People id for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'people_name'  => [
                    'description' => __( 'People name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'voucher_no'   => [
                    'description' => __( 'Voucher no for the resource.' ),
                    'type'        => 'string',
                    'format'      => 'email',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'amount'       => [
                    'description' => __( 'Amount for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'trn_date'     => [
                    'description' => __( 'Transaction date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'trn_by'       => [
                    'description' => __( 'payment method of the resource.' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'voucher_type' => [
                    'description' => __( 'Voucher type of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'particulars'  => [
                    'description' => __( 'Particulers for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'debit'        => [
                    'description' => __( 'Debit of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'credit'       => [
                    'description' => __( 'Credit of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];


        return $schema;
    }

    /**
     * Log when reimbursement is created
     *
     * @since 1.2.4
     *
     * @param array $data
     * @param string $action
     *
     * @return void
     */
    public function add_log( $data, $action ) {
        $amount = $data['voucher_type'] === 'credit' ? $data['credit'] : $data['debit'];

        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Reimbursement', 'erp-pro' ),
                'message'       => sprintf( __( 'A %1$s transaction of %2$s has been created for <strong>%3$s</strong>', 'erp-pro' ), $data['voucher_type'], $amount, $data['name'] ),
                'changetype'    => $action,
                'created_by'    => get_current_user_id(),
            ]
        );
    }
}
