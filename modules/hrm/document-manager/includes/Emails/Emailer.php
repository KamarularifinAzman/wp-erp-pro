<?php
namespace WeDevs\DocumentManager\Emails;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\DocumentManager\Emails\DmFileShareNotification as DmFileShareNotification;

/**
 * HR Email handler class
 */
class Emailer {

    use Hooker;

    function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    function register_emails( $emails ) {
        $emails['DmFileShareNotification'] = new DmFileShareNotification();

        return $emails;
    }
}
