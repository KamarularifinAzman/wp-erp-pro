<?php
namespace WeDevs\DocumentManager\API;

use WP_Rest_Server;
use WP_Rest_Response;
use WP_Error;
use WeDevs\ERP\API\Rest_Controller;

class DocumentController extends Rest_Controller {
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route Base
     *
     * @var string
     */
    protected $rest_base = 'hrm/docs';

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)', [
            [
                'methods'       =>  WP_Rest_Server::READABLE,
                'callback'      =>  [ $this, 'get_docs' ],
                'args'          =>  $this->get_collection_params(),
                'permission_callback'   => function( $request ) {
                    return current_user_can( 'erp_list_employee' );
                }
            ],
            [
                'methods'   =>  WP_Rest_Server::CREATABLE,
                'callback'  =>  [ $this, 'create_dir' ],
                'args'      =>  $this->get_collection_params(),
                'permission_callback'   =>  function( $request ) {
                    return current_user_can( 'erp_list_employee' );
                }
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/file' . '/(?P<target_id>[\d]+)', [
                [
                    'methods'   =>  WP_Rest_Server::EDITABLE,
                    'callback'  =>  [ $this, 'rename_dir_file' ],
                    'args'      =>  $this->get_collection_params(),
                    'permission_callback'   =>  function( $request ) {
                        return current_user_can( 'erp_list_employee' );
                    }
                ],
                [
                    'methods'   =>  WP_Rest_Server::DELETABLE,
                    'callback'  =>  [ $this, 'delete_dir_file' ],
                    'args'      =>  $this->get_collection_params(),
                    'permission_callback'   =>  function( $request ) {
                        return current_user_can( 'erp_list_employee' );
                    }
                ]
            ]
        );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/move', [
            [
                'methods'   =>      WP_Rest_Server::EDITABLE,
                'callback'  =>      [ $this, 'move_dir_file' ],
                'args'      =>      $this->get_collection_params(),
                'permission_callback'   =>  function( $request ) {
                    return current_user_can( 'erp_list_employee' );
                }
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/search', [
            [
                'methods'   =>      WP_Rest_Server::READABLE,
                'callback'  =>      [ $this, 'search_file_folder' ],
                'args'      =>      $this->get_collection_params(),
                'permission_callback'   =>  function( $request ) {
                    return current_user_can( 'erp_list_employee' );
                }
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/dropbox', [
            [
                'methods'       =>  WP_Rest_Server::READABLE,
                'callback'      =>  [ $this, 'dropbox' ],
                'args'          =>  $this->get_collection_params(),
                'permission_callback'   => function( $request ) {
                    return true;
                }
            ]
        ] );
    }

    /**
     * Get all documents for an employee
     *
     * @return array
     */
    public function get_docs( \WP_REST_Request $request ) {
        error_log( print_r( 'Employee : ' . $request['user_id'],true ) );
        $user_id = isset( $request['user_id'] ) ? $request['user_id'] : 0;

        $response = erp_doc_load_dir_file( $user_id );

        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Create directory
     *
     * @param  \WP_REST_Request $request
     *
     * @return object
     */
    public function create_dir( \WP_REST_Request $request ) {
        $employee_id    =   isset( $request['employee_id'] ) ? intval( $request['employee_id'] ) : 0;
        $parent_id      =   isset( $request['parent_id'] ) ? intval( $request['parent_id'] ) : 0;
        $dir_name       =   isset( $request['dir_name'] ) ? $request['dir_name'] : '';

        if ( empty( $dir_name ) ) {
            return;
        }
        $response = erp_doc_create_dir( $employee_id, $parent_id, $dir_name );

        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Rename directory or file
     *
     * @param  \WP_REST_Request $request
     *
     * @return object
     */
    public function rename_dir_file( \WP_REST_Request $request ) {
        $employee_id    =   isset( $request['employee_id'] ) ? intval( $request['employee_id'] ) : 0;
        $parent_id      =   isset( $request['parent_id'] ) ? intval( $request['parent_id'] ) : 0;
        $target_id      =   isset( $request['target_id'] ) ? intval( $request['target_id'] ) : 0;
        $dir_name       =   isset( $request['dir_name'] ) ? $request['dir_name'] : '';
        $type           =   isset( $request['type'] ) ? $request['type'] : '';

        if ( empty( $dir_name ) ) {
            return;
        }

        $response = rename_dir_file( $employee_id, $parent_id, $target_id, $dir_name, $type );

        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Delete document of file
     *
     * @param  \WP_REST_Request $request
     *
     * @return bool
     */
    public function delete_dir_file( \WP_REST_Request $request ) {
        $user_id    =   isset( $request['user_id'] ) ? intval( $request['user_id'] ) : 0;
        $target_id  =   isset( $request['target_id'] ) ? intval( $request['target_id'] ) : 0;


        erp_doc_delete_dir_file( $user_id, $target_id );

        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    /**
     * Move file or folder
     *
     * @param  \WP_REST_Request $request
     *
     * @return
     */
    public function move_dir_file( \WP_REST_Request $request ) {
        $selected   =   isset( $request['select_file_folder'] ) ? $request['select_file_folder'] : array();
        $user_id    =   isset( $request['user_id'] ) ? $request['user_id'] : 0;
        $parent_id  =   isset( $request['parent_id'] ) ? $request['parent_id'] : 0;

        erp_doc_move_dir_file( $user_id, $parent_id, $selected );
        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    /**
     * Search file or folder
     *
     * @param  \WP_REST_Request $request
     *
     * @return array
     */
    public function search_file_folder( \WP_REST_Request $request ) {
        $user_id    =   isset( $request['user_id'] ) ? $request['user_id'] : 0;
        $search_key =   isset( $request['search_key'] ) ? $request['search_key'] : '';

        $response   =  erp_doc_search_dir_file( $user_id, $search_key );
        $response   =   rest_ensure_response( $response );

        return $response;
    }

    /**
     * Get admin dropbox files
     *
     * @param  \WP_REST_Request $request
     *
     * @return array
     */
    public function dropbox( \WP_REST_Request $request ) {

        $result = dropbox_api_call( "files/list_folder", [
            'path' => '',
            'recursive' => false,
            //'shared_link ' => true,
        ] );
        return $result;

    }
}
