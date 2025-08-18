<?php
/**
 * Employee reimbursement requests helper
 */


 /**
  * Get employee all requests
  *
  * @return void
  */
function erp_acct_get_employee_reimb_requests( $args = [] ) {
    global $wpdb;

    $defaults = [
        'people_id' => null,
        'number'    => 20,
        'offset'    => 0,
        'orderby'   => 'id',
        'order'     => 'DESC',
        'count'     => false,
        's'         => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where = '';
    $limit = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where = "WHERE request.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['status'] ) ) {
        if ( ! empty( $where ) ) {
            $where .= ' AND';
        } else {
            $where = 'WHERE';
        }

        $where .= " request.status = '{$args['status']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        if ( ! empty( $where ) ) {
            $where .= ' AND';
        } else {
            $where = 'WHERE';
        }

        $where .= " request.people_id = {$args['people_id']}";
    }

    if ( -1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = 'SELECT';

    if ( $args['count'] ) {
        $sql .= ' COUNT( request.id ) as total_number';
    } else {
        $sql .= " CONCAT(people.first_name, ' ', people.last_name) as people_name, request.*";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_reimburse_requests AS request LEFT JOIN {$wpdb->prefix}erp_peoples as people ON people.id = request.people_id {$where} ORDER BY request.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Reimbursement insert request
 *
 * @param array $data
 * 
 * @return bool
 */
function erp_acct_reimb_insert_request( $data ) {
    global $wpdb;

    if ( empty( $data['user_id'] ) ) {
        $people = erp_get_people_by('user_id', get_current_user_id());
        $people_id = $people->id;
    } else {
        $people = erp_get_people_by('user_id', $data['user_id']);
        $people_id = $people->id;
    }

    $awaiting_payment = 2;
    $validation = [ '%d', '%s', '%s', '%d', '%f', '%s', '%s', '%s' ];

    $formated_data = [
        'people_id'    => absint( $people_id ),
        'trn_date'     => $data['trn_date'],
        'reference'    => $data['reference'],
        'status'       => empty( $data['status'] ) ? $awaiting_payment : $data['status'],
        'amount_total' => $data['amount_total'],
        'attachments'  => $data['attachments'],
        'particulars'  => $data['particulars'],
        'created_at'   => date('Y-m-d')
    ];

    if ( ! empty( $data['id'] ) ) {
        $formated_data['id'] = absint( $data['id'] );
        $validation[] = '%d';
    }

    // Insert into DATABASE
    $wpdb->insert( "{$wpdb->prefix}erp_acct_reimburse_requests", $formated_data, $validation );

    $request_id = $wpdb->insert_id;

    erp_acct_insert_request_details_data( $data['line_items'], $request_id );

    return erp_acct_get_reimb_employee_request( $request_id );
}

/**
 * Reimbursement update request
 *
 * @param array $data
 * 
 * @return bool
 */
function erp_acct_reimb_update_request( $data ) {
    global $wpdb;

    $request_id = absint( $data['id'] );

    $request_table = $wpdb->prefix . 'erp_acct_reimburse_requests';

    $sql    = $wpdb->prepare( "SELECT `status` FROM {$request_table} WHERE id = %d", $request_id );
    $status = $wpdb->get_var( $sql );

    $awaiting_payment = 2;
    $status_closed    = 7;

    if ( $status == $status_closed ) {
        return;
    }

    $formated_data = [
        'trn_date'     => $data['trn_date'],
        'reference'    => $data['reference'],
        'status'       => $awaiting_payment,
        'amount_total' => $data['amount_total'],
        'attachments'  => $data['attachments'],
        'particulars'  => $data['particulars'],
        'updated_at'   => date('Y-m-d')
    ];

    $wpdb->update(
        $request_table,
        $formated_data,
        [ 'id' => $request_id ],
        [ '%s', '%s', '%d', '%f', '%s', '%s', '%s' ],
        ['%d']
    );

    /**
     * remove and insert new details
     * we can't update details because before there may be 10 data
     * now updated details may be 4 data
     * 
     */
    $wpdb->delete(
        $wpdb->prefix . 'erp_acct_reimburse_request_details',
        ['request_id' => $request_id],
        ['%d']
    );

    erp_acct_insert_request_details_data( $data['line_items'], $request_id );

    return erp_acct_get_reimb_employee_request( $request_id );
}

/**
 * Reimbursement insert details data
 *
 * @param array $data
 * 
 * @return bool
 */
function erp_acct_insert_request_details_data( $line_items, $request_id ) {
    if ( ! $request_id ) {
        return;
    }

    global $wpdb;

    foreach ( $line_items as $item ) {
        $wpdb->insert( 
            "{$wpdb->prefix}erp_acct_reimburse_request_details",
            [
                'request_id'  => $request_id,
                'particulars' => $item['particulars'],
                'amount'      => $item['amount']
            ],
            [ '%d', '%s', '%s' ]
        );
    }
}

/**
 * Get employee request
 *
 * @param int $request_id
 * 
 * @return array
 */
function erp_acct_get_reimb_employee_request( $request_id ) {
    global $wpdb;

    // get request data along with details data
    $sql = $wpdb->prepare(
        "SELECT
            request.id, request.people_id, request.trn_date, request.particulars,
            request.status, request.amount_total, request.created_at, request.reference,
            request.attachments, request_detail.particulars AS detail_particulars, request_detail.amount
        FROM {$wpdb->prefix}erp_acct_reimburse_requests AS request
        LEFT JOIN {$wpdb->prefix}erp_acct_reimburse_request_details AS request_detail
        ON request.id = request_detail.request_id WHERE request.id = %d",
        $request_id
    );

    $requests = $wpdb->get_results( $sql, ARRAY_A );

    // because every data is same in requests array except `details_data`
    // we can get the basic data from first array item
    $first_item = reset( $requests );

    // oh we need the employee name
    $people = erp_get_people_by( 'id', $first_item['people_id'] );

    // lets make it pretty
    $formatted  = [
        'id'           => $first_item['id'],
        'people_id'    => $first_item['people_id'],
        'people_name'  => $people->first_name . ' ' . $people->last_name,
        'trn_date'     => $first_item['trn_date'],
        'amount_total' => $first_item['amount_total'],
        'reference'    => $first_item['reference'],
        'particulars'  => $first_item['particulars'],
        'attachments'  => unserialize( $first_item['attachments'] ),
        'status'       => $first_item['status'],
        'created_at'   => $first_item['created_at'],
        'amount'       => 0,
        'line_items'   => []
    ];

    // push details in line items
    // and help calculate total amount
    foreach ( $requests as $request ) {
        $formatted['line_items'][] = [
            'particulars' => $request['detail_particulars'],
            'amount'      => (float) $request['amount']
        ];

        $formatted['amount'] += (float) $request['amount'];
    }

    return $formatted;
}
