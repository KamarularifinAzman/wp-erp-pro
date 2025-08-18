<?php
namespace WeDevs\AssetManagement;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Asset Request
 */
class EmployeeAssetOverdue extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'employee-asset-overdue';
        $this->title          = __( 'Employee Asset Overdue', 'erp-pro' );
        $this->description    = __( 'Asset overdue email to employee.', 'erp-pro' );

        $this->subject        = __( 'Asset overdue notification', 'erp-pro');
        $this->heading        = __( 'Asset overdue', 'erp-pro');

        $this->find = [
            'full-name'      => '{employee_name}',
            'item_group'     => '{item_group}',
            'item_code'      => '{item_code}',
            'model_no'       => '{model_no}',
            'date_given'     => '{date_given}',
            'date_return_proposed' => '{date_return_proposed}'
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

        if ( is_array( $data ) ) {

            foreach ( $data as $single ) {

                $this->recipient = $single['ser_email'];
                $this->heading   = $this->get_option( 'heading', $this->heading );
                $this->subject   = $this->get_option( 'subject', $this->subject );

                $this->replace = [
                    'full-name'            => $single['display_name'],
                    'date_given'           => erp_format_date( $single['date_given'] ),
                    'date_return_proposed' => erp_format_date( $single['date_return_proposed'] ),
                    'item_group'           => $single['item_group'],
                    'item_code'            => $single['item_code'],
                    'model_no'             => $single['model_no']
                ];

                if ( !$this->get_recipient() ) {
                    return;
                }

                $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            }
        }

    }
}


