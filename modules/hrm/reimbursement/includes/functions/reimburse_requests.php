<?php

/**
 * Get all people transactions
 *
 * @return mixed
 */
function erp_acct_get_all_reimburse_reqs( $args = [] ) {
	global $wpdb;

	$defaults = [
		'number'  => 20,
		'offset'  => 0,
		'orderby' => 'id',
		'order'   => 'DESC',
		'count'   => false,
		's'       => '',
	];

	$args = wp_parse_args( $args, $defaults );

	$limit = '';

	if ( $args['number'] != '-1' ) {
		$limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
	}

	$sql = "SELECT";
	$sql .= $args['count'] ? " COUNT( id ) as total_number " : " *, (debit - credit) as balance ";

	$sql .= "FROM {$wpdb->prefix}erp_acct_people_account_details GROUP BY trn_no";

	if ( $args['count'] ) {
		return $wpdb->get_var( $sql );
	}

	return $wpdb->get_results( $sql, ARRAY_A );
}


/**
 * Get a people transaction
 *
 * @return mixed
 */
function erp_acct_get_reimburse_req( $trn_no ) {
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT

    reimburse_req.voucher_no,
    reimburse_req.amount,
    reimburse_req.trn_date,
    reimburse_req.trn_by,
    reimburse_req.voucher_type,

    reimburse_req_acc.id,
    reimburse_req_acc.people_id,
    reimburse_req_acc.trn_no,
    reimburse_req_acc.particulars,
    reimburse_req_acc.debit,
    reimburse_req_acc.credit,
    (reimburse_req_acc.debit-reimburse_req_acc.credit) as balance

    FROM {$wpdb->prefix}erp_acct_people_trn AS reimburse_req
    RIGHT JOIN {$wpdb->prefix}erp_acct_people_account_details AS reimburse_req_acc ON reimburse_req.voucher_no = reimburse_req_acc.trn_no
    WHERE reimburse_req_acc.trn_no = %d", $trn_no );

	$row = $wpdb->get_row( $sql, ARRAY_A );

	return $row;
}

/**
 * Insert a people transaction
 *
 * @return mixed
 */
function erp_acct_insert_reimburse_req( $data ) {
	global $wpdb;

	$created_by         = get_current_user_id();
	$voucher_no         = 0;
	$data['created_at'] = date( "Y-m-d H:i:s" );
	$data['created_by'] = $created_by;
	$data['updated_at'] = date( "Y-m-d H:i:s" );
	$data['updated_by'] = $created_by;
	$currency           = erp_get_option( 'erp_currency', 'erp_settings_general', 'USD' );

	try {
		$wpdb->query( 'START TRANSACTION' );

		$wpdb->insert( $wpdb->prefix . 'erp_acct_reimburse_request', array(
			'people_id'    => $data['people_id'],
			'amount'       => $data['amount'],
			'trn_date'     => $data['trn_date'],
			'trn_by'       => $data['trn_by'],
			'particulars'  => $data['particulars'],
			'created_at'   => $data['created_at'],
			'created_by'   => $created_by,
			'updated_at'   => $data['updated_at'],
			'updated_by'   => $data['updated_by'],
		) );

		$wpdb->query( 'COMMIT' );

	} catch ( Exception $e ) {
		$wpdb->query( 'ROLLBACK' );

		return new WP_error( 'reimburse-req-exception', $e->getMessage() );
	}

	$reimburse_req = erp_acct_get_reimburse_req( $voucher_no );

	do_action( 'erp_acct_new_transaction_reimburse_req', $voucher_no, $reimburse_req );

	return $reimburse_req;
}


