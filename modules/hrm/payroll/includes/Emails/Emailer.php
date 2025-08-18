<?php
namespace WeDevs\Payroll\Emails;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * HR Email handler class
 */
class Emailer {

    use Hooker;

    function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    function register_emails( $emails ) {
        $emails['EmailPayslip'] = new EmailPayslip();

        return $emails;
    }
}
