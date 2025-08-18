<?php

namespace WeDevs\Reimbursement\Api;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 ** NOTE: Please update permissions, ...
 */

class EmployeeRequestsController extends \WeDevs\ERP\API\Rest_Controller {
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
    protected $rest_base = 'accounting/v1/employee-requests';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_reimb_requests' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employee_reimb_request' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_reimb_request' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                }
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_employee_reimb_request' ],
                'args'                => [],
                'permission_callback' => function( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/attachments',
            [
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'upload_attachments' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_view_list' );
					},
				]
			]
        );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/employee/chart-requests',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_reimb_req_chart_data' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_view_list' );
					},
				],
			]
        );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/employee/chart-status',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_reimb_req_chart_status' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_view_list' );
					},
				],
			]
        );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/manager/chart-requests',
        [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_reimb_req_chart_data' ],
                'args'                => [],
                'permission_callback' => function( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ],
        ]
    );

    register_rest_route( $this->namespace, '/' . $this->rest_base . '/manager/chart-status',
        [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_reimb_req_chart_status' ],
                'args'                => [],
                'permission_callback' => function( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ],
        ]
    );
    }

    /**
     * Create employee reimbursement request
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_employee_reimb_request( $request ) {
        $request_data = $this->prepare_item_for_database( $request );

        $request_data['attachments'] = maybe_serialize( $request['attachments'] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $response = erp_acct_reimb_insert_request( $request_data );

        $this->add_log( $request_data, 'add' );

        $response = $this->prepare_item_for_response( $request_data, $request, $additional_fields );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update employee reimbursement request
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_employee_reimb_request( $request ) {
        $request_data = $this->prepare_item_for_database( $request );

        $request_data['attachments'] = maybe_serialize( $request['attachments'] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $old_data = erp_acct_get_reimb_employee_request( $request['id'] );

        $response = erp_acct_reimb_update_request( $request_data );

        $this->add_log( $request_data, 'edit', $old_data );

        $response = $this->prepare_item_for_response( $request_data, $request, $additional_fields );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Get all employee reimbursement requests
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee_reimb_requests( $request ) {
        $args = [
            'number'    => (int) ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : 20,
            'offset'    => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'people_id' => ! empty( $request['people_id'] ) ? absint( $request['people_id'] ) : null
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $request_data = erp_acct_get_employee_reimb_requests( $args );
        $total_items  = erp_acct_get_employee_reimb_requests( [ 'count' => true, 'number' => -1 ] );

        foreach ( $request_data as $item ) {
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
     * Get employee reimb single request
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee_reimb_request( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_invoice_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_reimb_employee_request( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;
        $item = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Upload attachment for reimbursement request
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function upload_attachments( $request ) {
        $movefiles = erp_acct_upload_attachments( $_FILES['attachments'] );

        $response = rest_ensure_response( $movefiles );
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
    public function get_reimb_req_chart_data( $request ) {
        $args = [
            'people_id'  => empty( $request['people_id'] ) ? 0 : $request['people_id'],
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_data = erp_acct_reimb_empl_chart_data( $args );

        $response = rest_ensure_response( $chart_data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Chart status
     */
    public function get_reimb_req_chart_status( $request ) {
        $args = [
            'people_id'  => empty( $request['people_id'] ) ? 0 : $request['people_id'],
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_status = erp_acct_reimb_empl_chart_status( $args );

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

        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = $request['id'];
        }
        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['due_date'] ) ) {
            $prepared_item['due_date'] = $request['due_date'];
        }
        if ( isset( $request['reference'] ) ) {
            $prepared_item['reference'] = $request['reference'];
        }
        if ( isset( $request['line_items'] ) ) {
            $prepared_item['line_items'] = $request['line_items'];
        }
        if ( isset( $request['amount_total'] ) ) {
            $prepared_item['amount_total'] = $request['amount_total'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
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
            'id'           => empty( $item->id ) ? null : $item->id,
            'people_id'    => empty( $item->people_id ) ? null : $item->people_id,
            'people_name'  => empty( $item->people_name ) ? null : $item->people_name,
            'amount_total' => $item->amount_total,
            'trn_date'     => $item->trn_date,
            'reference'    => $item->reference,
            'line_items'   => empty( $item->line_items ) ? [] : $item->line_items,
            'particulars'  => $item->particulars,
            'attachments'  => $item->attachments,
            'status'       => erp_acct_get_trn_status_by_id( $item->status ),
            'created_at'   => empty( $item->created_at ) ? null : $item->created_at
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
            'title'      => 'employee-reimburse',
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
                    'type'        => 'integer',
                    'context'     => [ 'edit' ]
                ],
                'people_name'  => [
                    'description' => __( 'People name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ],
                'amount'       => [
                    'description' => __( 'Amount for the resource.' ),
                    'type'        => 'number',
                    'context'     => [ 'edit' ]
                ],
                'amount_total' => [
                    'description' => __( 'Total Amount for the resource.' ),
                    'type'        => 'number',
                    'context'     => [ 'edit' ]
                ],
                'trn_date'     => [
                    'description' => __( 'Transaction date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'reference'       => [
                    'description' => __( 'payment method of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ],
                'particulars'  => [
                    'description' => __( 'Particulers for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ]
            ],
        ];

        return $schema;
    }

    /**
     * Log when reimbursement request created or edited
     *
     * @since 1.2.4
     *
     * @param array $data
     * @param string $action
     * @param array $old_data
     *
     * @return void
     */
    public function add_log( $data, $action, $old_data = [] ) {
        $operation = $action === 'edit' ? 'updated': 'created';
        $employee  = new \WeDevs\ERP\HRM\Employee( get_current_user_id() );

        $def_diff = [
            'old_value' => '',
            'new_value' => '',
        ];

        if ( ! empty( $old_data ) ) {
            $diff = erp_get_array_diff(
                erp_array_flatten( $data ),
                erp_array_flatten( $old_data )
            );

            $def_diff['old_value'] = $diff['old_value'];
            $def_diff['new_value'] = $diff['new_value'];
        }

        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Reimbursement', 'erp' ),
                'message'       => sprintf( __( '%1$s employee has %2$s a reimbursement request of %3$s', 'erp-pro' ), $employee->get_full_name(), $operation, $data['amount_total'] ),
                'changetype'    => $action,
                'created_by'    => get_current_user_id(),
                'old_value'     => $def_diff['old_value'],
                'new_value'     => $def_diff['new_value'],
            ]
        );
    }
}
