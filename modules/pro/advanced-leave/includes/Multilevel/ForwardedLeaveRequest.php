<?php
namespace WeDevs\AdvancedLeave\Multilevel;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Forwarded Leave Request
 */
class ForwardedLeaveRequest extends Email {

    use Hooker;

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    function __construct() {
        $this->id          = 'forwarded-leave-request';
        $this->title       = esc_html__( 'Forwarded Leave Request', 'erp' );
        $this->description = esc_html__( 'Forwarded leave request notification to leave manager.', 'erp' );
        $this->subject     = esc_html__( 'A leave request has been forwarded', 'erp');
        $this->heading     = esc_html__( 'Leave Request Forwarded', 'erp');

        $this->find = [
            'full-name'    => '{employee_name}',
            'leave_type'   => '{leave_type}',
            'date_from'    => '{date_from}',
            'date_to'      => '{date_to}',
            'no_days'      => '{no_days}',
            'reason'       => '{reason}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    /**
     * Get arguments
     *
     * @since 1.0.0
     *
     * @return array
     */
    function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    /**
     * Trigger mail
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function trigger( $request_id = null, $recipient_email ) {
        $request = erp_hr_get_leave_request( $request_id );

        if ( ! $request ) {
            return;
        }

        $this->recipient = $recipient_email;
        $this->heading   = $this->get_option( 'heading', $this->heading );
        $this->subject   = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'  => $request->display_name,
            'leave_type' => $request->policy_name,
            'date_from'  => erp_format_date( $request->start_date ),
            'date_to'    => erp_format_date( $request->end_date ),
            'no_days'    => $request->days,
            'reason'     => $request->reason,
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

}
