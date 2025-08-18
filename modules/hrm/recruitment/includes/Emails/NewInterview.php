<?php
namespace WeDevs\Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class NewInterview extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'new-interview';
        $this->title       = __( 'New interview Assigned', 'erp-pro' );
        $this->description = __( 'New interview notification.', 'erp-pro' );

        $this->subject     = __( 'Interview notification', 'erp-pro');
        $this->heading     = __( 'Interview Notification', 'erp-pro');

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
        $html = 'Interview Type: '.$data['type_of_interview_text'].'<br/>';
        $html .= 'Interview Description (place/phone): '.$data['interview_detail'].'<br/>';
        $html .= 'Interview DateTime: '.$data['start_date_time'].'<br/>';
        $html .= 'Interview Duration: '.$data['duration_minutes'].' Minutes'.'<br/>';

        $ids = explode(',', $data['interviewer_id']);

        foreach ( $ids as $inv_id ) {
            $author_obj = get_user_by('ID', $inv_id);
            if ( ! $author_obj ) {
                continue;
            }
            $this->send( $author_obj->user_email, $this->get_subject(), $html, $this->get_headers(), $this->get_attachments() );
        }
    }
}
