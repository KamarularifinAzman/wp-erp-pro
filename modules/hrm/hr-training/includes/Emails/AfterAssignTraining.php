<?php
namespace WeDevs\HrTraining\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * New Training Assign
 */
class AfterAssignTraining extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'after-assign-training';
        $this->title       = __( 'New Training Assigned', 'erp-pro' );
        $this->description = __( 'New Training Assigned.', 'erp-pro' );

        $this->subject     = __( 'New training has been assigned to you', 'erp-pro');
        $this->heading     = __( 'New Training Assigned', 'erp-pro');

        $this->find = [
            'training-name'    => '{training_name}',
            'date'             => '{date}',
            'employee-name'    => '{employee_name}'
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

        $this->recipient   = $data['recipient'];
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'training-name'  => $data['training_name'],
            'date'           => $data['date'],
            'employee-name'  => $data['employee_name']
        ];

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
}
