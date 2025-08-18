<?php

/**
 * Retrieve a workflow by id.
 *
 * @param  int $id
 *
 * @return object
 */
function erp_wf_get_workflow( $id ) {
    $workflow = \WeDevs\ERP\Workflow\Models\Workflow::find( $id );

    return $workflow;
}

/**
 * Retrieve list of workflows.
 *
 * @param  array $args
 *
 * @return array
 */
function erp_wf_get_workflows( $args = null ) {
    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
        'trashed' => false,
        's'       => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $workflows = \WeDevs\ERP\Workflow\Models\Workflow::query();

    if ( $args['number'] != '-1' && ! $args['count'] ) {
        $workflows = $workflows->skip( $args['offset'] )->take( $args['number'] );
    }

    if ( $args['trashed'] ) {
        $workflows->onlyTrashed();
    }

    if ( ! empty( $args['status'] ) && in_array( $args['status'], ['active', 'paused'] ) ) {
        $workflows->where( 'status', $args['status'] );
    }

    if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
        $workflows->where( 'name', 'LIKE', "%{$args['s']}%" );
    }

    if ( $args['count'] ) {
        return $workflows->count();
    }

    $result = $workflows->orderBy( $args['orderby'], $args['order'] )->get()->toArray();

    return erp_array_to_object( $result );
}

/**
 * Retrieve list of workflows by given field & field's values.
 *
 * @param  array $field
 * @param  mixed $value
 *
 * @return array
 */
function erp_wf_get_workflows_by( $field, $value ) {
    if ( empty( $field ) ) {
        return new WP_Error( 'no-field', __( 'No field provided', 'erp-pro' ) );
    }

    if ( empty( $value ) ) {
        return new WP_Error( 'no-value', __( 'No value provided', 'erp-pro' ) );
    }

    if ( is_array( $value ) ) {
        $workflows = \WeDevs\ERP\Workflow\Models\Workflow::whereIn( $field, $value )->get();
    } else {
        $workflows = \WeDevs\ERP\Workflow\Models\Workflow::where( $field, $value )->get();
    }

    return erp_array_to_object( $workflows->toArray() );
}

/**
 * Delete a workflow.
 *
 * @param  int     $id
 * @param  boolean $parmanent
 *
 * @return boolean
 */
function erp_wf_delete_workflow( $id, $parmanent = false ) {
    $is_deleted = false;

    if ( $parmanent ) {
        $workflow = \WeDevs\ERP\Workflow\Models\Workflow::withTrashed()->where( 'id', $id );

        if ( $workflow ) {
            $is_deleted = $workflow->forceDelete();
        }

        \WeDevs\ERP\Workflow\Models\Condition::where( 'workflow_id', $id )->delete();
        \WeDevs\ERP\Workflow\Models\Action::where( 'workflow_id', $id )->delete();
    } else {
        $workflow = \WeDevs\ERP\Workflow\Models\Workflow::where( 'id', $id );

        if ( $workflow ) {
            $is_deleted = $workflow->delete();
        }
    }

    return $is_deleted;
}

/**
 * Restore a workflow.
 *
 * @param  int $id
 *
 * @return boolean
 */
function erp_wf_restore_workflow( $id ) {
    $is_restored = \WeDevs\ERP\Workflow\Models\Workflow::withTrashed()->where('id', $id)->restore();

    return $is_restored;
}

/**
 * Update a workflow.
 *
 * @param  int   $id
 * @param  array $data
 *
 * @return boolean
 */
function erp_wf_update_workflow( $id, $data ) {
    $workflow = \WeDevs\ERP\Workflow\Models\Workflow::find( $id );

    $is_updated = false;
    if ( $workflow ) {
        $is_updated = $workflow->update( $data );
    }

    return $is_updated;
}

/**
 * Build sql conditions for a workflow.
 *
 * @param  int $workflow_id
 *
 * @return string
 */
function erp_wf_build_condtion_sql( $workflow_id ) {
    global $wpdb;

    $workflow = erp_wf_get_workflow( $workflow_id );

    $main_group = $workflow->conditions_group;


    $where .= '(';

    $conditions = $wpdb->get_results( "select * from {$wpdb->prefix}erp_workflow_conditions where workflow_id = {$workflow_id}" );

    foreach( $conditions as $condition ) {
        // @Todo neet to change operator with sql operator
        $where .= $condition->condition_name . ' ' . $condition->operator . ' ' . "'$condition->value'";

        if ( $condition != end( $conditions ) ) {
            $where .= " {$main_group} ";
        }
    }

    $where .= ')';

    return $where;
}

/**
 * Build conditions array for a workflow.
 *
 * @param  int $workflow_id
 *
 * @return array
 */
function erp_wf_build_condtion_raw( $workflow_id ) {
    global $wpdb;

    $workflow = erp_wf_get_workflow( $workflow_id );

    $main_group = $workflow->conditions_group;

    $php_conditions = [];

    $conditions = $wpdb->get_results( "select * from {$wpdb->prefix}erp_workflow_conditions where workflow_id = {$workflow_id}" );

    if ( ! empty( $conditions ) ) {
        $php_conditions['conditions_group'] = $workflow->conditions_group;

        if ( $workflow->type == 'auto' ) {
            $php_conditions['event'] = $workflow->event;
        }
    }

    foreach( $conditions as $condition ) {
        $php_conditions['conditions'][] = [
            'condition_name' => $condition->condition_name,
            'operator' => $condition->operator,
            'value' => $condition->value,
        ];
    }

    return $php_conditions;
}

/**
 * Get workflows by an event.
 *
 * @param  string $event
 *
 * @return array
 */
function erp_wf_get_workflows_by_event( $event ) {
    $workflows = \WeDevs\ERP\Workflow\Models\Workflow::where( ['type' => 'auto', 'status' => 'active', 'event' => $event] )->get();

    return erp_array_to_object( $workflows->toArray() );
}

/**
 * Check a workflow if it's meet with the current event.
 *
 * @param  int   $workflow_id
 * @param  array $event_data
 *
 * @return boolean
 */
function erp_wf_check_workflow( $workflow_id, $event_data ) {
    $condition_count = \WeDevs\ERP\Workflow\Models\Condition::where('workflow_id', $workflow_id)->count();
    if ( $condition_count === 0 ) {
        return true;
    }

    $conditions = erp_wf_build_condtion_raw( $workflow_id );

    $is_meet = erp_wf_meet_current_process( $conditions, $event_data );

    return $is_meet;
}

/**
 * Determine if the current event meet the crieteria.
 *
 * @param  array  $conditions
 * @param  array  $user_data
 *
 * @return boolean
 */
function erp_wf_meet_current_process( $conditions, $user_data = [] ) {
    $x = 0;
    foreach ( $conditions['conditions'] as $key => $condition ) {
        $matched[ $x ] = false;

        if ( isset( $user_data[ $condition['condition_name'] ] ) && erp_wf_parse_condition( $user_data[ $condition['condition_name'] ], $condition['operator'], $condition['value'] ) ) {
            $matched[ $x ] = true;
        }

        $x ++;
    }

    return erp_wf_logical_and_or( $matched, $conditions['conditions_group'] );
}

/**
 * Check the condition.
 *
 * @param  string $var1 (field)
 * @param  string $op
 * @param  string $var2 (value | optional)
 *
 * @return boolean
 */
function erp_wf_parse_condition( $var1, $op, $var2 = '' ) {
    if ( ! empty( $var1 ) && is_array( $var1 ) ) {
        return in_array( $var2, $var1 );
    } else {
        switch ( $op ) {
            case "=":   return $var1 == $var2;
            case "!=":  return $var1 != $var2;
            case ">=":  return $var1 >= $var2;
            case "<=":  return $var1 <= $var2;
            case ">":   return $var1 >  $var2;
            case "<":   return $var1 <  $var2;
            case "~":   return ( preg_match( "/$var2/i", $var1 ) ) ? true : false;
            case "!~":  return ! ( preg_match( "/$var2/i", $var1 ) ) ? true : false;
            case "ts":  return erp_wf_is_timestamp( $var1 );
            case "!ts": return ! erp_wf_is_timestamp( $var1 );

            default:    return false;
        }
    }
}

/**
 * Check logical and/or for a group of and/or.
 *
 * @param  array  $group
 * @param  string $type
 *
 * @return boolean
 */
function erp_wf_logical_and_or( $group = [], $type = 'and' ) {
    $first_value = current( $group );
    switch ( strtolower( $type ) ) {
        case 'and':
            foreach ( $group as $val ) {
                if ( ! ( $first_value && $val ) ) {
                    return false;
                }
            }

            return true;

            break;

        case 'or':
            foreach ( $group as $val ) {
                if ( true === $val ) {
                    return true;
                }
            }

            return false;

            break;
    }

    return false;
}

/**
 * Get ERP common fields.
 *
 * @return array
 */
function erp_wf_get_erp_fields( $type = null ) {
    $fields['contact'] = [
        'first_name'  => __( 'First Name', 'erp-pro' ),
        'last_name'   => __( 'Last Name', 'erp-pro' ),
        'email'       => __( 'Email', 'erp-pro' ),
        'phone'       => __( 'Phone', 'erp-pro' ),
        'mobile'      => __( 'Mobile', 'erp-pro' ),
        'website'     => __( 'Website', 'erp-pro' ),
        'fax'         => __( 'Fax', 'erp-pro' ),
        'notes'       => __( 'Notes', 'erp-pro' ),
        'street_1'    => __( 'Street 1', 'erp-pro' ),
        'street_2'    => __( 'Street 2', 'erp-pro' ),
        'city'        => __( 'City', 'erp-pro' ),
        'state'       => __( 'State', 'erp-pro' ),
        'postal_code' => __( 'Postal Code', 'erp-pro' ),
        'country'     => __( 'Country', 'erp-pro' ),
        'currency'    => __( 'Currency', 'erp-pro' ),
        'life_stage'  => __( 'Life Stage', 'erp-pro' ),
    ];

    $fields['company'] = [
        'company'     => __( 'Company', 'erp-pro' ),
        'email'       => __( 'Email', 'erp-pro' ),
        'phone'       => __( 'Phone', 'erp-pro' ),
        'mobile'      => __( 'Mobile', 'erp-pro' ),
        'website'     => __( 'Website', 'erp-pro' ),
        'fax'         => __( 'Fax', 'erp-pro' ),
        'notes'       => __( 'Notes', 'erp-pro' ),
        'street_1'    => __( 'Street 1', 'erp-pro' ),
        'street_2'    => __( 'Street 2', 'erp-pro' ),
        'city'        => __( 'City', 'erp-pro' ),
        'state'       => __( 'State', 'erp-pro' ),
        'postal_code' => __( 'Postal Code', 'erp-pro' ),
        'country'     => __( 'Country', 'erp-pro' ),
        'currency'    => __( 'Currency', 'erp-pro' ),
        'life_stage'  => __( 'Life Stage', 'erp-pro' ),
    ];

    $fields['employee'] = [
        'first_name'      => __( 'First Name', 'erp-pro' ),
        'middle_name'     => __( 'Middle Name', 'erp-pro' ),
        'last_name'       => __( 'Last Name', 'erp-pro' ),
        'user_email'      => __( 'User Email', 'erp-pro' ),
        'designation'     => __( 'Designation', 'erp-pro' ),
        'department'      => __( 'Department', 'erp-pro' ),
        'location'        => __( 'Location', 'erp-pro' ),
        'hiring_source'   => __( 'Hiring Source', 'erp-pro' ),
        'hiring_date'     => __( 'Hiring Date', 'erp-pro' ),
        'date_of_birth'   => __( 'Date of Birth', 'erp-pro' ),
        'pay_rate'        => __( 'Pay Rate', 'erp-pro' ),
        'pay_type'        => __( 'Pay Type', 'erp-pro' ),
        'type'            => __( 'Type', 'erp-pro' ),
        'status'          => __( 'Status', 'erp-pro' ),
        'other_email'     => __( 'Other Email', 'erp-pro' ),
        'phone'           => __( 'Phone', 'erp-pro' ),
        'work_phone'      => __( 'Work Phone', 'erp-pro' ),
        'mobile'          => __( 'Mobile', 'erp-pro' ),
        'address'         => __( 'Address', 'erp-pro' ),
        'gender'          => __( 'Gender', 'erp-pro' ),
        'marital_status'  => __( 'Marital Status', 'erp-pro' ),
        'nationality'     => __( 'Nationality', 'erp-pro' ),
        'driving_license' => __( 'Driving License', 'erp-pro' ),
        'hobbies'         => __( 'Hobbies', 'erp-pro' ),
        'user_url'        => __( 'User Url', 'erp-pro' ),
        'description'     => __( 'Description', 'erp-pro' ),
        'street_1'        => __( 'Street 1', 'erp-pro' ),
        'street_2'        => __( 'Street 2', 'erp-pro' ),
        'city'            => __( 'City', 'erp-pro' ),
        'country'         => __( 'Country', 'erp-pro' ),
        'state'           => __( 'State', 'erp-pro' ),
        'postal_code'     => __( 'Postal Code', 'erp-pro' ),
    ];

    return isset( $type ) ? $fields[ $type ] : $fields;
}

/**
 * Get all events list.
 *
 * @return array
 */
function erp_wf_get_events_list() {
    $events['general'] = [
        'created_user'                => __( 'Created User', 'erp-pro' ),
    ];

    if ( erp_is_module_active( 'crm' ) ) {
        $events['crm'] = [
            'created_contact'         => __( 'Created Contact', 'erp-pro' ),
            'deleted_contact'         => __( 'Deleted Contact', 'erp-pro' ),
            'subscribed_contact'      => __( 'Subscribed Contact', 'erp-pro' ),
            'unsubscribed_contact'    => __( 'Unsubscribed Contact', 'erp-pro' ),
            'created_note'            => __( 'Created Note', 'erp-pro' ),
            'created_task'            => __( 'Created Task', 'erp-pro' ),
            'scheduled_meeting'       => __( 'Scheduled Meeting', 'erp-pro' ),
        ];

        $events['imap'] = [
            'inbound_email'           => __( 'Inbound Email', 'erp-pro' ),
        ];
    }

    if ( erp_is_module_active( 'hrm' ) ) {
        $events['hrm'] = [
            'created_employee'        => __( 'Created Employee', 'erp-pro' ),
            'deleted_employee'        => __( 'Deleted Employee', 'erp-pro' ),
            'published_announcement'  => __( 'Published Announcement', 'erp-pro' ),
            'requested_leave'         => __( 'Requested Leave', 'erp-pro' ),
            'confirmed_leave_request' => __( 'Confirmed Leave Request', 'erp-pro' ),
            'happened_birthday_today' => __( 'Happened Birthday Today', 'erp-pro' ),
        ];
    }

    if ( erp_is_module_active( 'accounting' ) ) {
        $events['accounting'] = [
            'created_customer' => __( 'Created Customer', 'erp-pro' ),
            'deleted_customer' => __( 'Deleted Customer', 'erp-pro' ),
            'created_vendor'   => __( 'Created Vendor', 'erp-pro' ),
            'deleted_vendor'   => __( 'Deleted Vendor', 'erp-pro' ),
            'added_sale'       => __( 'Added Sale', 'erp-pro' ),
            'added_expense'    => __( 'Added Expense', 'erp-pro' )
        ];

        if ( version_compare( WPERP_VERSION , '1.4.0', '>' ) ) {
            $events['accounting']['added_check']          = __( 'Added Check', 'erp-pro' );
            $events['accounting']['added_bill']           = __( 'Added Bill', 'erp-pro' );
            $events['accounting']['added_purchase_order'] = __( 'Added Purchase Order', 'erp-pro' );
            $events['accounting']['added_purchase']       = __( 'Added purchase', 'erp-pro' );

        }
    }

    return apply_filters( 'erp_workflow_events_list', $events );
}

/**
 * Get all conditions list.
 *
 * @return array
 */
function erp_wf_get_conditions_list() {
    /** Manual Conditions Start Here **/
    $conditions['manual']['contact'] = erp_wf_get_erp_fields( 'contact' );

    $conditions['manual']['company'] = erp_wf_get_erp_fields( 'company' );

    $conditions['manual']['employee'] = erp_wf_get_erp_fields( 'employee' );
    /** Manual Conditions End Here **/

    /** Auto Conditions Start Here **/
    $conditions['auto']['created_user'] = [
        'email'        => __( 'Email', 'erp-pro' ),
        'roles'        => __( 'Roles', 'erp-pro' ),
        'first_name'   => __( 'First Name', 'erp-pro' ),
        'last_name'    => __( 'Last Name', 'erp-pro' ),
        'full_name'    => __( 'Full Name', 'erp-pro' ),
    ];

    $conditions['auto']['created_contact'] = erp_wf_get_erp_fields( 'contact' );

    $conditions['auto']['deleted_contact'] = erp_wf_get_erp_fields( 'contact' );

    $conditions['auto']['subscribed_contact'] = erp_wf_get_erp_fields( 'contact' );

    $conditions['auto']['unsubscribed_contact'] = erp_wf_get_erp_fields( 'contact' );

    $conditions['auto']['created_note'] = [
        'message' => __( 'Message', 'erp-pro' ),
    ];

    $conditions['auto']['created_task'] = [
        'task_title' => __( 'Task Title', 'erp-pro' ),
        'message'    => __( 'Message', 'erp-pro' ),
        'start_date' => __( 'Start Date', 'erp-pro' ),
    ];

    $conditions['auto']['scheduled_meeting'] = [
        'schedule_title' => __( 'Schedule Title', 'erp-pro' ),
        'schedule_type'  => __( 'Schedule Type', 'erp-pro' ),
        'message'        => __( 'Message', 'erp-pro' ),
        'start_date'     => __( 'Start Date', 'erp-pro' ),
    ];

    $conditions['auto']['created_employee'] = erp_wf_get_erp_fields( 'employee' );

    $conditions['auto']['deleted_employee'] = erp_wf_get_erp_fields( 'employee' );

    $conditions['auto']['published_announcement'] = [
        'post_title'   => __( 'Title', 'erp-pro' ),
        'post_content' => __( 'Content', 'erp-pro' ),
    ];

    $conditions['auto']['requested_leave'] = [
        'reason'     => __( 'Reason', 'erp-pro' ),
        'start_date' => __( 'Start Date', 'erp-pro' ),
        'end_date'   => __( 'End Date', 'erp-pro' ),
        'policy'     => __( 'Policy', 'erp-pro' ),
    ];

    $conditions['auto']['confirmed_leave_request'] = [
        'reason'     => __( 'Reason', 'erp-pro' ),
        'start_date' => __( 'Start Date', 'erp-pro' ),
        'end_date'   => __( 'End Date', 'erp-pro' ),
        'policy'     => __( 'Policy', 'erp-pro' ),
    ];

    $conditions['auto']['happened_birthday_today'] = erp_wf_get_erp_fields( 'employee' );

    $conditions['auto']['created_customer'] = [
        'first_name'  => __( 'First Name', 'erp-pro' ),
        'last_name'   => __( 'Last Name', 'erp-pro' ),
        'email'       => __( 'Email', 'erp-pro' ),
        'company'     => __( 'Company', 'erp-pro' ),
        'phone'       => __( 'Phone', 'erp-pro' ),
        'mobile'      => __( 'Mobile', 'erp-pro' ),
        'website'     => __( 'Website', 'erp-pro' ),
        'fax'         => __( 'Fax', 'erp-pro' ),
        'notes'       => __( 'Notes', 'erp-pro' ),
        'street_1'    => __( 'Street 1', 'erp-pro' ),
        'street_2'    => __( 'Street 2', 'erp-pro' ),
        'city'        => __( 'City', 'erp-pro' ),
        'state'       => __( 'State', 'erp-pro' ),
        'postal_code' => __( 'Postal Code', 'erp-pro' ),
        'country'     => __( 'Country', 'erp-pro' ),
        'currency'    => __( 'Currency', 'erp-pro' ),
    ];

    $conditions['auto']['deleted_customer'] = [
        'first_name'  => __( 'First Name', 'erp-pro' ),
        'last_name'   => __( 'Last Name', 'erp-pro' ),
        'email'       => __( 'Email', 'erp-pro' ),
        'company'     => __( 'Company', 'erp-pro' ),
        'phone'       => __( 'Phone', 'erp-pro' ),
        'mobile'      => __( 'Mobile', 'erp-pro' ),
        'website'     => __( 'Website', 'erp-pro' ),
        'fax'         => __( 'Fax', 'erp-pro' ),
        'notes'       => __( 'Notes', 'erp-pro' ),
        'street_1'    => __( 'Street 1', 'erp-pro' ),
        'street_2'    => __( 'Street 2', 'erp-pro' ),
        'city'        => __( 'City', 'erp-pro' ),
        'state'       => __( 'State', 'erp-pro' ),
        'postal_code' => __( 'Postal Code', 'erp-pro' ),
        'country'     => __( 'Country', 'erp-pro' ),
        'currency'    => __( 'Currency', 'erp-pro' ),
    ];

    $conditions['auto']['created_vendor'] = [
        'first_name'  => __( 'First Name', 'erp-pro' ),
        'last_name'   => __( 'Last Name', 'erp-pro' ),
        'email'       => __( 'Email', 'erp-pro' ),
        'company'     => __( 'Company', 'erp-pro' ),
        'phone'       => __( 'Phone', 'erp-pro' ),
        'mobile'      => __( 'Mobile', 'erp-pro' ),
        'website'     => __( 'Website', 'erp-pro' ),
        'fax'         => __( 'Fax', 'erp-pro' ),
        'notes'       => __( 'Notes', 'erp-pro' ),
        'street_1'    => __( 'Street 1', 'erp-pro' ),
        'street_2'    => __( 'Street 2', 'erp-pro' ),
        'city'        => __( 'City', 'erp-pro' ),
        'state'       => __( 'State', 'erp-pro' ),
        'postal_code' => __( 'Postal Code', 'erp-pro' ),
        'country'     => __( 'Country', 'erp-pro' ),
        'currency'    => __( 'Currency', 'erp-pro' ),
    ];

    $conditions['auto']['deleted_vendor'] = [
        'first_name'  => __( 'First Name', 'erp-pro' ),
        'last_name'   => __( 'Last Name', 'erp-pro' ),
        'email'       => __( 'Email', 'erp-pro' ),
        'company'     => __( 'Company', 'erp-pro' ),
        'phone'       => __( 'Phone', 'erp-pro' ),
        'mobile'      => __( 'Mobile', 'erp-pro' ),
        'website'     => __( 'Website', 'erp-pro' ),
        'fax'         => __( 'Fax', 'erp-pro' ),
        'notes'       => __( 'Notes', 'erp-pro' ),
        'street_1'    => __( 'Street 1', 'erp-pro' ),
        'street_2'    => __( 'Street 2', 'erp-pro' ),
        'city'        => __( 'City', 'erp-pro' ),
        'state'       => __( 'State', 'erp-pro' ),
        'postal_code' => __( 'Postal Code', 'erp-pro' ),
        'country'     => __( 'Country', 'erp-pro' ),
        'currency'    => __( 'Currency', 'erp-pro' ),
    ];

    $conditions['auto']['added_expense'] = [
        'issue_date' => __( 'Issue Date', 'erp-pro' ),
        'due_date'   => __( 'Due Date', 'erp-pro' ),
        'total'      => __( 'Total Amount', 'erp-pro' ),
        'due'        => __( 'Due Amount', 'erp-pro' ),
    ];

    if ( version_compare( WPERP_VERSION , '1.4.0', '>' ) ) {
        // invoice
        $conditions['auto']['added_sale'] = [
            'trn_date'   => __( 'Transaction Date', 'erp-pro' ),
            'due_date'   => __( 'Due Date', 'erp-pro' ),
            'voucher_no' => __( 'Voucher Number', 'erp-pro' ),
            'amount'     => __( 'Total Amount', 'erp-pro' ),
            'total_due'  => __( 'Due Amount', 'erp-pro' )
        ];

        // expense
        $conditions['auto']['added_expense'] = [
            'trn_date' => __( 'Transaction Date', 'erp-pro' ),
            'amount'   => __( 'Total Amount', 'erp-pro' )
        ];

        // check
        $conditions['auto']['added_check'] = [
            'trn_date' => __( 'Transaction Date', 'erp-pro' ),
            'amount'   => __( 'Total Amount', 'erp-pro' ),
        ];

        // bill
        $conditions['auto']['added_bill'] = [
            'trn_date'  => __( 'Transaction Date', 'erp-pro' ),
            'due_date'  => __( 'Due Date', 'erp-pro' ),
            'amount'    => __( 'Total Amount', 'erp-pro' ),
            'total_due' => __( 'Due Amount', 'erp-pro' ),
        ];

        // purchase_order
        $conditions['auto']['added_purchase_order'] = [
            'trn_date' => __( 'Transaction Date', 'erp-pro' ),
            'due_date' => __( 'Due Date', 'erp-pro' ),
            'amount'   => __( 'Total Amount', 'erp-pro' ),
        ];

        // purchase
        $conditions['auto']['added_purchase'] = [
            'trn_date' => __( 'Transaction Date', 'erp-pro' ),
            'due_date' => __( 'Due Date', 'erp-pro' ),
            'amount'   => __( 'Total Amount', 'erp-pro' ),
        ];

    } else {
        // old accounting
        $conditions['auto']['added_sale'] = [
            'issue_date'     => __( 'Issue Date', 'erp-pro' ),
            'due_date'       => __( 'Due Date', 'erp-pro' ),
            'invoice_number' => __( 'Invoice Number', 'erp-pro' ),
            'total'          => __( 'Total Amount', 'erp-pro' ),
            'due'            => __( 'Due Amount', 'erp-pro' ),
        ];

        $conditions['auto']['added_expense'] = [
            'issue_date' => __( 'Issue Date', 'erp-pro' ),
            'due_date'   => __( 'Due Date', 'erp-pro' ),
            'total'      => __( 'Total Amount', 'erp-pro' ),
            'due'        => __( 'Due Amount', 'erp-pro' ),
        ];
    }

    $conditions['auto']['inbound_email'] = [
        'subject' => __( 'Subject', 'erp-pro' ),
        'from'    => __( 'From', 'erp-pro' ),
        'body'    => __( 'Body', 'erp-pro' ),
    ];
    /** Auto Conditions Start Here **/

    return apply_filters( 'erp_workflow_conditions_list', $conditions );
}

/**
 * Get all actions list.
 *
 * @return array
 */
function erp_wf_get_actions_list() {
    /** Manual Actions Start Here **/
    $keys = [
        'send_email',
        'trigger_action_hook',
        'update_field',
        'add_activity',
        'schedule_meeting',
    ];
    $actions['manual']['contact'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
        'update_field',
        'add_activity',
        'schedule_meeting',
    ];
    $actions['manual']['company'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
        'update_field',
    ];
    $actions['manual']['employee'] = erp_wf_get_actions_by_keys( $keys );
    /** Manual Actions End Here **/

    /** Auto Actions Start Here **/
    $keys = [
        'send_email',
        'trigger_action_hook',
        'update_field',
        'add_user_role',
    ];
    $actions['auto']['created_user'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
        'update_field',
        'add_activity',
    ];
    $actions['auto']['created_contact'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
        'update_field',
        'add_activity',
        'schedule_meeting',
    ];
    $actions['auto']['deleted_contact'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
        'update_field',
        'add_activity',
        'schedule_meeting',
    ];
    $actions['auto']['subscribed_contact'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
        'update_field',
        'add_activity',
        'schedule_meeting',
    ];
    $actions['auto']['unsubscribed_contact'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['created_note'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['created_task'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['scheduled_meeting'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
        'update_field',
    ];
    $actions['auto']['created_employee'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['deleted_employee'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['published_announcement'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['requested_leave'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['confirmed_leave_request'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['happened_birthday_today'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
    ];
    $actions['auto']['created_customer'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['deleted_customer'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
    ];
    $actions['auto']['created_vendor'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['deleted_vendor'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
        'send_invoice',
    ];
    $actions['auto']['added_sale'] = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'trigger_action_hook',
    ];
    $actions['auto']['added_expense']        = erp_wf_get_actions_by_keys( $keys );
    $actions['auto']['added_check']          = erp_wf_get_actions_by_keys( $keys );
    $actions['auto']['added_bill']           = erp_wf_get_actions_by_keys( $keys );
    $actions['auto']['added_purchase_order'] = erp_wf_get_actions_by_keys( $keys );
    $actions['auto']['added_purchase']       = erp_wf_get_actions_by_keys( $keys );

    $keys = [
        'send_email',
        'assign_task',
        'trigger_action_hook',
        'add_activity',
        'schedule_meeting',
    ];
    $actions['auto']['inbound_email'] = erp_wf_get_actions_by_keys( $keys );
    /** Auto Actions Start Here **/

    return apply_filters( 'erp_workflow_actions_list', $actions );
}

/**
 * Get actions by given keys.
 *
 * @param  array $keys (optional)
 *
 * @return array
 */
function erp_wf_get_actions_by_keys( $keys = [] ) {
    $actions = [];

    $actions['add_user_role']       = __( 'Add User Role', 'erp-pro' );
    $actions['send_email']          = __( 'Send Email', 'erp-pro' );
    $actions['assign_task']         = __( 'Assign Task', 'erp-pro' );
    $actions['trigger_action_hook'] = __( 'Trigger Action Hook', 'erp-pro' );
    $actions['update_field']        = __( 'Update Field', 'erp-pro' );
    $actions['add_activity']        = __( 'Add Activity', 'erp-pro' );
    $actions['schedule_meeting']    = __( 'Schedule Meeting', 'erp-pro' );

    if ( erp_is_module_active( 'accounting' ) ) {
        $actions['send_invoice'] = __( 'Send Invoice', 'erp-pro' );
    }

    if ( ! empty( $keys ) ) {
        $filtered_actions = [];

        foreach ( $actions as $key => $value ) {
            if ( in_array( $key, $keys ) ) {
                $filtered_actions[ $key ] = $value;
            }
        }

        return $filtered_actions;
    }

    return $actions;
}

/**
 * Get hooks by given event name.
 *
 * @param  string $event (optional)
 *
 * @return array
 */
function erp_wf_get_hooks( $event = null ) {
    $hooks = [
        'created_user'            => 'user_register',
        // crm
        'created_contact'         => 'erp_create_new_people',
        'deleted_contact'         => 'erp_after_delete_people',
        'subscribed_contact'      => 'erp_crm_create_contact_subscriber',
        'unsubscribed_contact'    => 'erp_crm_pre_unsubscribed_contact',
        'created_note'            => 'erp_crm_save_customer_new_note_feed',
        'created_task'            => 'erp_crm_save_customer_tasks_activity_feed',
        'scheduled_meeting'       => 'erp_crm_save_customer_schedule_feed',
        // hrm
        'created_employee'        => 'erp_hr_employee_new',
        'deleted_employee'        => 'erp_hr_after_delete_employee',
        'published_announcement'  => 'hr_annoucement_save',
        'requested_leave'         => 'erp_hr_leave_new',
        'confirmed_leave_request' => 'erp_hr_leave_request_approved',
        'happened_birthday_today' => 'erp_hr_happened_birthday_today',
        // accounting
        'created_customer'        => 'erp_ac_after_new_customer',
        'deleted_customer'        => 'erp_ac_delete_customer',
        'created_vendor'          => 'erp_ac_after_new_vendor',
        'deleted_vendor'          => 'erp_ac_delete_vendor',
        'added_sale'              => 'erp_ac_new_transaction_sales',
        'added_expense'           => 'erp_ac_new_transaction_expense',
        // imap
        'inbound_email'           => 'erp_crm_contact_inbound_email',
    ];

    if ( version_compare( WPERP_VERSION , '1.4.0', '>' ) ) {
        // new accountign
        $args = [
            'created_customer'     => 'erp_acct_after_new_customer',
            'deleted_customer'     => 'erp_acct_delete_customer',
            'created_vendor'       => 'erp_acct_after_new_vendor',
            'deleted_vendor'       => 'erp_acct_delete_vendor',
            'added_sale'           => 'erp_acct_new_transaction_sales',
            'added_expense'        => 'erp_acct_new_transaction_expense',
            'added_check'          => 'erp_acct_new_transaction_check',
            'added_bill'           => 'erp_acct_new_transaction_bill',
            'added_purchase_order' => 'erp_acct_new_transaction_purchase_order',
            'added_purchase'       => 'erp_acct_new_transaction_purchase',
        ];

        $hooks = wp_parse_args( $args, $hooks );
    }

    if ( $event ) {
        return isset( $hooks[ $event ] ) ? $hooks[ $event ] : null;
    }

    return $hooks;
}

/**
 * Determine if the given string is timestamp.
 *
 * @param  string $timestamp
 *
 * @return boolean
 */
function erp_wf_is_timestamp( $timestamp ) {
    if ( date( 'Y', strtotime( $timestamp ) ) === (int) $timestamp ) {
        return true;
    }

    return false;
}

/**
 * Get all localize strings.
 *
 * @return array
 */
function erp_wf_get_localize_strings() {
    return include_once ERP_WORKFLOW_PATH . '/includes/localize-strings.php';
}

/**
 * Dropdown list formatter
 *
 * @param  array   $items
 * @param  boolean $group
 *
 * @return array
 */
function erp_wf_dropdown_list_formatter( $items, $group = false ) {
    $formatted_items = [];

    if ( $group ) {
        $groups = array_keys( $items );

        foreach ( $groups as $group ) {
            foreach ( $items[ $group ] as $key => $value ) {
                $formatted_items[ $group ][] = [
                    'key'   => $key,
                    'label' => $value
                ];
            }
        }
    } else {
        foreach ( $items as $key => $value ) {
            $formatted_items[] = [
                'key'   => $key,
                'label' => $value
            ];
        }
    }

    return $formatted_items;
}

/**
 * Normalize and get employee information.
 *
 * @param  integer $id
 *
 * @return array
 */
function erp_wf_get_hr_employee( $id ) {
    $emp_obj = new \WeDevs\ERP\HRM\Employee( intval( $id ) );

    $employee['first_name']      = $emp_obj->first_name;
    $employee['middle_name']     = $emp_obj->middle_name;
    $employee['user_email']      = $emp_obj->user_email;
    $employee['designation']     = $emp_obj->get_job_title();
    $employee['department']      = $emp_obj->get_department_title();
    $employee['location']        = $emp_obj->get_work_location();
    $employee['hiring_source']   = $emp_obj->get_hiring_source();
    $employee['hiring_date']     = $emp_obj->get_joined_date();
    $employee['date_of_birth']   = $emp_obj->get_birthday();
    $employee['pay_rate']        = $emp_obj->pay_rate;
    $employee['pay_type']        = $emp_obj->pay_type;
    $employee['type']            = $emp_obj->get_type();
    $employee['status']          = $emp_obj->get_status();
    $employee['other_email']     = $emp_obj->other_email;
    $employee['phone']           = $emp_obj->get_phone( 'phone' );
    $employee['work_phone']      = $emp_obj->get_phone( 'work' );
    $employee['mobile']          = $emp_obj->get_phone( 'mobile' );
    $employee['address']         = $emp_obj->address;
    $employee['gender']          = $emp_obj->get_gender();
    $employee['marital_status']  = $emp_obj->get_marital_status();
    $employee['nationality']     = $emp_obj->get_nationality();
    $employee['driving_license'] = $emp_obj->driving_license;
    $employee['hobbies']         = $emp_obj->hobbies;
    $employee['description']     = $emp_obj->description;
    $employee['street_1']        = $emp_obj->street_1;
    $employee['street_2']        = $emp_obj->street_2;
    $employee['city']            = $emp_obj->city;
    $employee['country']         = $emp_obj->get_country();
    $employee['state']           = $emp_obj->get_state();
    $employee['postal_code']     = $emp_obj->get_postal_code();

    return $employee;
}

/**
 * Update employee
 *
 * @param  array $args
 *
 * @return int
 */
function erp_wf_hr_update_employee( $args ) {
    $employee_obj        = new \WeDevs\ERP\HRM\Employee( $args['user_id'] );
    $employee            = $employee_obj->to_array();
    $employee['user_id'] = $args['user_id'];

    $employee['personal']['first_name']  = $employee['name']['first_name'];
    $employee['personal']['middle_name'] = $employee['name']['middle_name'];
    $employee['personal']['last_name']   = $employee['name']['last_name'];

    $emp_work = array(
        'designation',
        'department',
        'location',
        'hiring_source',
        'hiring_date',
        'date_of_birth',
        'reporting_to',
        'pay_rate',
        'pay_type',
        'type',
        'status'
    );

    if ( ! empty( $args['field_name'] ) ) {
        if ( $args['field_name'] == 'user_email' ) {
            $employee[ $args['field_name'] ] = $args['field_value'];
        } elseif ( in_array( $args['field_name'], $emp_work ) ) {
            $employee['work'][ $args['field_name'] ] = $args['field_value'];
        } else {
            $employee['personal'][ $args['field_name'] ] = $args['field_value'];
        }
    }

    return erp_hr_employee_create( $employee );
}

/**
 * Replace text variables for some keys.
 *
 * @param  array $vars
 *
 * @return string
 */
function erp_wf_replace_text_vars( $vars, $content ) {
    foreach ( $vars as $key => $value ) {
        if ( ! is_array( $value ) ) {
            $content = str_replace( "[$key]", $value, $content );
        }
    }

    return $content;
}
