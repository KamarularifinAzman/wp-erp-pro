<?php
namespace WeDevs\Attendance\Emails;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\Attendance\Emails\AttendanceReminder;

/**
 * HR Email handler class
 */
class Emailer {

    use Hooker;

    function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    function register_emails( $emails ) {
        $emails['AttendanceReminder'] = new AttendanceReminder();

        return $emails;
    }
}
