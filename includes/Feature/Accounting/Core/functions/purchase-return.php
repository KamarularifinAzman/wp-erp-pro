<?php

/**
 * Get all purchase return
 *
 * @since 1.1.0
 *
 * @param array $args
 *
 * @return mixed
 */
function erp_acct_get_purchase_return_list( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'    => 20,
        'offset'    => 0,
        'order'     => 'DESC',
        'count'     => false,
        'vendor_id' => false,
        's'         => '',
        'status'    => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE ( voucher.type = 'purchase_return')";

    if ( ! empty( $args['vendor_id'] ) ) {
        $where .= " AND purchase.vendor_id = {$args['vendor_id']} ";
    }

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND  purchase.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' ";
    }

    if ( ! empty( $args['status'] ) ) {
        $where .= " AND purchase.status={$args['status']}  ";
    }

    if ( ! empty( $args['type'] ) ) {
        $where .= " AND voucher.type = '{$args['start_date']}'";
    }

    if ( - 1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = 'SELECT';

    if ( $args['count'] ) {
        $sql .= ' COUNT( DISTINCT voucher.id ) AS total_number';
    } else {
        $sql .= ' voucher.id,
            voucher.type,
            purchase.invoice_id as purchase_voucher_no,
            purchase.vendor_id as vendor_id,
            purchase.vendor_name AS vendor_name,
            purchase.trn_date AS bill_trn_date,
            purchase.amount,
            purchase.tax,
            purchase.discount,
            purchase.status AS purchase_status ';
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
            JOIN {$wpdb->prefix}erp_acct_purchase_return AS purchase ON purchase.voucher_no = voucher.id
            {$where} GROUP BY voucher.id ORDER BY voucher.id {$args['order']} {$limit}";

    erp_disable_mysql_strict_mode();

    if ( $args['count'] ) {
        $wpdb->get_results( $sql );

        return $wpdb->num_rows;
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Insert purchase return invoice
 *
 * @since 1.1.0
 *
 * @param array $data
 *
 * @return string
 */
function erp_acct_insert_purchase_return( $data ) {
    global $wpdb;

    $user_id = get_current_user_id();

    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $user_id;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $user_id;

    $voucher_no = null;
    $currency   = erp_get_currency( true );

    try {

        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            [
                'type'       => 'purchase_return',
                'currency'   => $currency,
                'editable'   => 1,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
            ]
        );

        $voucher_no    = $wpdb->insert_id;
        $invoice_data  = erp_acct_get_formatted_purchase_return_data( $data, $voucher_no );

        $insert_return = $wpdb->insert(
            $wpdb->prefix . 'erp_acct_purchase_return',
            [
                'invoice_id'    => $invoice_data['purchase_voucher_no'],
                'voucher_no'    => $invoice_data['voucher_no'],
                'vendor_id'     => $invoice_data['vendor_id'],
                'vendor_name'   => $invoice_data['vendor_name'],
                'trn_date'      => $invoice_data['return_date'],
                'amount'        => $invoice_data['amount'],
                'discount'      => $invoice_data['discount'],
                'discount_type' => $invoice_data['discount_type'],
                'tax'           => $invoice_data['tax'],
                'reason'        => $invoice_data['return_reason'],
                'comments'      => $invoice_data['comments'],
                'status'        => $invoice_data['status'],
                'created_at'    => $invoice_data['created_at'],
                'created_by'    => $invoice_data['created_by'],
            ]
        );

        if ( ! $insert_return ) {
            throw new Exception( __( "Something went wrong purchase invoice", "erp-pro" ) );
        }


        foreach ( $invoice_data['line_items'] as $item ) {

            $total_tax      = (float) $item['tax'] * (int) $item['qty'];
            $total_discount = (float) $item['discount'] * (int) $item['qty'];

            $insert_details = $wpdb->insert(
                $wpdb->prefix . 'erp_acct_purchase_return_details',
                [
                    'invoice_details_id' => $item['invoice_details_id'],
                    'trn_no'             => $voucher_no,
                    'product_id'         => $item['product_id'],
                    'qty'                => $item['qty'],
                    'price'              => $item['price'],
                    'discount'           => $total_discount,
                    'tax'                => $total_tax,
                    'created_at'         => $invoice_data['created_at'],
                    'created_by'         => $invoice_data['created_by'],
                ]
            );

            if ( ! $insert_details ) {
                throw new Exception( __( "Something went wrong with item", "erp-pro" ) );
            }

        }

        // insert data to purchase return, return discount, return tax ledger
        erp_acct_insert_purchase_return_data_into_ledger( $invoice_data, $voucher_no );

        // insert into purchase  account details
        $insert_purchase_account = $wpdb->insert(
            $wpdb->prefix . 'erp_acct_purchase_account_details',
            [
                'purchase_no' => $invoice_data['purchase_voucher_no'],
                'trn_no'      => $voucher_no,
                'trn_date'    => $invoice_data['return_date'],
                'particulars' => $invoice_data['return_reason'] ? $invoice_data['return_reason'] : __( "Purchase return with voucher no ", "erp-pro" ) . $voucher_no,
                'debit'       => ( $invoice_data['amount'] + $invoice_data['tax'] ) - $invoice_data['discount'],
                'credit'      => 0,
                'created_at'  => $invoice_data['created_at'],
                'created_by'  => $invoice_data['created_by'],
                'updated_at'  => $invoice_data['created_at'],
                'updated_by'  => $invoice_data['created_by'],
            ]
        );

        if ( ! $insert_purchase_account ) {
            throw new Exception( __( "Something went wrong", "erp-pro" ) );
        }

        do_action( 'erp_acct_after_purchase_return', $data, $voucher_no );

        // add people transaction for total amount
        $data['date']        = $invoice_data['return_date'];
        $data['dr']          = 0;
        $data['cr']          = $invoice_data['amount'];
        $data['particulars'] = __( "Total purchase return", "erp-pro" );

        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        // add people transaction for discount
        if ( $invoice_data['discount'] ) {
            $data['dr']          = 0;
            $data['cr']          = $invoice_data['discount'];
            $data['particulars'] = __( "Purchase return discount", "erp-pro" );

            erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );
        }

        // add people transaction for tax
        if ( $invoice_data['tax'] ) {
            $data['dr']          = $invoice_data['tax'];
            $data['cr']          = 0;
            $data['particulars'] = __( "Purchase return tax", "erp-pro" );

            erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );
        }

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {

        $wpdb->query( 'ROLLBACK' );

        return new WP_error( 'sales-return-exception', $e->getMessage() );
    }

    $return_amount = (float) $invoice_data['amount'] - (float) $invoice_data['discount'] + (float) $invoice_data['tax'];

    erp_acct_change_purchase_return_status( $invoice_data['purchase_voucher_no'] );

    $purchase_return_invoice = erp_acct_get_purchase_return_invoice( $voucher_no );

    do_action( 'erp_acct_new_transaction_purchase_return', $voucher_no, $purchase_return_invoice );

    return $voucher_no;
}

/**
 * Insert invoice  data into ledger
 *
 * @since 1.1.0
 *
 * @param array $invoice_data
 * @param int $voucher_no
 * @param bool $contra
 *
 * @return mixed
 */
function erp_acct_insert_purchase_return_data_into_ledger( $invoice_data, $voucher_no = 0, $contra = false ) {
    global $wpdb;

    $user_id = get_current_user_id();
    $date    = date( 'Y-m-d H:i:s' );
    $total   = (float) $invoice_data['amount'];

    $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();

    $purchase_return_ledger_id          = $ledger_map->get_ledger_id_by_slug( 'purchase_return' );
    $purchase_return_vat_ledger_id      = $ledger_map->get_ledger_id_by_slug( 'purchase_return_vat' );
    $purchase_return_discount_ledger_id = $ledger_map->get_ledger_id_by_slug( 'purchase_return_discount' );
    $accounts_payable_ledger_id         = $ledger_map->get_ledger_id_by_slug( 'accounts_payable' );

    // insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        [
            'ledger_id'   => $purchase_return_ledger_id,
            'trn_no'      => $voucher_no,
            'particulars' => "Purchase returned with voucher no " . $voucher_no,
            'debit'       => 0,
            'credit'      => $invoice_data['amount'],
            'trn_date'    => $invoice_data['return_date'],
            'created_at'  => $date,
            'created_by'  => $user_id,
            'updated_at'  => $date,
            'updated_by'  => $user_id,
        ]
    );

    if ( $invoice_data['tax'] > 0 ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $purchase_return_vat_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => "Purchase returned with voucher no " . $voucher_no,
                'debit'       => 0,
                'credit'      => $invoice_data['tax'],
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );

        $total += (float) $invoice_data['tax'];
    }

    if ( $invoice_data['discount'] > 0 ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $purchase_return_discount_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => "Purchase returned with voucher no " . $voucher_no,
                'debit'       => $invoice_data['discount'],
                'credit'      => 0,
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );

        $total -= (float) $invoice_data['discount'];
    }

    if ( (int) $invoice_data['status'] === 4 || (int) $invoice_data['status'] === 5 ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $accounts_payable_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => "Purchase returned with voucher no " . $voucher_no,
                'debit'       => $total,
                'credit'      => 0,
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );
    }

    return $voucher_no;
}

/**
 * Get an purchase invoice for return
 *
 * @since 1.1.0
 *
 * @param int $invoice_no
 *
 * @return mixed
 */
function erp_acct_get_invoice_for_purchase_return( $invoice_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT voucher.editable,
                purchase.id,
                purchase.voucher_no,
                purchase.vendor_id,
                purchase.trn_date,
                purchase.due_date,
                purchase.amount,
                purchase.vendor_name,
                purchase.billing_address,
                purchase.ref,
                purchase.status,
                purchase.purchase_order,
                purchase.attachments,
                purchase.particulars,
                purchase.created_at,
                purchase_acc_detail.purchase_no,
                purchase_acc_detail.debit,
                purchase_acc_detail.credit,
                purchase_return.amount as return_amount,
                purchase_return.discount as return_discount,
                purchase_return.tax as return_tax,
                purchase_return.reason as return_reason,
                purchase_return.comments as return_comments,
                purchase_return.trn_date as return_trn_date
        FROM {$wpdb->prefix}erp_acct_purchase AS purchase
        LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON purchase.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_account_details AS purchase_acc_detail ON purchase.voucher_no = purchase_acc_detail.trn_no
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_return as purchase_return ON purchase.id = purchase_return.invoice_id
        WHERE purchase.voucher_no = %d",
        $invoice_no
    );

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['line_items']  = erp_acct_format_invoice_line_items_for_purchase_return( $invoice_no );
    $row['tax_rate_id'] = erp_acct_get_default_tax_rate_name_id();

    $row['attachments'] = null;

    return $row;
}

/**
 * Get an single purchase return invoice
 *
 * @since 1.1.0
 *
 * @param int $invoice_no
 *
 * @return mixed
 */
function erp_acct_get_purchase_return_invoice( $invoice_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT voucher.editable,
                purchase.id,
                purchase.voucher_no,
                purchase.vendor_id,
                purchase.trn_date,
                purchase.amount,
                purchase.vendor_name,
                purchase.status,
                purchase.tax,
                purchase.discount,
                purchase.discount_type,
                purchase.reason,
                purchase.comments,
                purchase.created_at,
                purchase.invoice_id as purchase_voucher_no
        FROM {$wpdb->prefix}erp_acct_purchase_return AS purchase
        LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON purchase.voucher_no = voucher.id
        WHERE purchase.voucher_no = %d",
        $invoice_no
    );

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['particulars'] = ! empty( $row['reason'] ) ? $row['reason'] : $row['comments'];
    $row['line_items']  = erp_acct_format_purchase_return_line_items( $invoice_no );
    $row['pdf_link']    = erp_acct_pdf_abs_path_to_url( $invoice_no['voucher_no'] );

    return $row;
}

/**
 * Get purchase return items
 *
 * @since 1.1.0
 *
 * @param int $voucher_no
 *
 * @return mixed
 */
function erp_acct_format_purchase_return_line_items( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT inv_detail.id,
                inv_detail.invoice_details_id ,
                inv_detail.product_id,
                inv_detail.qty,
                inv_detail.price,
                inv_detail.tax,
                product.name,
                product.product_type_id,
                product.category_id,
                product.vendor
        FROM  {$wpdb->prefix}erp_acct_purchase_return_details as inv_detail
        LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
        WHERE inv_detail.trn_no = %d GROUP BY inv_detail.id",
        $voucher_no
    );

    erp_disable_mysql_strict_mode();

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get purchase invoice items for return
 *
 * @since 1.1.0
 *
 * @param int $voucher_no
 *
 * @return mixed
 */
function erp_acct_format_invoice_line_items_for_purchase_return( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT inv_detail.id,
                inv_detail.id as invoice_details_id,
                inv_detail.product_id,
                inv_detail.qty,
                inv_detail.price,
                inv_detail.tax,
                SUM(inv_detail_tax.tax_rate) as tax_rate,
                product.name,
                product.product_type_id,
                product.category_id,
                product.vendor,
                product.tax_cat_id,
                return_inv_detail.qty as return_qty,
                return_inv_detail.price as return_unit_price,
                return_inv_detail.discount as return_discount,
                return_inv_detail.tax as return_tax
        FROM  {$wpdb->prefix}erp_acct_purchase_details as inv_detail
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_return_details as return_inv_detail ON inv_detail.id = return_inv_detail.invoice_details_id
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_details_tax  as inv_detail_tax ON inv_detail.id = inv_detail_tax.invoice_details_id
        LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
        WHERE inv_detail.trn_no = %d GROUP BY inv_detail.id",
        $voucher_no
    );

    erp_disable_mysql_strict_mode();

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Change purchase status after returning
 *
 * @since 1.1.0
 *
 * @param int|string $purchase_no
 *
 * @return void
 */
function erp_acct_change_purchase_return_status( $purchase_no ) {
    global $wpdb;

    $total_amount  = erp_acct_get_purchase_amount_by_voucher( $purchase_no );
    $return_amount = erp_acct_get_purchase_return_amount_by_voucher( $purchase_no );

    $status        = ( (float) $return_amount < (float) abs( $total_amount ) ) ? 10 : 9;

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_purchase',
        [ 'status' => $status ],
        [ 'voucher_no' => $purchase_no ]
    );
}

/**
 * Retrieves total return amount of a specific purchase invoice
 *
 * @since 1.1.0
 *
 * @param int|string $invoice
 *
 * @return string
 */
function erp_acct_get_purchase_return_amount_by_voucher( $invoice ) {
    global $wpdb;

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(amount+tax-discount)
            FROM {$wpdb->prefix}erp_acct_purchase_return
            WHERE invoice_id = %d
            GROUP BY invoice_id",
            (int) $invoice
        )
    );
}

/**
 * Retrieves total return amount of a specific purchase voucher
 *
 * @since 1.1.0
 *
 * @param int|string $voucher_no
 *
 * @return string
 */
function erp_acct_get_purchase_amount_by_voucher( $voucher_no ) {
    global $wpdb;

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT amount
            FROM {$wpdb->prefix}erp_acct_purchase
            WHERE voucher_no = %d",
            (int) $voucher_no
        )
    );
}

/**
 * Get formatted purchase return data
 *
 * @since 1.1.0
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_purchase_return_data( $data, $voucher_no ) {
    $invoice_data = [];

    $user_info = erp_get_people( $data['vendor_id'] );

    $invoice_data['purchase_voucher_no'] = isset( $data['purchase_voucher_no'] ) ? $data['purchase_voucher_no'] : null;
    $invoice_data['voucher_no']          = ! empty( $voucher_no ) ? $voucher_no : 0;
    $invoice_data['vendor_id']           = isset( $data['vendor_id'] ) ? $data['vendor_id'] : null;
    $invoice_data['vendor_name']         = $user_info->first_name . ' ' . $user_info->last_name;
    $invoice_data['return_date']         = isset( $data['return_date'] ) ? $data['return_date'] : date( 'Y-m-d' );
    $invoice_data['amount']              = isset( $data['amount'] ) ? $data['amount'] : 0;
    $invoice_data['discount']            = isset( $data['discount'] ) ? $data['discount'] : 0;
    $invoice_data['discount_type']       = isset( $data['discount_type'] ) ? $data['discount_type'] : null;
    $invoice_data['tax_rate_id']         = isset( $data['tax_rate_id'] ) ? $data['tax_rate_id'] : 0;
    $invoice_data['line_items']          = isset( $data['line_items'] ) ? $data['line_items'] : [];
    $invoice_data['tax']                 = isset( $data['tax'] ) ? $data['tax'] : 0;
    $invoice_data['attachments']         = ! empty( $data['attachments'] ) ? $data['attachments'] : '';
    $invoice_data['status']              = 9;
    $invoice_data['return_reason']       = ! empty( $data['return_reason'] ) ? $data['return_reason'] : '';
    $invoice_data['comments']            = sprintf( __( 'Invoice created with voucher no %s', 'erp-pro' ), $voucher_no );
    $invoice_data['created_at']          = isset( $data['created_at'] ) ? $data['created_at'] : null;
    $invoice_data['created_by']          = isset( $data['created_by'] ) ? $data['created_by'] : null;
    $invoice_data['updated_at']          = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $invoice_data['updated_by']          = isset( $data['updated_by'] ) ? $data['updated_by'] : null;

    return $invoice_data;
}
