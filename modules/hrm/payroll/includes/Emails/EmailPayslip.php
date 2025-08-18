<?php
namespace WeDevs\Payroll\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class EmailPayslip extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'payslip-custom';
        $this->title       = __( 'Employee Payslip', 'erp-pro' );
        $this->description = __( 'Employee Payslip Notification.', 'erp-pro' );

        $this->subject     = __( 'Employee notification', 'erp-pro' );
        $this->heading     = __( 'Employee Notification heading', 'erp-pro' );

        $this->find = [
            'full-name'       => '{full_name}',
            'first-name'      => '{first_name}',
            'last-name'       => '{last_name}'
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

    public function trigger( $data = [] ) {

        if ( empty( $data ) ) {
            return;
        }

        $employee          = new \WeDevs\ERP\HRM\Employee( $data['user_id'] );

        $this->recipient   = $employee->user_email;
        $this->heading = $this->get_option( 'heading', $this->heading );
        $this->subject = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'       => $employee->get_full_name(),
            'first-name'      => $employee->first_name,
            'last-name'       => $employee->last_name
        ];


        add_filter( 'erp_email_body', function ( $email_body ) use ( $data ) {
            $email_body .= $data['content'];
            return $email_body;
        });

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
}
