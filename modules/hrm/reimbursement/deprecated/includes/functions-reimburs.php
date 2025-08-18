<?php
/**
 * get employee
 *
 * @since  1.0.0
 *
 * @return array
 */
function erp_reimburs_get_employees() {
	global $current_user;

    if ( function_exists( 'erp_hr_get_employees' ) ) {
        $hr_employees = erp_hr_get_employees( array( 'number' => '-1' ) );
        $employees    = [];

        foreach ( $hr_employees as $key => $employee ) {
            $employees[$employee->ID] = $employee->display_name;
        }

        return $employees;
    }

	$employees[$current_user->ID] = $current_user->display_name;

	return $employees;
}

/**
 * Register reimbursement form type
 *
 * @since 1.0.0
 *
 * @return  array
 */
function erp_ac_reimbursement_register_form_types() {
    $form_types = [
        'reimbur_payment' => [
        	'name'        => 'reimbur_payment',
            'label'       => __( 'Payment', 'erp' ),
            'description' => __( '', 'erp' ),
            'type'        => 'credit'
        ],

        'reimbur_invoice' => [
            'name'        => 'reimbur_invoice',
            'label'       => __( 'New Receipt', 'erp' ),
            'description' => __( '', 'erp' ),
            'type'        => 'credit'
        ],
    ];

    if ( erp_ac_reimbur_is_employee() ) {
    	unset( $form_types['reimbur_payment'] );
    }

    return apply_filters( 'erp_ac_get_reimbursement_form_types', $form_types );
}

/**
 * New imbursement time check the form type
 *
 * @since  1.0.0
 *
 * @param  array $form_types
 * @param  array $args
 *
 * @return array
 */
function erp_ac_reimbursement_form_types( $form_types, $args ) {
	if( $args['type'] == 'reimbur' ) {
        $form_types = erp_ac_reimbursement_register_form_types();
    }

    return $form_types;
}

/**
 * Check is HRM module exist or not
 *
 * @since  1.0.0
 *
 * @return  boolen
 */
function erp_ac_reimbursement_is_hrm_active() {
	$all_active_modules = wperp()->modules->get_active_modules();

	if ( array_key_exists( 'hrm', $all_active_modules ) ) {
		return true;
	}

	return false;
}

/**
 * Redirect after new reimbursement
 *
 * @since  1.0.0
 *
 * @param  str $redirect_to
 * @param  array $postdata
 *
 * @return str url
 */
function erp_reimburs_redirect( $redirect_to, $insert_id, $postdata ) {
    $slug = 'erp-accounting&section=reimbursement';
    if ( current_user_can( 'employee' ) ) {
        $slug = 'erp-hr&section=reimbursement';
    }

    if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
        $slug = 'erp-accounting-reimbursement';
    }

	if ( $postdata['type'] == 'reimbur' && $postdata['redirect'] == 'single_page' ) {
		$redirect_to = admin_url( 'admin.php?page=' . $slug . '&action=view&id=' . $insert_id );
	}

	return $redirect_to;
}

/**
 * Get transaction status with the help of submit button status
 *
 * @since  1.0.0
 *
 * @param  str $status
 * @param  array $postdata
 *
 * @return str
 */
function erp_ac_reimbur_trans_status( $status, $postdata ) {
	if ( $postdata['form_type'] == 'reimbur_payment' ) {
		return erp_ac_get_voucher_status_according_with_btn( $postdata['status'] );
	} else if ( $postdata['form_type'] == 'reimbur_invoice' ) {
		return erp_ac_reimbur_get_voucher_status_according_with_btn( $postdata['status'] );
	}

	return $status;
}

/**
 * Get transaction submit data status for payment voucher
 *
 * @since 1.0.0
 *
 * @param  string $btn
 *
 * @return string
 */
function erp_ac_reimbur_get_voucher_status_according_with_btn( $btn ) {
    $button = [
        'awaiting_approval'                 => 'awaiting_approval',
        'save_and_add_another'             => 'draft',
        'save_and_draft'                   => 'draft',
        'void'                             => 'void',
        'awaiting_payment'                 => 'awaiting_payment',
        'awaiting_approve_and_add_another' => 'awaiting_approval'
    ];

    return $button[$btn];
}

/**
 * Register reimbur transaction type
 *
 * @since  1.0.0
 *
 * @param  array $type
 *
 * @return array
 */
function erp_ac_reimbur_register_type( $type ) {
	array_push( $type, 'reimbur' );
	return $type;
}

/**
 * Include new partial type reimur
 *
 * @since  1.0.0
 *
 * @param  array $type
 * @param  array $trans
 *
 * @return array
 */
function erp_ac_reimbur_partial_types( $type, $trans ) {
	if ( $trans['form_type'] == 'reimbur_payment' ) {
		array_push( $type, 'reimbur_payment' );
	}

	return $type;
}

/**
 * Include new form type reimbur_invoice
 *
 * @since  1.0.0
 *
 * @param  array $type
 * @param  array $postdata
 *
 * @return array
 */
function erp_ac_reimbur_is_due_trans( $type, $postdata ) {
	if ( $postdata['form_type'] == 'reimbur_invoice' ) {
		array_push( $type, 'reimbur_invoice' );
	}
	return $type;
}

/**
 * Check is the current user HR employee
 *
 * @since  1.0.0
 *
 * @param  array $type
 * @param  array $postdata
 *
 * @return array
 */
function erp_ac_reimbur_is_employee() {
	if ( ! current_user_can( 'erp_ac_manager' ) ) {
		return true;
	}
	return false;
}

/**
 * Section url
 *
 * @since  1.0.0
 *
 * @param  string
 *
 * @return string
 */
function erp_ac_reimbur_section_url( $section ) {

    $slug = 'erp-accounting&section=reimbursement';

    if ( current_user_can( 'employee' ) ) {
        $slug = 'erp-hr&section=reimbursement';
    }
    if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
        $slug = 'erp-accounting-reimbursement';
    }

	switch ( $section ) {
		case 'draft':
			return admin_url( 'admin.php?page=' . $slug . '&filter=draft' );
			break;

		case 'awaiting_approval':
			return admin_url( 'admin.php?page=' . $slug . '&filter=awaiting-approval' );
			break;

		case 'awaiting_payment':
			return admin_url( 'admin.php?page=' . $slug . '&filter=awaiting-payment' );
			break;

		case 'paid':
			return admin_url( 'admin.php?page=' . $slug . '&filter=paid' );
			break;

        case 'closed':
            return admin_url( 'admin.php?page=' . $slug . '&filter=closed' );
            break;

		case 'void':
			return admin_url( 'admin.php?page=' . $slug . '&filter=void' );
			break;

		case 'partial':
			return admin_url( 'admin.php?page=' . $slug . '&filter=partial' );
			break;

		default:
			return admin_url( 'admin.php?page=' . $slug );
			break;
	}
}

/**
 * Get status from url section
 *
 * @since  1.0.0
 *
 * @param  string $section
 *
 * @return string
 */
function erp_ac_reimbur_get_status_from_url( $section ) {
	switch ( $section ) {
		case 'draft':
			return 'draft';
			break;

		case 'awaiting-approval':
			return 'awaiting_approval';
			break;

		case 'awaiting-payment':
			return 'awaiting_payment';
			break;

		case 'paid':
			return 'paid';
			break;

		case 'void':
			return 'void';
			break;

		case 'partial':
			return 'partial';
			break;

        case 'closed':
            return 'closed';
            break;

		default:
			return 'all';
			break;
	}
}

/**
 * Get the registered reimburse section
 *
 * @return array
 */
function erp_ac_reimbur_get_section() {
    $statuses = array(
		'all'               => __( 'All', 'erp-pro' ),
		'draft'             => __( 'Draft', 'erp-pro' ),
		'awaiting_approval' => __( 'Awaiting Approval', 'erp-pro' ),
		'awaiting_payment'  => __( 'Awaiting Payment', 'erp-pro' ),
        'partial'           => __( 'Partially Paid', 'erp-pro' ),
        'closed'            => __( 'Closed Installments' ),
        'paid'              => __( 'Completed Payment', 'erp-pro' ),
		'void'              => __( 'void', 'erp-pro' ),
    );

    return apply_filters( 'erp_ac_reimbur_section', $statuses );
}

/**
 * Get employee profile link
 *
 * @since  1.0.0
 *
 * @param int $employee_id
 *
 * @return  string
 */
function erp_ac_reimbur_user_url( $employee_id ) {
	if ( erp_ac_reimbursement_is_hrm_active() ) {
        $employee          = new \WeDevs\ERP\HRM\Employee( intval($employee_id) );
        $user_display_name = $employee->get_full_name();
        $profile           = $employee->get_details_url();

    } else {
        $employee          = get_user_by( 'id', intval( $employee_id ) );
        $user_display_name = $employee->display_name;
        $profile           = admin_url( 'user-edit.php?user_id=' . $employee_id );
    }

    return sprintf( '<a href="%1$s">%2$s</a>', $profile, $user_display_name );
}

/**
 * Get reimbur status count
 *
 * @since  1.0.0
 *
 * @return array
 */
function erp_ac_reimbur_transaction_count() {
	$cache_key = 'erp-ac-reimbur-trnasction-counts-' . get_current_user_id();
    $results = wp_cache_get( $cache_key, 'erp-pro' );

    if ( false === $results ) {
        $trans = new \WeDevs\ERP\Accounting\Model\Transaction();
        $db = new \WeDevs\ORM\Eloquent\Database();

        if ( erp_ac_reimbur_is_employee() ) {
            $results = $trans->select( array( 'status', $db->raw('COUNT(id) as num') ) )
                            ->where( 'type', '=', 'reimbur' )
                            ->where( 'user_id', '=', get_current_user_id() )
                            ->groupBy('status')
                            ->get()->toArray();
        } else {
        	$results = $trans->select( array( 'status', $db->raw('COUNT(id) as num') ) )
                            ->where( 'type', '=', 'reimbur' )
                            ->groupBy('status')
                            ->get()->toArray();
        }

        wp_cache_set( $cache_key, $results, 'erp-pro' );
    }

    $statuses = erp_ac_reimbur_get_section();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    foreach ( $results as $row ) {
        if ( array_key_exists( $row['status'], $counts ) ) {
            $counts[ $row['status'] ]['count'] = (int) $row['num'];
        }

        $counts['all']['count'] += (int) $row['num'];
    }

    return $counts;
}

/**
 * Check is current page actions
 *
 * @since 1.0.0
 *
 * @param  integer $page_id
 * @param  integer $bulk_action
 *
 * @return boolean
 */
function erp_ac_reimbur_bulk_action() {
    if( ! erp_ac_reimbur_verify_current_page_screen( 'erp-accounting', 'reimbursement', 'bulk-reimbursements' ) ) {
        return;
    }

    $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
    foreach ( $_REQUEST['transaction_id'] as $key => $trans_id ) {
        switch ( $action ) {
            case 'delete':
                erp_ac_remove_transaction( $trans_id );
                break;

            case 'void':
                erp_ac_update_transaction_to_void( $trans_id );
                break;

            default:
                erp_ac_update_transaction( $trans_id, ['status' => $action] );
                break;
        }
    }

	wp_safe_redirect( $_REQUEST['_wp_http_referer'] );
	exit();
}

/**
 * Update reimbursement transacton
 *
 * @since 1.0.0
 *
 * @param  int $transaction_id
 *
 * @return boolean
 */
function erp_ac_reimbur_update_transaction( $transaction_id, $update = array() ) {
	if ( is_array( $transaction_id ) ) {
		foreach (  $transaction_id as $key => $trans_id ) {
			erp_ac_update_transaction( $trans_id, $update );
		}

		return true;
	}

	return erp_ac_update_transaction( intval( $transaction_id ), $update );
}

/**
 * Remove reimbursement transacton
 *
 * @since 1.0.0
 *
 * @param  int $transaction_id
 *
 * @return boolean
 */
function erp_ac_reimbur_delete( $transaction_id ) {
	if ( is_array( $transaction_id ) ) {
		foreach (  $transaction_id as $key => $trans_id ) {
			erp_ac_remove_transaction( $trans_id );
		}

		return true;
	}

	return erp_ac_remove_transaction( intval( $transaction_id ) );
}

/**
 * Handle transaction update using ajax
 *
 * @since  1.0.0
 *
 * @return void
 */
function erp_ac_reimbur_ajax_handel_trn_update() {
	check_ajax_referer('erp-ac-nonce');
    $status         = isset( $_POST['status'] ) ? $_POST['status'] : false;
    $trns_id        = isset( $_POST['id'] ) ? $_POST['id'] : false;
    $status         = isset( $_POST['status'] ) ? $_POST['status'] : false;
    $transaction_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : array();

    switch ( $status ) {
        case 'delete':
            $update = erp_ac_remove_transaction( $trns_id );
            break;

        case 'void':
            erp_ac_update_transaction_to_void( $trns_id, ['status' => $status] );
            break;

        default:
            erp_ac_reimbur_update_transaction( $transaction_id, ['status' => $status] );
            break;
    }

	wp_send_json_success(['success' => __( 'Done', 'erp-pro' ) ]);
}

/**
 * Check is current page actions
 *
 * @since 1.0.0
 *
 * @param  integer $page_id
 * @param  integer $bulk_action
 *
 * @return boolean
 */
function erp_ac_reimbur_verify_current_page_screen( $page_id, $section_id, $bulk_action){
    if (!isset($_REQUEST['_wpnonce']) || !isset($_GET['page'])) {
        return false;
    }

    if (isset($_GET['page']) && isset($_GET['section']) && $_GET['page'] != $page_id && $_GET['section'] != $section_id) {
        return false;
    }

    if (!wp_verify_nonce($_REQUEST['_wpnonce'], $bulk_action)) {
        return false;
    }

    return true;
}

/**
 * action after new reimbursement
 *
 * @since  1.0.0
 *
 * @param  int $trans_id
 * @param  array $args
 * @param  array $items
 *
 * @return void
 */
function erp_ac_reibur_after_new_trans( $trans_id, $args, $items ) {
	if ( $args['form_type'] != 'reimbur_payment' ) {
        return;
    }
    WeDevs\ERP\Accounting\Model\Journal::where( 'transaction_id', '=', $trans_id )->where( 'ledger_id', '=', 8 )->delete();

    $parent      = WeDevs\ERP\Accounting\Model\Payment::select('child')->where( 'transaction_id', '=', $trans_id )->pluck('child');
    $prev_amount = WeDevs\ERP\Accounting\Model\Journal::select('credit')->where( 'transaction_id', '=', $parent )->where( 'ledger_id', '=', 10 )->pluck('credit');
    $new_credit  = $prev_amount - $args['trans_total'];

    WeDevs\ERP\Accounting\Model\Journal::where( 'transaction_id', '=', $parent )->where( 'ledger_id', '=', 10 )->update(['credit' => $new_credit]);
}

/**
 * Partial payment singel page url
 *
 * @since  1.0.0
 *
 * @param  string $url
 * @param  array $partial
 *
 * @return string
 */
function erp_ac_reimbur_single_partial_payment_url( $url, $partial ) {
    $slug = 'erp-accounting&section=reimbursement';

    if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
        $slug = 'erp-accounting-reimbursement';
    }

	if ( $partial['type'] == 'reimbur' ) {
		return admin_url( 'admin.php?page=' . $slug . '&action=view&id=' . $partial['id'] );
	}

	return $url;
}

/**
 * Filter trial balance
 *
 * @since  1.0.0
 *
 * @param  string $where
 *
 * @return string
 */
function erp_ac_reimbur_trial_balance_where( $where ) {
	$where .= " AND ( reimbur.status not in ('awaiting_approval', 'draft') )";
	return $where;
}

/**
 * Modify traila balance join query
 *
 * @since  1.0.0
 *
 * @param  string $join
 *
 * @return string
 */
function erp_ac_reimbure_trial_balance_join( $join ) {
	global $wpdb;
	$tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';
	$join = " LEFT JOIN {$tbl_transaction} as reimbur ON reimbur.id = jour.transaction_id";
	return $join;
}


/**
 * Expense Pie chart query
 *
 * @since  1.0.0
 *
 * @param  array $args
 *
 * @return array
 */
function erp_ac_expense_pie_chart( $args ) {
	array_push( $args['type'], 'reimbur' );
	return $args;
}

/**
 * Net expense with reimbursement
 *
 * @since  1.0.0
 *
 * @param  float $exp_amount
 *
 * @return float
 */
function erp_ac_net_expense( $exp_amount, $transections ) {
	$reimbursements = isset( $transections['reimbur'] ) ? $transections['reimbur'] : [];
    $reimbure = 0;

    foreach ( $reimbursements as $key => $details ) {

        if ( $details->status == 'partial' ) {
            $reimbure = $reimbure + $details->due;
        } else {
            $reimbure = $reimbure + $details->trans_total;
        }
    }

    return ( $reimbure + $exp_amount );
}

/**
 * Net income args
 *
 * @since  1.0.0
 *
 * @param  array $args
 *
 * @return array
 */
function erp_ac_net_income_args( $args ) {
	array_push( $args['type'], 'reimbur' );
	return $args;
}

/**
 * Reimbursement bill payable
 *
 * @since  1.0.0
 *
 * @param  array $args
 *
 * @return array
 */
function erp_ac_bill_payable_arags( $args ) {
	array_push( $args['form_type'], 'reimbur_invoice' );
	array_push( $args['type'], 'reimbur' );
	return $args;
}

/**
 * Include reimbur transaction type for income and expense bar chart
 *
 * @since  1.0.0
 *
 * @param  array $exp_args
 *
 * @return array
 */
function erp_ac_dashboard_expense_args( $exp_args ) {
    array_push( $exp_args['type'], 'reimbur' );
    return $exp_args;
}





