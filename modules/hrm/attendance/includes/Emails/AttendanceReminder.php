<?php

namespace WeDevs\Attendance\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\HRM\Employee;

/**
 * Approved Leave Request
 */
class AttendanceReminder extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'attendance-reminder';
        $this->title       = __( 'Attendance Reminder', 'erp' );
        $this->description = __( 'Sends email to remind for checking-in', 'erp' );

        $this->subject = __( 'Attendance Reminder for {date}', 'erp' );
        $this->heading = __( 'Attendance Reminder for {date}', 'erp' );

        $this->find = [
            'full-name' => '{employee_name}',
            'date'      => '{date}',
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

    public function trigger( Employee $employee ) {
        if ( ! $employee->is_employee() ) {
            return;
        }

        $this->recipient = $employee->user_email;
        $this->heading   = $this->get_option( 'heading', $this->heading );
        $this->subject   = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name' => $employee->get_full_name(),
            'date'      => erp_format_date( current_time( 'mysql' ) ),
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    /**
     * Template tags
     *
     * @return void
     */
    function replace_keys() {
        ?>
        <tr valign="top" class="single_select_page">
            <th scope="row" class="titledesc"><?php _e( 'Template Tags', 'erp' ); ?></th>
            <td class="forminp">
                <em><?php _e( 'You may use these template tags inside subject, heading, body and those will be replaced by original values', 'erp' ); ?></em>:
                <?php echo '<code>' . implode( '</code>, <code>', $this->find ) . '</code>'; ?>
            </td>
        </tr>
        <?php
    }

}
