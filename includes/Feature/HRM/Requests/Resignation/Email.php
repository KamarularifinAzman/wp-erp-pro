<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Resignation;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Email handler class
 * 
 * @since 1.2.0
 */
class Email {

    use Hooker;

    /**
     * Class constructor
     * 
     * @since 1.2.0
     */
    function __construct() {
        $this->action( 'erp_hr_employee_after_resign_request', 'send_email' );
    }

    /**
     * Retrieves hr emails
     * 
     * @since 1.2.0
     *
     * @return string
     */
    public function get_hr_email() {
        $hr_managers = get_users( [
            'role'    => 'erp_hr_manager',
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        ] );

        $email_recipient = '';

        foreach ( $hr_managers as $hr ) {
            $email_recipient .= $hr->user_email . ',';
        }

        return $email_recipient;
    }

    /**
     * Send resign request email to hr
     *
     * @since 1.2.0
     * 
     * @param array $data
     *
     * @return mixed
     */
    public function send_email( $data ) {
        $email              = new \WeDevs\ERP\Email();
        $email->id          = 'resign-request-email-to-hr';
        $email->title       = __( 'New Resignation Request', 'erp-pro' );
        $email->description = __( 'Notification for New Resignation Request', 'erp-pro' );
        $email->subject     = __( 'Resignation Letter', 'erp-pro' );
        $email->heading     = '';
        $email->recipient   = $this->get_hr_email();

        $email_body         = $email->get_template_content( WPERP_INCLUDES . '/email/email-body.php', [
            'email_heading' => $email->heading,
            'email_body'    => wpautop( $this->generate_email_body( $data ) )
        ] );

        return erp_mail( $email->get_recipient(), $email->get_subject(), wordwrap( $email->format_string( $email_body ) ), $email->get_headers(), $email->get_attachments() );
    }

    /**
     * Generates email body
     * 
     * @since 1.2.0
     *
     * @param array $data
     * 
     * @return string
     */
    public function generate_email_body( $data ) {
        $employee    = new \WeDevs\ERP\HRM\Employee( $data['user_id'] );
        $company     = new \WeDevs\ERP\Company();
        $description = $data['description'];
        $contact     = ! empty( $employee->get_work_phone() ) ? $employee->get_work_phone() : (
                       ! empty( $employee->get_mobile() )     ? $employee->get_mobile()     : (
                       ! empty( $employee->get_phone() )      ? $employee->get_phone()      : $employee->user_email ) );

        if ( empty( $description ) ) {
            $description = erp_hr_get_default_resign_email_body( [
                'designation' => $employee->get_job_title(),
                'date'        => $data['date'],
                'company'     => $company->name,
                'contact'     => $contact
            ] );
        }

        ob_start();
        
        include_once ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/resign-request-email.php';
        
        return ob_get_clean();
    }
}