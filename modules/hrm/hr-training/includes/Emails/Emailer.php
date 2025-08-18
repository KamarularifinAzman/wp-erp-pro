<?php
namespace WeDevs\HrTraining\Emails;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * HR Email handler class
 */
class Emailer {

    use Hooker;

    public function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    public function register_emails( $emails ) {
        $emails['AfterAssignTraining'] = new AfterAssignTraining();

        return $emails;
    }
}
