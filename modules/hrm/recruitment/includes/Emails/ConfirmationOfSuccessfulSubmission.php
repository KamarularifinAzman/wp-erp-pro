<?php
namespace WeDevs\Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Confirmation Of Successful Submission
 */
class ConfirmationOfSuccessfulSubmission extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'new-job-application-submitted-to-applicant';
        $this->title       = __( 'Confirmation Of Successful Submission', 'erp-pro' );
        $this->description = __( 'Sending a confirmation to the applicant for successful submission', 'erp-pro' );

        $this->subject     = __( 'You successfully applied for the job', 'erp-pro');
        $this->heading     = __( 'You successfully applied for the job');

        $this->find = [
            'applicant-name'    => '{applicant_name}',
            'date'              => '{date}',
            'position'          => '{position}'
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

        $this->recipient  = $data['recipient'];
        $this->heading    = $this->get_option( 'heading', $this->heading );
        $this->subject    = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'applicant-name' => $data['applicant_name'],
            'date'           => $data['date'],
            'position'       => $data['position']
        ];

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
}
