<?php

namespace WeDevs\Reimbursement\Api;

/**
 * REST_API Handler
 */
class REST_API {

    public function __construct() {
        add_filter( 'erp_rest_api_controllers', array( $this, 'register_erp_people_trn_controllers' ) );
    }

    public function register_erp_people_trn_controllers( $controllers ) {
        return array_merge( $controllers, [
            '\WeDevs\Reimbursement\Api\PeopleTrnController',
            '\WeDevs\Reimbursement\Api\EmployeeRequestsController',
        ] );
    }
}
