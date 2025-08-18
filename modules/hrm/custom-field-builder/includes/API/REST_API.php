<?php

namespace WeDevs\CustomFieldBuilder\API;

/**
 * REST_API Handler
 */
class REST_API {

    public function __construct() {
        add_filter( 'erp_rest_api_controllers', array( $this, 'register_erp_cfb_controllers' ) );
    }

    /**
     * Register rest controller
     *
     * @param object $controllers
     * @return object
     */
    public function register_erp_cfb_controllers( $controllers ) {
        $controllers = array_merge( $controllers, [
            '\WeDevs\CustomFieldBuilder\API\CustomFieldBuilderController',
        ] );

        return $controllers;
    }
}
