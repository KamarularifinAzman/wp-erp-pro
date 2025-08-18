<?php

namespace WeDevs\ERP_PRO\Feature\Accounting\Api;

/**
 * class REST_API Handler
 */
class Rest_Controller {

    /**
     * Class constructor
     */
    public function __construct() {
        add_filter( 'erp_rest_api_controllers', [ $this, 'register_accounting_new_controllers' ] );
    }

    /**
     * Registers new controllers
     *
     * @param array $controllers
     *
     * @return array
     */
    public function register_accounting_new_controllers( $controllers ) {
        $this->include_controllers();

        $controllers = array_merge(
            $controllers,
            [
                '\WeDevs\ERP_PRO\Feature\Accounting\Api\PurchaseReturnController',
                '\WeDevs\ERP_PRO\Feature\Accounting\Api\SalesReturnController',
                '\WeDevs\ERP_PRO\Feature\Accounting\Api\ReportsController',
            ]
        );

        return $controllers;
    }

    /**
     * Includes required controller files
     *
     * @return void
     */
    public function include_controllers() {
        foreach ( glob( ERP_ACCOUNTING_API . '/*.php' ) as $filename ) {
            include_once $filename;
        }
    }
}
