<?php
namespace WeDevs\Recruitment\Api;

use WeDevs\ERP\API\Rest_Controller;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\HRM\Department;


class RecruitmentController extends Rest_Controller {

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
    protected $rest_base = 'hrm/recruitment';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/jobs', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_jobs' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function( $request ) {
                    header( 'Access-Control-Allow-Origin: *' );
                    header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
                    return true;
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    public function get_jobs( \WP_REST_Request $request ) {
        global $wpdb;

        $items_per_page  = ( isset( $request['per_page'] ) ) ? $request['per_page'] : 10;
        $page            = ( isset( $request['page'] ) ) ? $request['page'] : 1;
        $offset          = ( $page - 1 ) * $items_per_page;
        $today           = date( 'Y-m-d' );

        $query = "SELECT
                post.*
                FROM {$wpdb->prefix}posts AS post
                INNER JOIN {$wpdb->prefix}postmeta as pmeta
                ON pmeta.post_id = post.id
                WHERE post.post_type = 'erp_hr_recruitment'
                AND (
                    (pmeta.meta_key = '_expire_date' AND pmeta.meta_value >= '$today')
                   OR (pmeta.meta_key = '_expire_date' AND pmeta.meta_value = '')
                ) ";

        if ( isset( $request['status'] ) && $request['status'] !== '' && $request['status'] !== '-1' && ( $request['status'] === 'draft' || $request['status'] === 'pending' ) ) { //has status
            $query .= " AND post.post_status='" . $request['status'] . "'";
        }
        if ( isset( $request['status'] ) && $request['status'] === 'publish' && $request['status'] !== '' && $request['status'] !== '-1' ) {
            $query .= " AND post.post_status='publish' AND ( DATE(pmeta.meta_value) > CURDATE() OR DATE(pmeta.meta_value) = ' ')";
        }
        if ( isset( $request['status'] ) && $request['status'] === 'expired' && $request['status'] !== '' && $request['status'] !== '-1' ) {
            $query .= " AND post.post_status='publish' AND ( DATE(pmeta.meta_value) < CURDATE() AND DATE(pmeta.meta_value) <> ' ')";
        }
        if ( isset( $request['search_key'] ) && $request['search_key'] !== '' ) { //search is not empty
            $query .= " AND post.post_title LIKE '%" . $request['search_key'] . "%'";
        }

        $query .= " ORDER BY id DESC LIMIT {$offset}, {$items_per_page}";

        $results = $wpdb->get_results( $query );

        if ( ! $results ) {
            wp_send_json_error( [], 404 );
        }


        foreach ( $results as $rsl_arr ) {
            $department = new Department( intval( get_post_meta( $rsl_arr->ID, '_department', true ) ) );

            $response['jobs'][] = [
                'id'                    => $rsl_arr->ID,
                'title'                 => $rsl_arr->post_title,
                'description'           => $rsl_arr->post_content,
                'status'                => $rsl_arr->post_status,
                'guid'                  => get_post_permalink( $rsl_arr->ID ),
                'publish_date'          => $rsl_arr->post_date,
                'department_name'       => $department->name,
                'department_id'         => get_post_meta( $rsl_arr->ID, '_department', true ),
                'employment_type'       => get_post_meta( $rsl_arr->ID, '_employment_type', true ),
                'minimum_experience'    => get_post_meta( $rsl_arr->ID, '_minimum_experience', true ),
                'remote_job'            => get_post_meta( $rsl_arr->ID, '_remote_job', true ),
                'expire_date'           => get_post_meta( $rsl_arr->ID, '_expire_date', true ),
                'location'              => get_post_meta( $rsl_arr->ID, '_location', true ),
                'vacancy'               => get_post_meta( $rsl_arr->ID, '_vacancy', true ),
                'street_address'        => get_post_meta( $rsl_arr->ID, '_street_address', true ),
                'address_locality'      => get_post_meta( $rsl_arr->ID, '_address_locality', true ),
                'postal_code'           => get_post_meta( $rsl_arr->ID, '_postal_code', true ),
                'address_country'       => get_post_meta( $rsl_arr->ID, '_address_country', true ),
                'currency'              => get_post_meta( $rsl_arr->ID, '_currency', true ),
                'salary'                => get_post_meta( $rsl_arr->ID, '_salary', true ),
                'salary_type'           => get_post_meta( $rsl_arr->ID, '_salary_type', true ),
            ];
        }

        $response['departments'] = erp_hr_get_departments();

        wp_send_json_success( $response, '200' );
    }

}
