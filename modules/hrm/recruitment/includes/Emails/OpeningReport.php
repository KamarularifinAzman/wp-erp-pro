<?php
namespace WeDevs\Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class OpeningReport extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'opening-report';
        $this->title       = __( 'Opening Report', 'erp-pro' );
        $this->description = __( 'Opening Repost.', 'erp-pro' );

        $this->subject     = __( 'Opening Report', 'erp-pro');
        $this->heading     = __( 'Opening Report', 'erp-pro');

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    public function get_attachments(){
        $attachments = array( WPERP_REC_PATH . '/assets/csv_reports/opening-report.csv' );
        return $attachments;
    }

    public function trigger() {
        global $current_user;

        $this->heading = $this->get_option( 'heading', $this->heading );
        $this->subject = $this->get_option( 'subject', $this->subject );

        $author_obj = get_user_by('ID', get_current_user_id());
        $this->send( $author_obj->user_email, $this->get_subject(), 'Opening Report', $this->get_headers(), $this->get_attachments() );

    }
}
