<?php
namespace WeDevs\ERP_PRO\REST;

use WP_Error;
use WP_REST_Server;
use WP_REST_Controller;

class ModulesController extends WP_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp_pro/v1/admin';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'modules';

    /**
     * Register all routes related with modules
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/activate', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'activate_modules' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                =>  $this->module_toggle_request_args(),
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/deactivate', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'deactivate_modules' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                =>  $this->module_toggle_request_args(),
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/installed', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'installed_modules' ],
                'permission_callback' => [ $this, 'check_permission' ],
                //'args'                =>  $this->module_toggle_request_args(),
            ]
        ] );
    }

    /**
     * Perform permission checking
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function check_permission() {
        return true;
        return current_user_can( 'manage_options' );
    }

    /**
     * Activation/deactivation request args
     *
     * @return array
     */
    public function module_toggle_request_args() {
        return [
            'module' => [
                'description'       => __( 'Basename of the module as array', 'wp_erp' ),
                'required'          => true,
                'type'              => 'array',
                'validate_callback' => [ $this, 'validate_modules' ],
                'items'             => [
                    'type' => 'string'
                ]
            ],
        ];
    }

    /**
     * Validate module ids
     *
     * @since 0.0.1
     *
     * @param array $modules
     *
     * @return bool|\WP_Error
     */
    public function validate_modules( $modules ) {
        if ( ! is_array( $modules ) ) {
            return new WP_Error( 'erp_pro_rest_error', __( 'module parameter must be an array of id of ERP Pro modules.', 'wp_erp' ) );
        }

        if ( empty( $modules ) ) {
            return new WP_Error( 'erp_pro_rest_error', 'module parameter is empty', 'wp_erp' );
        }

        $available_modules = wp_erp_pro()->module->get_available_modules();

        foreach ( $modules as $module ) {
            if ( ! in_array( $module, $available_modules ) ) {
                return new WP_Error( 'erp_pro_rest_error', sprintf( __( '%s module is not available in your system.', 'wp_erp' ), $module ) );
            }
        }

        return true;
    }

    /**
     * Get all modules
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_items( $request ) {
        $data             = [];
        $modules          = wp_erp_pro()->module->get_all_modules();
        $activate_modules = wp_erp_pro()->module->get_active_modules();

        foreach ( $modules as $module ) {
            $data[] = [
                'id'           => $module['id'],
                'name'         => $module['name'],
                'description'  => $module['description'],
                'thumbnail'    => $module['thumbnail'],
                'active'       => in_array( $module['id'], $activate_modules ),
                'available'    => file_exists( $module['module_file'] ),
                'doc_id'       => $module['doc_id'] ? $module['doc_id'] : null,
                'doc_link'     => $module['doc_link'] ? $module['doc_link'] : null,
            ];
        }

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Activate modules
     *
     * @param  WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function activate_modules( $request ) {
        $modules        = $request['module'];
        wp_erp_pro()->module->activate_modules( $modules );
        wp_erp_pro()->module->set_modules( [] );

        return $this->get_items( $request );
    }

    /**
     * Deactivate modules
     *
     * @param  WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function deactivate_modules( $request ) {
        $modules = $request['module'];
        wp_erp_pro()->module->deactivate_modules( $modules );
        wp_erp_pro()->module->set_modules( [] );

        return $this->get_items( $request );
    }

    public function installed_modules() {
        $available_modules = wp_erp_pro()->module->get_available_modules( true );
        return rest_ensure_response( $available_modules );
    }
}

