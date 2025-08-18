<?php
namespace WeDevs\ERP\Workflow;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

use WeDevs\ERP\Workflow\Models\Workflow;
use WeDevs\ERP\Workflow\Models\Condition;
use WeDevs\ERP\Workflow\Models\Action;

/**
 * Ajax Class
 *
 * @package WP-ERP
 * @subpackage Workflow
 */
class AjaxHandler {

    use Ajax;
    use Hooker;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_wf_fetch_workflow', 'fetch_workflow' );
        $this->action( 'wp_ajax_erp_wf_new_workflow', 'new_workflow' );
        $this->action( 'wp_ajax_erp_wf_edit_workflow', 'update_workflow' );
        $this->action( 'wp_ajax_erp_wf_get_employees', 'get_employees' );
        $this->action( 'wp_ajax_erp_wf_get_contacts', 'get_contacts' );
        $this->action( 'wp_ajax_erp_wf_get_crm_users', 'get_crm_users' );
    }

    /**
     * Fetch a workflow.
     *
     * @return void
     */
    public function fetch_workflow() {
        $workflow_id = $_REQUEST['id'];
        $workflow = erp_wf_get_workflow( $workflow_id );

        $workflow_data = $workflow->toArray();

        $workflow_data['conditions'] = [];
        $conditions = Condition::select( 'condition_name', 'operator', 'value' )->where( 'workflow_id', $workflow_id )->get();

        if ( ! empty( $conditions ) ) {
            $workflow_data['conditions'] = $conditions->toArray();
        }

        $workflow_data['actions'] = [];
        $actions = Action::select('name', 'params')->where( 'workflow_id', $workflow_id )->get();

        if ( ! empty( $actions ) ) {
            $actions = array_map( function( $action ) {
                $action['title'] = ucwords( str_replace( '_', ' ', $action['name'] ) );
                $params = unserialize( $action['params'] );
                unset( $action['params'] );

                foreach ( $params as $key => $value ) {
                    $action[ $key ] = $value;
                }

                return $action;
            }, $actions->toArray() );

            $workflow_data['actions'] = $actions;
        }

        $this->send_success( $workflow_data );
    }

    /**
     * Create new workflow.
     *
     * @return void
     */
    public function new_workflow() {
        $this->verify_nonce( 'erp-wf-new-workflow' );

        $workflow_data = [
            'name'             => sanitize_text_field( $_POST['workflow_name'] ),
            'events_group'     => isset( $_POST['events_group'] ) ? sanitize_key( $_POST['events_group'] ) : '',
            'event'            => isset( $_POST['event'] ) ? sanitize_key( $_POST['event'] ) : '',
            'conditions_group' => sanitize_key( $_POST['conditions_group'] ),
            'status'           => ( isset( $_POST['activate'] ) && $_POST['activate'] == 'true' ) ? 'active' : 'paused',
            'delay_time'       => intval( $_POST['delay_time'] ),
            'delay_period'     => sanitize_text_field( $_POST['delay_period'] ),
            'created_by'       => get_current_user_id(),
        ];

        $workflow = Workflow::create( $workflow_data );

        $conditions_data = [];
        if ( isset( $_POST['conditions'] ) && ! empty( $_POST['conditions'] ) ) {
            $i = 0;
            foreach ( $_POST['conditions'] as $condition ) {
                $conditions_data[ $i ]                = $condition;
                $conditions_data[ $i ]['workflow_id'] = $workflow->id;

                $i++;
            }

            Condition::insert( $conditions_data );
        }

        $actions_data = [];
        if ( isset( $_POST['actions'] ) && ! empty( $_POST['actions'] ) ) {
            $i = 0;
            foreach ( $_POST['actions'] as $action ) {
                $actions_data[ $i ]['name'] = $action['name'];
                unset( $action['name'] );
                unset( $action['title'] );

                foreach ( $action as $key => $value ) {
                    $actions_data[ $i ]['params'][ $key ] = ! is_array( $value ) ? stripslashes( $value ) : $value;
                }

                // Serialize the params
                $actions_data[ $i ]['params']      = serialize( $actions_data[ $i ]['params'] );
                $actions_data[ $i ]['workflow_id'] = $workflow->id;

                $i++;
            }

            Action::insert( $actions_data );
        }

        $logger = erp_log();

        $message = sprintf(
            '<a href="%s">%s</a> has been created.',
            admin_url( 'admin.php?page=erp-workflow&action=edit&id=' . $workflow->id ),
            $workflow->name
        );

        $log_data = [
            'component'     => 'General',
            'sub_component' => 'Workflow',
            'message'       => $message,
            'changetype'    => 'add',
            'created_by'    => get_current_user_id(),
        ];

        $logger->add( $log_data );

        $this->send_success();
    }

    /**
     * Update a workflow.
     *
     * @return void
     */
    public function update_workflow() {
        $this->verify_nonce( 'erp-wf-edit-workflow' );

        $workflow_id = $_POST['workflow_id'];

        $workflow_data = [
            'name'             => sanitize_text_field( $_POST['workflow_name'] ),
            'events_group'     => isset( $_POST['events_group'] ) ? sanitize_key( $_POST['events_group'] ) : '',
            'event'            => isset( $_POST['event'] ) ? sanitize_key( $_POST['event'] ) : '',
            'conditions_group' => sanitize_key( $_POST['conditions_group'] ),
            'delay_time'       => intval( $_POST['delay_time'] ),
            'delay_period'     => sanitize_text_field( $_POST['delay_period'] ),
            'created_by'       => get_current_user_id(),
        ];

        Workflow::find( $workflow_id )->update( $workflow_data );

        Condition::where( 'workflow_id', $workflow_id )->delete();

        $conditions_data = [];
        if ( isset( $_POST['conditions'] ) && ! empty( $_POST['conditions'] ) ) {
            $i = 0;
            foreach ( $_POST['conditions'] as $condition ) {
                $conditions_data[ $i ]                = $condition;
                $conditions_data[ $i ]['workflow_id'] = $workflow_id;

                $i++;
            }

            Condition::insert( $conditions_data );
        }

        Action::where( 'workflow_id', $workflow_id )->delete();

        $actions_data = [];
        if ( isset( $_POST['actions'] ) && ! empty( $_POST['actions'] ) ) {
            $i = 0;
            foreach ( $_POST['actions'] as $action ) {
                $actions_data[ $i ]['name'] = $action['name'];
                unset( $action['name'] );
                unset( $action['title'] );

                foreach ( $action as $key => $value ) {
                    $actions_data[ $i ]['params'][ $key ] = ! is_array( $value ) ? stripslashes( $value ) : $value;
                }

                // Serialize the params
                $actions_data[ $i ]['params']      = serialize( $actions_data[ $i ]['params'] );
                $actions_data[ $i ]['workflow_id'] = $workflow_id;

                $i++;
            }

            Action::insert( $actions_data );
        }

        $logger = erp_log();

        $message = sprintf(
            '<a href="%s">%s</a> has been updated.',
            admin_url( 'admin.php?page=erp-workflow&action=edit&id=' . $workflow_id ),
            $workflow_data['name']
        );

        $log_data = [
            'component'     => 'General',
            'sub_component' => 'Workflow',
            'message'       => $message,
            'changetype'    => 'change',
            'created_by'    => get_current_user_id(),
        ];

        $logger->add( $log_data );

        $this->send_success();
    }

    /**
     * Get employees.
     *
     * @return array
     */
    public function get_employees() {
        $this->verify_nonce( 'erp-wf-fetch-users' );

        $employees = [];

        if ( erp_is_module_active( 'hrm' ) ) {
            $orgs           = [];
            $args['number'] = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : 20;

            if ( isset( $_REQUEST['s'] ) ) {
                $args['s'] = $_REQUEST['s'];
            }

            $empls = erp_hr_get_employees( $args );

            foreach( $empls as $employee ) {
                $employees[] = [
                    'id'   => $employee->get_user_id(),
                    'name' => $employee->get_full_name()
                ];
            }
        }

        $this->send_success( $employees );
    }

    /**
     * Get contacts
     *
     * @return array
     */
    public function get_contacts() {
        $this->verify_nonce( 'erp-wf-fetch-users' );

        $contacts  = [];

        if ( erp_is_module_active( 'crm' ) ) {
            $orgs           = ['type' => 'contact'];
            $args['number'] = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : 20;

            if ( isset( $_REQUEST['s'] ) ) {
                $args['s'] = $_REQUEST['s'];
            }

            $conts = erp_get_peoples( $args );

            foreach( $conts as $contact ) {
                $contacts[] = [
                    'id'   => $contact->id,
                    'name' => $contact->first_name . ' ' . $contact->last_name,
                ];
            }
        }

        $this->send_success( $contacts );
    }

    /**
     * Get CRM users
     *
     * @return array
     */
    public function get_crm_users() {
        $this->verify_nonce( 'erp-wf-fetch-users' );

        $crm_users = [];

        if ( erp_is_module_active( 'crm' ) ) {
            $orgs           = [];
            $args['number'] = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : 20;

            if ( isset( $_REQUEST['s'] ) ) {
                $args['s'] = $_REQUEST['s'];
            }

            $crm_usrs = erp_crm_get_crm_user( $args );

            foreach( $crm_usrs as $user ) {
                $crm_users[] = [
                    'id'   => $user->ID,
                    'name' => $user->first_name . ' ' . $user->last_name,
                ];
            }
        }

        $this->send_success( $crm_users );
    }
}
