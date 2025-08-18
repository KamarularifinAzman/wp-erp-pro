<?php
namespace WeDevs\Recruitment\Emails;

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

        $emails['NewJobApplicationSubmitted']             = new NewJobApplicationSubmitted();
        $emails['ConfirmationOfSuccessfulSubmission']    = new ConfirmationOfSuccessfulSubmission();

        return $emails;
    }
}
