<?php
namespace WeDevs\ERP_PRO\Feature\Accounting\Core;

use \WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Accounting core class
 *
 * @since 1.1.0
 */
class Core {

    use Hooker;

    /**
     * Class constructor
     * 
     * @since 1.1.0
     */
    public function __construct() {
        $this->includes();
        $this->init_classes();
    }

    /**
     * init classes
     * 
     * @since 1.1.0
     *
     * @return void
     */
    private function init_classes() {
        new Assets();
    }

    /**
     * Includes necessary files
     * 
     * @since 1.1.0
     *
     * @return void
     */
    private function includes() {
        include_once ERP_ACCT_FEATURE_PATH . '/Core/functions/purchase-return.php';
        include_once ERP_ACCT_FEATURE_PATH . '/Core/functions/sales-return.php';
        include_once ERP_ACCT_FEATURE_PATH . '/Core/functions/reports.php';
    }
}
