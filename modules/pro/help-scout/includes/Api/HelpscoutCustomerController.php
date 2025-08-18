<?php
namespace WeDevs\HelpScout\Api;

use WeDevs\ERP\API\Rest_Controller;
use WP_Rest_Server;
use WP_Rest_Response;
use WP_Error;

/**
 * Class Helpscout Webhook Controller
 */
class HelpscoutCustomerController extends Rest_Controller {

    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'erp/helpscout/v1';

    /**
     *  Route base
     *
     * @var string
     */
    protected $rest_base = 'customer';

    /**
     * Register the routes for the object of customer
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_Rest_Server::READABLE,
                'callback'            => [ $this, 'get_customers' ],
                'permission_callback' => '__return_true',
            ],
        ] );
    }

    /**
     * Get helpscout customers
     *
     * @return array
     */
    public function get_customers() {
        new \WeDevs\HelpScout\User();
    }
}
