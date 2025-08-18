<?php
/**
 * Get all people transactions
 *
 * @return mixed
 */
function erp_acct_get_all_people_trns( $args = [] ) {
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

    $sql .= "FROM {$wpdb->prefix}erp_acct_people_account_details GROUP BY trn_no ORDER BY id DESC";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get all people balances
 *
 * @return mixed
 */
function erp_acct_get_all_people_balances( $args = [] ) {
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
    $sql .= $args['count'] ? " COUNT(id) as total_number " : " *, SUM(debit - credit) as balance ";

    $sql .= "FROM {$wpdb->prefix}erp_acct_people_account_details GROUP BY people_id";

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
function erp_acct_get_people_trn( $trn_no ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT

    people_trn.voucher_no,
    people_trn.amount,
    people_trn.trn_date,
    people_trn.trn_by,
    people_trn.voucher_type,

    people_trn_acc.id,
    people_trn_acc.people_id,
    people_trn_acc.trn_no,
    people_trn_acc.particulars,
    people_trn_acc.debit,
    people_trn_acc.credit,
    (people_trn_acc.debit-people_trn_acc.credit) as balance

    FROM {$wpdb->prefix}erp_acct_people_trn AS people_trn
    RIGHT JOIN {$wpdb->prefix}erp_acct_people_account_details AS people_trn_acc ON people_trn.voucher_no = people_trn_acc.trn_no
    WHERE people_trn_acc.trn_no = %d", $trn_no );

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Insert a people transaction
 *
 * @return mixed
 */
function erp_acct_insert_people_trn( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $voucher_no         = 0;
    $data['created_at'] = date( 'Y-m-d' );
    $data['created_by'] = $created_by;
    $data['updated_at'] = date( 'Y-m-d' );
    $data['updated_by'] = $created_by;
    $currency           = erp_get_option( 'erp_currency', 'erp_settings_general', 'USD' );

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'people_trn',
            'currency'   => $currency,
            'created_at' => $data['created_at'],
            'created_by' => $created_by,
            'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
            'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : ''
        ) );

        $voucher_no = $wpdb->insert_id;
        $data['voucher_no'] = $voucher_no;

        $debit  = 0;
        $credit = 0;
        $data['source_credit'] = 0;
        $data['source_debit']  = 0;
        if ( 'debit' == $data['voucher_type'] ) {
            $debit = $data['amount'];
            $data['source_credit'] = $debit;
        } elseif ( 'credit' == $data['voucher_type'] ) {
            $credit = $data['amount'];
            $data['source_debit'] = $credit;
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
            'people_id'    => $data['people_id'],
            'trn_no'       => $voucher_no,
            'debit'        => $debit,
            'credit'       => $credit,
            'trn_date'     => $data['trn_date'],
            'trn_by'       => $data['trn_by'],
            'particulars'  => $data['particulars'],
            'voucher_type' => $data['voucher_type'],
            'created_at'   => $data['created_at'],
            'created_by'   => $data['created_by'],
            'updated_at'   => $data['updated_at'],
            'updated_by'   => $data['updated_by']
        ) );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_people_trn', array(
            'people_id'    => $data['people_id'],
            'voucher_no'   => $voucher_no,
            'amount'       => $data['amount'],
            'trn_date'     => $data['trn_date'],
            'trn_by'       => $data['trn_by'],
            'particulars'  => $data['particulars'],
            'voucher_type' => $data['voucher_type'],
            'created_at'   => $data['created_at'],
            'created_by'   => $created_by,
            'updated_at'   => $data['updated_at'],
            'updated_by'   => $data['updated_by'],
        ) );

        erp_acct_insert_people_data_into_ledger( $data );

        $data['dr'] = $debit;
        $data['cr'] = $credit;
        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        if ( ! empty( $data['request_id'] ) ) {
            $status_closed = 7;

            $wpdb->update(
                $wpdb->prefix . 'erp_acct_reimburse_requests',
                ['status' => $status_closed],
                ['id' => $data['request_id']],
                ['%d'],
                ['%d']
            );
        }

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'people_trn-exception', $e->getMessage() );
    }

    $people_trn = erp_acct_get_people_trn( $voucher_no );

    do_action( 'erp_acct_new_transaction_people_trn', $voucher_no, $people_trn );

    return $people_trn;
}

/**
 * Insert people data into ledger
 *
 * @param array $people_data
 *
 * @return mixed
 */
function erp_acct_insert_people_data_into_ledger( $people_data ) {
    global $wpdb;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $people_data['ledger_id'],
        'trn_no'      => $people_data['voucher_no'],
        'particulars' => $people_data['particulars'],
        'debit'       => $people_data['source_debit'],
        'credit'      => $people_data['source_credit'],
        'trn_date'    => $people_data['trn_date'],
        'created_at'  => $people_data['created_at'],
        'created_by'  => $people_data['created_by'],
        'updated_at'  => $people_data['updated_at'],
        'updated_by'  => $people_data['updated_by']
    ) );

}

/**
 * Requests chart data
 *
 * @param array $args
 * @return array
 */
function erp_acct_reimb_get_req_chart_data( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['emp_user_id'] ) ) {
        $people = erp_get_people_by( 'user_id', $args['emp_user_id']);
        $where .= " AND people_id = {$people->id}";
    }

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $awaiting = $wpdb->get_var(
        "SELECT sum(amount_total) from {$wpdb->prefix}erp_acct_reimburse_requests
        WHERE status = 2 {$where}"
    );

    $received = $wpdb->get_var(
        "SELECT sum(amount_total) from {$wpdb->prefix}erp_acct_reimburse_requests
        WHERE status = 4 {$where}"
    );

    return [
        'outstanding' => $awaiting,
        'received'    => $received
    ];
}

/**
 * Reimbursement employee chart
 *
 * @param array $args
 * @return array
 */
function erp_acct_reimb_empl_chart_data( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " WHERE trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $debit_where = empty( $where ) ? 'WHERE status = 7' : $where  . ' AND status = 7';

    $credit_where = empty( $where ) ? 'WHERE status = 2' : $where  . ' AND status = 2';

    if ( ! empty( $args['people_id'] ) ) {
        $debit_where  .= " AND people_id = {$args['people_id']}";
        $credit_where .= " AND people_id = {$args['people_id']}";
    }

    $debit = $wpdb->get_var(
        "SELECT SUM(amount_total) FROM {$wpdb->prefix}erp_acct_reimburse_requests {$debit_where}"
    );

    $credit = $wpdb->get_var(
        "SELECT SUM(amount_total) FROM {$wpdb->prefix}erp_acct_reimburse_requests {$credit_where}"
    );

    return [
        'received'    => $debit,
        'outstanding' => $credit,
    ];
}

/**
 * Reimbursement chart data
 *
 * @param array $args
 * @return array
 */
function erp_acct_reimb_chart_data( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " WHERE trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $debit = $wpdb->get_var(
        "SELECT SUM(debit) FROM {$wpdb->prefix}erp_acct_people_account_details WHERE voucher_type = 'debit'"
    );

    $credit = $wpdb->get_var(
        "SELECT SUM(credit) FROM {$wpdb->prefix}erp_acct_people_account_details WHERE voucher_type = 'credit'"
    );

    return [
        'debit'  => $debit,
        'credit' => $credit,
    ];
}

/**
 * Reimbursement chart status
 * 
 * @return array
 */
function erp_acct_reimb_chart_status( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " WHERE trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT COUNT(trn_no) as sub_total, voucher_type as type_name
        FROM {$wpdb->prefix}erp_acct_people_account_details {$where} GROUP BY voucher_type";

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Request Chart status
 *
 * @return array
 */
function erp_acct_reimb_empl_chart_status( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " WHERE trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( empty( $where ) ) {
        if ( ! empty( $args['people_id'] ) ) {
            $where .= " WHERE people_id = {$args['people_id']}";
        }
    } else {
        if ( ! empty( $args['people_id'] ) ) {
            $where .= " AND people_id = {$args['people_id']}";
        }
    }

    $sql = "SELECT status_type.type_name, COUNT(reimburse_request.status) as sub_total
            FROM {$wpdb->prefix}erp_acct_reimburse_requests as reimburse_request
            LEFT JOIN {$wpdb->prefix}erp_acct_trn_status_types as status_type
            ON reimburse_request.status = status_type.id
            {$where} GROUP BY reimburse_request.status";

    return $wpdb->get_results( $sql, ARRAY_A );
}
