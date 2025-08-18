<?php
namespace WeDevs\AdvancedLeave\Multilevel;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WP_Error;
use WeDevs\ERP\HRM\Models\LeaveRequest;
use WeDevs\ERP\HRM\Models\LeaveApprovalStatus;

class Multilevel {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'erp_settings_hr_leave_section_fields', array( $this, 'leave_settings_fields' ) );

        if ( get_option( 'erp_pro_multilevel_approval' ) !== 'yes' ) {
            return;
        }

        $this->include_files();

        add_action( 'wp_ajax_erp_pro_hr_leave_multilevel_approval', array( $this, 'get_multilevel_approvals' ) );

        add_filter( 'erp_leave_request_employee_name_column', array( $this, 'list_employee_expand_icon' ), 10, 2 );
        add_filter( 'erp_hr_email_classes', array( $this, 'leave_forward_email' ) );
        add_filter( 'email_settings_enable_filter', array( $this, 'leave_forward_email_setting' ) );

        //requests table action filter
        add_filter( 'erp_leave_request_row_actions', array( $this, 'request_row_actions' ), 10, 2 );

        //
        add_action( 'erp_hr_leave_request_before_process', array( $this, 'handle_leave_request' ), 10, 3 );

        //send email to team lead after a request is made
        add_filter( 'erp_new_leave_request_notification_recipients', array( $this, 'notify_team_lead' ), 10, 2 );
    }

    /**
     * Include files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function include_files() {
        require_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Multilevel/functions.php';
        new ForwardedLeaveRequest();
    }

    /**
     * Initialize leave forward email class
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function leave_forward_email( $emails ) {
        $emails['ForwardedLeaveRequest'] = new ForwardedLeaveRequest();

        return $emails;
    }

    /**
     * leave settings fields
     *
     * @since 1.0.0
     *
     * @param $fields array
     *
     * @return array
     */
    public function leave_settings_fields( $fields ) {
        $fields['leave'][] = [
            'title' => esc_html__( 'Enable Multilevel Approval', 'erp-pro' ),
            'type'  => 'checkbox',
            'id'    => 'erp_pro_multilevel_approval',
            'desc'  => esc_html__( 'Multilevel leave request approval.', 'erp-pro' ),
        ];

        return $fields;
    }

    /**
     * employee name expand icon
     *
     * @param $employee_name
     * @param $req_id
     *
     * @return string
     * @since 1.0.0
     */
    public function list_employee_expand_icon( $str, $req_id ) {
        $title = __( 'Multilevel Leave', 'erp-pro' );
        $color = '#0073aa;';
        $request = LeaveRequest::find( $req_id );

        if ( $request->last_status == 2 ) {
            global $wpdb;
            $status = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT approval_status_id FROM {$wpdb->prefix}erp_hr_leave_approval_status WHERE leave_request_id = %d ORDER BY id DESC LIMIT 1",
                    array( $request->id )
                )
            );
            if ( ! empty( $status ) && null !== $status->approval_status_id ) {
                if ( $status->approval_status_id == 1 ) {
                    $color = '#7ad03a;';
                } elseif ( $status->approval_status_id == 3 ) {
                    $color = '#dd3d36;';
                }
            } else {
                $color = '#7d7d7d;';
            }
        }

        return $str . "<span class='advanced-leave-req-expand dashicons dashicons-plus' style='color: $color' data-req-id='$req_id' title='$title'></span>";
    }

    /**
     * Get multilevel approvals data
     */
    public function get_multilevel_approvals() {
        if ( ! check_ajax_referer( 'erp-pro-nonce' ) ) {
            wp_die( __( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        if ( current_user_can( 'erp_leave_manage' ) && erp_hr_is_current_user_dept_lead() ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $req_id = isset( $_REQUEST['req_id'] ) ? absint( wp_unslash( $_REQUEST['req_id'] ) ) : 0;

        if ( $req_id ) {
            $approvals = LeaveApprovalStatus::where( 'leave_request_id', $req_id )->get();

            $formatted_data = array();

            foreach ( $approvals as $approval ) {
                $forward_to_name = ! empty( $approval->leave_forward_to ) ? $approval->leave_forward_to->display_name : '-';

                $formatted_data[] = array(
                    'id'               => $approval->id,
                    'request_id'       => $approval->leave_request_id,
                    'approved_by_name' => $approval->leave_approved_by->display_name,
                    'forward_to_name'  => $forward_to_name,
                    'approval_status'  => '<span class="status-' . $approval->approval_status_id . '">' . erp_hr_leave_request_get_statuses( $approval->approval_status_id ) . '</span>',
                    'created_at'    => erp_format_date( $approval->created_at ),
                    'message'          => $approval->message,
                );
            }

            wp_send_json_success( $formatted_data );
        }

        wp_send_json_error();
    }

    /**
     * Include modal view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function include_approval_modal() {
        erp_get_js_template( ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Multilevel/modal.php', 'pro-leave-approval-modal' );
    }

    /**
     * Process request forward
     *
     * @return void|WP_Error
     * @since 1.0.0
     */
    public function process_request_forward() {
        if ( ! check_ajax_referer( 'erp-pro-hr-leave' ) ) {
            wp_die( esc_html__( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        if ( current_user_can( 'erp_leave_manage' ) && erp_hr_is_current_user_dept_lead() ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $request_id = isset( $_POST['request_id'] ) ? absint( wp_unslash( $_POST['request_id'] ) ) : '';
        $forward_to = isset( $_POST['forward_to'] ) ? absint( wp_unslash( $_POST['forward_to'] ) ) : '';
        $comments   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

        $request = LeaveRequest::find( $request_id );

        if ( empty( $request ) ) {
            return new WP_Error( 'no-request-found', __( 'Invalid leave request', 'erp-pro' ) );
        }

        if ( $comments === '' ) {
            return new WP_Error( 'no-leave-reason', __( 'Please provide a forward reason for given leave request.', 'erp-pro' ) );
        }

        $res = erp_pro_hr_leave_insert_forward_leave_request( $request_id, $forward_to, $comments );

        if ( $res ) {
            $this->send_forward_eamil( $request->id, $forward_to );

            $request->last_status = 4;
            $request->save();
            wp_send_json_success( true );
        }

        wp_send_json_error( false );
    }

    /**
     * Trigger forward email
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function send_forward_eamil( $request_id, $forward_to ) {
        // get recipient user date
        $forward_to_user = get_userdata( $forward_to );

        $forwarded_email = wperp()->emailer->get_email( 'ForwardedLeaveRequest' );

        if ( is_a( $forwarded_email, '\WeDevs\ERP\Email' ) ) {
            $forwarded_email->trigger( $request_id, $forward_to_user->user_email );
        }
    }

    /**
     * Forward leave email setting
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function leave_forward_email_setting( $settings ) {
        $settings[] = 'erp_email_settings_forwarded-leave-request';

        return $settings;
    }

    /**
     * Add forward row action to list item
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_forward_row_action( $actions, $request ) {
        if ( current_user_can( 'erp_leave_manage' ) && erp_hr_is_current_user_dept_lead() ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $actions['forward'] = sprintf(
            '<a class="erp-hr-pro-leave-forward-btn" data-id="%d" href="#">%s</a>',
            $request->id,
            __( 'Forward', 'erp-pro' )
        );

        return $actions;
    }

    /**
     * Leave status
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function leave_status( $statuses ) {
        $statuses['4'] = esc_attr__( 'Forwarded', 'erp-pro' );
        return $statuses;
    }

    /**
     * This method will remove action links based on user permission
     *
     * @since 1.0.0
     * @param array $actions
     * @param object $request
     *
     * @return array
     */
    public function request_row_actions( $actions, $request ) {
        if ( erp_hr_is_current_user_dept_lead() && ! current_user_can( 'erp_leave_manage' ) ) {
            if ( $request->status == '2' ) {
                if ( isset( $actions['pending'] ) ) {
                    unset( $actions['pending'] );
                }

                if ( isset( $actions['delete'] ) ) {
                    unset( $actions['delete'] );
                }
            } else {
                $actions = array();
            }
        }

        return $actions;
    }

    /**
     * This method will store team lead responses
     *
     * @since 1.0.0
     * @param $request_id
     * @param $status
     * @param $comments
     */
    public function handle_leave_request( $request_id, $status, $comments ) {
        if ( $status != '1' && $status != '3' ) {
            return;
        }

        if ( erp_hr_is_current_user_dept_lead() && ! current_user_can( 'erp_leave_manage' ) ) {
            $request = LeaveRequest::find( $request_id );

            if ( empty( $request ) ) {
                die( __( 'Invalid leave request', 'erp-pro' ) );
            }

            if ( $comments === '' ) {
                die( __( 'Please provide a reason for given leave request.', 'erp-pro' ) );
            }

            $approval = LeaveApprovalStatus::create( array(
                'leave_request_id'   => $request_id,
                'approval_status_id' => $status,
                'approved_by'        => get_current_user_id(),
                'message'            => $comments,
            ) );

            wp_send_json_success( array(
				'data' => $request,
				'redirect' => 2,
			) );
            exit();
        }
    }

    function notify_team_lead( $recipients, $request ) {
        $dept_lead = erp_hr_get_department_lead_by_user( $request->user_id );

        if ( $dept_lead ) {
            $user = get_user_by( 'id', $dept_lead );
            if ( $user instanceof \WP_User ) {
                $recipients[] = $user->user_email;
            }
        }

        return $recipients;
    }

}
