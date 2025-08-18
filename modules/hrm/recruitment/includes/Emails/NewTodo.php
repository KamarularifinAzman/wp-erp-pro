<?php
namespace WeDevs\Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class NewTodo extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'new-todo';
        $this->title       = __( 'New To-do Assigned', 'erp-pro' );
        $this->description = __( 'New To-do notification.', 'erp-pro' );

        $this->subject     = __( 'To-do notification', 'erp-pro');
        $this->heading     = __( 'To-do Notification', 'erp-pro');

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
        $html = 'To-do Title: '.$data['title'].'<br/>';
        $html .= 'To-do Deadline: '.$data['deadline_date'];

        $ids = explode(',', $data['assigned_user_id']);

        foreach ( $ids as $inv_id ) {
            $author_obj = get_user_by('ID', $inv_id);
            if ( ! empty( $author_obj ) && ! empty( $author_obj->user_email ) ) {
                $this->send( $author_obj->user_email, $this->get_subject(), $html, $this->get_headers(), $this->get_attachments() );
            }
        }
    }
}
