<?php
namespace WeDevs\ERP\Workflow;

use WeDevs\ERP\Workflow\Models\Workflow;
use WeDevs\ERP\Workflow\Models\Action;

class Workflows {

    protected $events = [];

    protected $actions = [];

    protected $current_event;

    protected $event_data = [];

    /**
     * Class constructor.
     */
    function __construct() {
        add_action( 'erp_wf_send_email_action', [ $this, 'run_send_email_action' ] );
        add_action( 'erp_wf_assign_task_action', [ $this, 'run_assign_task_action' ] );
        add_action( 'erp_wf_trigger_action_hook_action', [ $this, 'run_trigger_action_hook_action' ] );
        add_action( 'erp_wf_update_field_action', [ $this, 'run_update_field_action' ] );
        add_action( 'erp_wf_add_activity_action', [ $this, 'run_add_activity_action' ] );
        add_action( 'erp_wf_schedule_meeting_action', [ $this, 'run_schedule_meeting_action' ] );
        add_action( 'erp_wf_add_user_role_action', [ $this, 'run_add_user_role_action' ] );
        add_action( 'erp_wf_send_invoice_action', [ $this, 'run_send_invoice_action' ] );
    }

    /**
      * Define hooks dynamically.
      *
      * @return void
      */
    public function define_hooks() {
        $hooks = erp_wf_get_hooks();
        foreach ( $hooks as $event => $action ) {
            if ( isset( $action ) ) {
                add_action( $action, [ $this, $event ], 10, 3 );
            }
        }
    }

    /**
     * Run a workflow.
     *
     * @param  object $workflow
     *
     * @return void
     */
    function run_workflow( $workflow ) {
        $this->count_workflow_run( $workflow->id );
        $workflow_data = $workflow;

        if ( $workflow->type == 'auto' ) {
            $actions = Action::where( 'workflow_id', $workflow->id )->get();
            $workflow_data->actions = $workflow_data;

            $period = 0;
            switch ( $workflow->delay_period ) {
                case 'minute':
                    $period = 1;
                    break;
                case 'hour':
                    $period = 60;
                    break;
                case 'day':
                    $period = 1440;
                    break;
            }

            $duration = time() + ( $workflow->delay_time * $period * 60 );

            foreach ( $actions as $action ) {
                $schedule_data = [ 'action' => $action, 'event_data' => $this->event_data ];
                // The action name will be like erp_wf_send_email_action
                wp_schedule_single_event( $duration, "erp_wf_{$action->name}_action", [ $schedule_data ] );
            }
        }

        do_action( 'erp_wf_run_workflow', $workflow_data );
    }

    /**
     * Increment the run times of a workflow.
     *
     * @param  int $workflow_id
     *
     * @return void
     */
    protected function count_workflow_run( $id ) {
        Workflow::find( $id )->increment( 'run' );
    }

    /**
     * Magic call method for handling the events.
     *
     * @param  string $name
     * @param  array  $hook_arguments
     *
     * @return void
     */
    public function __call( $name, $hook_arguments ) {
        $this->event_data          = [];
        $this->event_data['event'] = $name;

        // Event name
        switch ( $name ) {
            case 'created_user':
                $user_id = $hook_arguments[0];
                $user    = get_userdata( $user_id );
                $this->event_data = array_merge( $this->event_data, [
                    'object'     => 'user',
                    'id'         => $user_id,
                    'email'      => $user->user_email,
                    'roles'      => $user->roles,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'full_name'  => $user->display_name,
                ] );

                break;
            case 'created_contact':
                $this->event_data           = array_merge( $this->event_data, $hook_arguments[1] );
                $this->event_data['id']     = intval( $hook_arguments[0] );
                $this->event_data['object'] = 'contact';

                break;
            case 'deleted_contact':
                $contact_id                 = $hook_arguments[0];
                $contact                    = (array) erp_get_people( intval( $contact_id ) );
                $this->event_data           = $contact;
                $this->event_data['object'] = 'contact';

                break;
            case 'subscribed_contact':
                $contact_id                 = $hook_arguments[0]['user_id'];
                $contact                    = (array) erp_get_people( intval( $contact_id ) );
                $this->event_data           = array_merge( $this->event_data, $contact );
                $this->event_data['object'] = 'contact';

                break;
            case 'unsubscribed_contact':
                $contact_id                 = $hook_arguments[0];
                $contact                    = (array) erp_get_people( intval( $contact_id ) );
                $this->event_data           = array_merge( $this->event_data, $contact );
                $this->event_data['object'] = 'contact';

                break;
            case 'created_note':
                $this->event_data = array_merge( $this->event_data, $hook_arguments[0] );

                break;
            case 'created_task':
                $this->event_data = array_merge( $this->event_data, $hook_arguments[0] );
                $extra            = json_decode( base64_decode( $hook_arguments[0]['extra'] ), true );
                $this->event_data = array_merge( $this->event_data, $extra );

                break;
            case 'scheduled_meeting':
                $this->event_data = array_merge( $this->event_data, $hook_arguments[0] );
                $extra            = json_decode( base64_decode( $hook_arguments[0]['extra'] ), true );
                $this->event_data = array_merge( $this->event_data, $extra );

                break;
            case 'inbound_email':
                $this->event_data               = array_merge( $this->event_data, $hook_arguments[0] );
                $this->event_data['id']         = $hook_arguments[0]['cid'];
                $this->event_data['created_by'] = $hook_arguments[0]['sid'];

                break;
            case 'created_employee':
                $employee                       = erp_wf_get_hr_employee( $hook_arguments[0] );
                $this->event_data               = array_merge( $this->event_data, $employee );
                $this->event_data['id']         = $hook_arguments[0];
                $this->event_data['object']     = 'employee';
                $this->event_data['created_by'] = get_current_user_id();

                break;
            case 'deleted_employee':
                $employee                       = erp_wf_get_hr_employee( $hook_arguments[0] );
                $this->event_data               = array_merge( $this->event_data, $employee );
                $this->event_data['id']         = $hook_arguments[0];
                $this->event_data['object']     = 'employee';
                $this->event_data['created_by'] = get_current_user_id();

                break;
            case 'published_announcement':
                $post                         = get_post( $hook_arguments[0] );
                $announcement['post_title']   = $post->post_title;
                $announcement['post_content'] = $post->post_content;
                $this->event_data             = array_merge( $this->event_data, $announcement );
                $this->event_data['id']       = $hook_arguments[0];

                break;
            case 'requested_leave':
                $request = $hook_arguments[1];

                //$policy = \WeDevs\ERP\HRM\Models\LeavePolicy::find( $request['policy_id'] );
                $leave = \WeDevs\ERP\HRM\Models\Leave::find( $request['leave_id'] );
                if ( $leave ) {
                    $request['policy']  = $leave->name;
                }

                $this->event_data       = array_merge( $this->event_data, $request );
                $this->event_data['id'] = $hook_arguments[0];

                break;
            case 'confirmed_leave_request':
                $leave_request = [];
                $request = $hook_arguments[1];
                $policy  = $request->leave;

                if ( $request ) {
                    $leave_request['reason']     = $request->reason;
                    $leave_request['start_date'] = erp_current_datetime()->setTimestamp( $request->start_date )->format( 'Y-m-d H:i:s' );
                    $leave_request['end_date']   = erp_current_datetime()->setTimestamp( $request->end_date )->format( 'Y-m-d H:i:s' );
                }

                if ( $policy ) {
                    $leave_request['policy'] = $policy->name;
                }

                $this->event_data       = array_merge( $this->event_data, $leave_request );
                $this->event_data['id'] = $hook_arguments[0];

                break;
            case 'happened_birthday_today':
                $employee               = erp_wf_get_hr_employee( $hook_arguments[0] );
                $this->event_data       = array_merge( $this->event_data, $employee );
                $this->event_data['id'] = $hook_arguments[0];

                break;
            case 'created_customer':
                $customer               = $hook_arguments[1];
                $this->event_data       = array_merge( $this->event_data, $customer );
                $this->event_data['id'] = $hook_arguments[0];

                break;
            case 'deleted_customer':
                $customer               = (array) erp_get_people( intval( $hook_arguments[0][0] ) );
                $this->event_data       = array_merge( $this->event_data, $customer );
                $this->event_data['id'] = $hook_arguments[0][0];

                break;

            case 'created_vendor':
                $vendor                 = $hook_arguments[1];
                $this->event_data       = array_merge( $this->event_data, $vendor );
                $this->event_data['id'] = $hook_arguments[0];

                break;
            case 'deleted_vendor':
                $vendor                 = (array) erp_get_people( intval( $hook_arguments[0][0] ) );
                $this->event_data       = array_merge( $this->event_data, $vendor );
                $this->event_data['id'] = $hook_arguments[0][0];

                break;
            case 'added_sale':
            case 'added_expense':
            case 'added_check':
            case 'added_bill':
            case 'added_purchase_order':
            case 'added_purchase':
                $this->event_data       = array_merge( $this->event_data, $hook_arguments[1] );
                $this->event_data['id'] = $hook_arguments[0];

                break;

            default:
                do_action( 'erp_wf_map_event_data', ['event_name' => $name, 'event_data' => $this->event_data] );
        }

        $workflows = erp_wf_get_workflows_by_event( $name );

        foreach ( $workflows as $workflow ) {
            $is_meet = erp_wf_check_workflow( $workflow->id, $this->event_data );

            if ( $is_meet ) {
                $this->run_workflow( $workflow );
            }
        }

        return;
    }

    /**
     * Run send email action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_send_email_action( $data ) {
        $params  = unserialize( $data['action']->params );

        $subject = erp_wf_replace_text_vars( $data['event_data'], $params['subject'] );
        $message = erp_wf_replace_text_vars( $data['event_data'], $params['message'] );

        if ( ! empty( $params['user'] ) && ! json_decode( $params['send_itself'] ) ) {
            foreach ( $params['user'] as $user ) {
                $employee = new \WeDevs\ERP\HRM\Employee( intval( $user['id'] ) );
                $to       = $employee->user_email;
                erp_mail( $to, $subject, $message );
            }
        } else {
            $to = $data['event_data']['email'];
            erp_mail( $to, $subject, $message );
        }

        return;
    }

    /**
     * Run assign task action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_assign_task_action( $data ) {
        $params     = unserialize( $data['action']->params );

        $task_title = erp_wf_replace_text_vars( $data['event_data'], $params['task_title'] );
        $message    = erp_wf_replace_text_vars( $data['event_data'], $params['message'] );

        $employee_ids = [];
        foreach ( $params['user'] as $employee ) {
            $employee_ids[] = $employee['id'];
        }

        $extra_data = [
            'task_title'     => $task_title,
            'invite_contact' => $employee_ids,
        ];

        $post_data = [
            'type'       => 'tasks',
            'message'    => $message,
            'start_date' => date( 'Y-m-d H:i:s', strtotime( $params['task_date'] . $task_title ) ),
            'user_id'    => ( $data['event_data']['object'] === 'employee' ) ? $params['contact_id'] : $data['event_data']['id'],
            'created_by' => $data['event_data']['created_by'],
            'extra'      => base64_encode( json_encode( $extra_data ) ),
        ];

        erp_crm_save_customer_feed_data( $post_data );

        return;
    }

    /**
     * Run trigger action hook action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_trigger_action_hook_action( $data ) {
        $params = unserialize( $data['action']->params );
        do_action( $params['hook_name'], $data['event_data'] );

        return;
    }

    /**
     * Run update field action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_update_field_action( $data ) {
        $params = unserialize( $data['action']->params );

        if ( isset( $data['event_data']['object'] ) ) {
            switch ( $data['event_data']['object'] ) {
                case 'user':
                    $user_id = (int) $data['event_data']['id'];

                    switch ( $params['field_name'] ) {
                        case 'email':
                            $field_name = 'user_email';
                            break;
                        case 'full_name':
                            $field_name = 'display_name';
                            break;

                        default:
                            $field_name = $params['field_name'];
                            break;
                    }

                    wp_update_user( array( 'ID' => $user_id, $field_name => $params['field_value'] ) );

                    break;

                case 'contact':
                    $contact = $data['event_data'];

                    if ( ! empty( $params['field_name'] ) ) {
                        $contact[ $params['field_name'] ] = $params['field_value'];
                    }

                    erp_insert_people( $contact );

                    break;

                case 'employee':
                    $user_id = $data['event_data']['id'];

                    $args = [
                        'user_id'     => $user_id,
                        'field_name'  => $params['field_name'],
                        'field_value' => $params['field_value'],
                    ];

                    erp_wf_hr_update_employee( $args );

                    break;
            }
        }

        return;
    }

    /**
     * Run add activity action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_add_activity_action( $data ) {
        $params  = unserialize( $data['action']->params );
        $subject = erp_wf_replace_text_vars( $data['event_data'], $params['subject'] );
        $message = erp_wf_replace_text_vars( $data['event_data'], $params['message'] );

        $extra_data = [];
        if ( ! empty( $params['invite_contact'] ) ) {
            $contact_ids = [];
            foreach ( $params['invite_contact'] as $contact ) {
                $contact_ids[] = $contact['id'];
            }

            $extra_data = [
                'invite_contact' => $contact_ids,
            ];
        }

        $post_data = [
            'type'          => 'log_activity',
            'log_type'      => $params['log_type'],
            'message'       => $message,
            'email_subject' => isset( $subject ) ? $subject : '',
            'start_date'    => date( 'Y-m-d H:i:s', strtotime( $params['start_date'] . $params['start_time'] ) ),
            'user_id'       => $data['event_data']['id'],
            'created_by'    => $data['event_data']['created_by'],
            'extra'         => base64_encode( json_encode( $extra_data ) ),
        ];

        erp_crm_save_customer_feed_data( $post_data );

        return;
    }

    /**
     * Run schedule meeting action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_schedule_meeting_action( $data ) {
        $params         = unserialize( $data['action']->params );
        $schedule_title = erp_wf_replace_text_vars( $data['event_data'], $params['schedule_title'] );
        $message        = erp_wf_replace_text_vars( $data['event_data'], $params['message'] );

        $contact_ids = [];
        foreach ( $params['invite_contact'] as $contact ) {
            $contact_ids[] = $contact['id'];
        }

        $post_data = [
            'schedule_type'              => $params['schedule_type'],
            'schedule_title'             => $schedule_title,
            'message'                    => $message,
            'start_date'                 => $params['start_date'],
            'start_time'                 => $params['start_time'],
            'end_date'                   => $params['end_date'],
            'end_time'                   => $params['end_time'],
            'all_day'                    => $params['all_day'],
            'allow_notification'         => $params['allow_notification'],
            'notification_via'           => $params['notification_via'],
            'notification_time'          => $params['notification_time'],
            'notification_time_interval' => $params['notification_time_interval'],
            'user_id'                    => $data['event_data']['id'],
            'created_by'                 => $data['event_data']['created_by'],
            'invite_contact'             => $contact_ids,
            'client_time_zone'           => ! empty( $params['client_time_zone'] ) ? sanitize_text_field( wp_unslash( $params['client_time_zone'] ) ) : '',
        ];

        $save_data = erp_crm_customer_prepare_schedule_postdata( $post_data );
        erp_crm_save_customer_feed_data( $save_data );

        return;
    }

    /**
     * Run add user role action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_add_user_role_action( $data ) {
        $params = unserialize( $data['action']->params );
        $user   = get_userdata( $data['event_data']['id'] );
        $user->add_role( $params['role'] );

        return;
    }

    /**
     * Run send invoice action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function run_send_invoice_action( $data ) {
        $params  = unserialize( $data['action']->params );

        $subject = erp_wf_replace_text_vars( $data['event_data'], $params['subject'] );
        $message = erp_wf_replace_text_vars( $data['event_data'], $params['message'] );

        if ( version_compare( WPERP_VERSION , '1.4.0', '>' ) ) {
            $this->send_invoice_from_new_accounting( $data, $params, $subject, $message );
        } else {
            $this->send_invoice_from_old_accounting( $data, $params, $subject, $message );
        }

        return;
    }

    /**
     * Send invoice from new accounting
     *
     * @param  array $data
     * @param  array $params
     *
     * @return void
     */
    protected function send_invoice_from_new_accounting( $data, $params, $subject, $message ) {
        $receivers = [];

        if ( ! empty( $params['user'] ) && ! json_decode( $params['send_itself'] ) ) {
            foreach ( $params['user'] as $user ) {
                $employee = new \WeDevs\ERP\HRM\Employee( intval( $user['id'] ) );
                $receivers[] = $employee->user->user_email;
            }
        } else {
            $receivers[] = $data['event_data']['email'];
        }

        erp_acct_send_email_with_pdf_attached([
            'receiver'   => $receivers,
            'subject'    => $subject,
            'message'    => $message,
            'trn_data'   => $data['event_data'],
            'attachment' => 'on'
        ], 'F');
    }

    /**
     * Send invoice from old accounting
     *
     * @param  array $data
     * @param  array $params
     *
     * @return void
     */
    protected function send_invoice_from_old_accounting( $data, $params, $subject, $message ) {
        $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $data['event_data']['id'] );
        $pdf         = erp_ac_pdf_link_generator( $transaction );

        if ( ! empty( $params['user'] ) && ! json_decode( $params['send_itself'] ) ) {
            foreach ( $params['user'] as $user ) {
                $employee = new \WeDevs\ERP\HRM\Employee( intval( $user['id'] ) );
                $to       = $employee->user->user_email;
                erp_mail( $to, $subject, $message, '', [ $pdf ] );
            }
        } else {
            $customer = (array) erp_get_people( intval( $data['event_data']['user_id'] ) );
            $to       = $customer['email'];
            erp_mail( $to, $subject, $message, '', [ $pdf ] );
        }

        unlink( $pdf );
    }
}
