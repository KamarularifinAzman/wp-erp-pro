<?php
namespace WeDevs\AssetManagement;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Asset Request
 */
class EmployeeAssetApprove extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'employee-asset-approve';
        $this->title          = __( 'Approved Asset Request', 'erp-pro' );
        $this->description    = __( 'Asset approvement email to employee.', 'erp-pro' );

        $this->subject        = __( 'Your asset request was approved', 'erp-pro');
        $this->heading        = __( 'Asset request approved', 'erp-pro');

        $this->find = [
            'full-name'      => '{employee_name}',
            'request_desc'   => '{request_desc}',
            'date_requested' => '{date_requested}',
            'item_group'     => '{item_group}',
            'item_code'      => '{item_code}',
            'mdoel_no'       => '{model_no}'
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

    public function trigger( $data = null ) {

        if ( ! $data ) {
            return;
        }

        $this->recipient = $data->user_email;
        $this->heading   = $this->get_option( 'heading', $this->heading );
        $this->subject   = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'      => $data->display_name,
            'request_desc'   => $data->request_desc,
            'date_requested' => erp_format_date( $data->date_requested ),
            'item_group'     => $data->item_group,
            'item_code'      => $data->item_code,
            'model_no'       => $data->model_no
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
}

