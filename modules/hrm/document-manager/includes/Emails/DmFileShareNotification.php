<?php
namespace WeDevs\DocumentManager\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class DmFileShareNotification extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'dm-file-share-notification';
        $this->title       = __( 'New File Shared', 'erp-pro' );
        $this->description = __( 'New File Share Notification.', 'erp-pro' );

        $this->subject     = __( 'File share notification', 'erp-pro' );
        $this->heading     = __( 'File share notification heading', 'erp-pro' );
        $this->find = [
            'full-name'    => '{employee_name}',
            'current-time' => '{current_time}'
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    public function trigger( $emp_id = null ) {

        if ( ! $emp_id ) {
            return;
        }

        $employee          = new \WeDevs\ERP\HRM\Employee( intval( $emp_id ) );

        $this->recipient   = $employee->user_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'    => $employee->display_name,
            'current-time' => current_time( 'mysql' )
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
}
