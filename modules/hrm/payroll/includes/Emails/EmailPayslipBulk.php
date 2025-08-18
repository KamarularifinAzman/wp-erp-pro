<?php
namespace WeDevs\Payroll\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class EmailPayslipBulk extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'payslip-bulk';
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

        // loop to send bulk email
        foreach ( $data['sumdata'] as $sdata ) {
            $uemail         = get_user_by( 'user_email', $sdata['user_id'] );
            $udisplay_name  = get_user_by( 'display_name', $sdata['user_id'] );
            $payrun_payment = $sdata['payrun_payment'];
            $payslip_note   = $sdata['note'];

            /*making mail body*/
            $html = 'Payment Date: '. $data['payment_date'] .'<br/>';
            $html .= 'Pay Period: '. $data['from_date'] .'-'. $data['to_date'] . '<br/>';
            $html .= 'Amount Paid: '. $payrun_payment . '<br/>';
            $html .= 'Pay Slip Note: '. $payslip_note . '<br/>';

            $author_obj = get_user_by('ID', $sdata['user_id']);
            $this->send( $author_obj->user_email, $this->get_subject(), $html, $this->get_headers(), $this->get_attachments() );
        }

    }
}
