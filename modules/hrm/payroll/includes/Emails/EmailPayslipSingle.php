<?php
namespace WeDevs\Payroll\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class EmailPayslipSingle extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'payslip-single';
        $this->title       = __( 'New Payslip', 'erp-pro' );
        $this->description = __( 'New Payslip Notification.', 'erp-pro' );

        $this->subject     = __( 'Payslip notification', 'erp-pro' );
        $this->heading     = __( 'Payslip Notification heading', 'erp-pro' );

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
        global $current_user;

        if ( empty( $data ) ) {
            return;
        }

        $this->heading = $this->get_option( 'heading', $this->heading );
        $this->subject = $this->get_option( 'subject', $this->subject );

        /*making mail body*/
        $html = 'Payment Date: '. $data['payment_date'] .'<br/>';
        $html .= 'Pay Period: '. $data['from_date'] .'-'. $data['to_date'] . '<br/>';
        $html .= 'Amount Paid: '. $data['amount_paid'] . '<br/>';

        $author_obj = get_user_by('ID', $data['assigned_user_id']);
        $this->send( $author_obj->user_email, $this->get_subject(), $html, $this->get_headers(), $this->get_attachments() );
    }
}
