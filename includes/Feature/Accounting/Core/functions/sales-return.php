<?php

/**
 * Get all sales return lists with pagination
 *
 * @since 1.1.0
 *
 * @param array $args
 *
 * @return mixed
 */
function erp_acct_get_sales_return_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'      => 20,
        'offset'      => 0,
        'order'       => 'DESC',
        'count'       => false,
        'customer_id' => false,
        's'           => '',
        'status'      => '',
    ];

    $args  = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE voucher.type = 'sales_return'";

    if ( ! empty( $args['customer_id'] ) ) {
        $where .= " AND invoice.customer_id = {$args['customer_id']} ";
    }

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' ";
    }

    if ( empty( $args['status'] ) ) {
        $where .= '';
    } else {
        $where .= " AND invoice.status={$args['status']} ";
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
            voucher.editable,
            invoice.invoice_id as sales_invoice_id,
            invoice.customer_id,
            invoice.customer_name,
            invoice.trn_date,
            invoice.amount,
            invoice.discount,
            invoice.discount_type,
            invoice.tax,
            invoice.reason,
            invoice.comments,
            invoice.status AS status_code,
            (invoice.amount + invoice.tax) - invoice.discount AS sales_amount';
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
            INNER JOIN {$wpdb->prefix}erp_acct_sales_return AS invoice
            ON invoice.voucher_no = voucher.id
            {$where} GROUP BY voucher.id
            ORDER BY voucher.id {$args['order']} {$limit}";

    erp_disable_mysql_strict_mode();

    if ( $args['count'] ) {
        $wpdb->get_results( $sql );

        return $wpdb->num_rows;
    }

    $trns = $wpdb->get_results( $sql, ARRAY_A );

    $ledger_map             = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();
    $shipping_ledger_id     = $ledger_map->get_ledger_id_by_slug( 'shipment' );
    $shipping_tax_ledger_id = $ledger_map->get_ledger_id_by_slug( 'shipment_tax' );

    foreach ( $trns AS &$trn ) {
        $trn['shipping'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT IFNULL( SUM( debit ), 0 )
                FROM {$wpdb->prefix}erp_acct_ledger_details
                WHERE ledger_id = %d
                AND trn_no = %d",
                [ $shipping_ledger_id, $trn['id'] ]
            )
        );

        $trn['shipping_tax'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT IFNULL( SUM( debit ), 0 )
                FROM {$wpdb->prefix}erp_acct_ledger_details
                WHERE ledger_id = %d
                AND trn_no = %d",
                [ $shipping_tax_ledger_id, $trn['id'] ]
            )
        );

        $trn['sales_amount'] += $trn['shipping'] + $trn['shipping_tax'];
    }

    return $trns;
}

/**
 * Insert invoice data
 *
 * @since 1.1.0
 *
 * @param $data
 *
 * @return string
 */
function erp_acct_insert_sales_return( $data ) {
    global $wpdb;

    $user_id            = get_current_user_id();

    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $user_id;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $user_id;

    $voucher_no         = null;
    $tax_by_agency      = [];
    $currency           = erp_get_currency( true );

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            [
                'type'       => 'sales_return',
                'currency'   => $currency,
                'editable'   => 1,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
            ]
        );

        $voucher_no   = $wpdb->insert_id;

        $invoice_data = erp_acct_get_formatted_sales_return_data( $data, $voucher_no );

        $insert_eturn = $wpdb->insert(
            $wpdb->prefix . 'erp_acct_sales_return',
            [
                'invoice_id'    => $invoice_data['sales_voucher_no'],
                'voucher_no'    => $invoice_data['voucher_no'],
                'customer_id'   => $invoice_data['customer_id'],
                'customer_name' => $invoice_data['customer_name'],
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

        if ( ! $insert_eturn ) {
            throw new Exception( __( "Something went wrong", "erp-pro" ) );
        }

        $tax_zone_id = erp_acct_get_invoice_tax_zone( $invoice_data['sales_voucher_no'] );

        foreach ( $invoice_data['line_items'] as $item ) {
            $insert_details = $wpdb->insert(
                $wpdb->prefix . 'erp_acct_sales_return_details',
                [
                    'invoice_details_id' => $item['invoice_details_id'],
                    'trn_no'             => $voucher_no,
                    'product_id'         => $item['product_id'],
                    'qty'                => $item['qty'],
                    'unit_price'         => $item['unit_price'],
                    'discount'           => $item['discount'],
                    'tax'                => $item['tax'],
                    'item_total'         => $item['line_total'],
                    'ecommerce_type'     => $item['discount_type'],
                    'created_at'         => $invoice_data['created_at'],
                    'created_by'         => $invoice_data['created_by'],
                ]
            );

            if ( ! $insert_details ) {
                throw new Exception( __( "Something went wrong with item", "erp-pro" ) );
            }

            if ( floatval( $invoice_data['tax'] ) > 0 ) {
                $tax_agency_details = erp_acct_get_invoice_items_agency_wise_tax_rate( $item['invoice_details_id'] );

                foreach ( $tax_agency_details as $tax_agency ) {

                    if ( (int) $item['qty'] > 0 ) {
                        $tax = ( ( (float) $item['unit_price'] * (float) $item['qty'] ) - (float) $item['discount'] ) * (float) $tax_agency['tax_rate'] / 100.00;

                        if ( array_key_exists( $tax_agency['agency_id'], $tax_by_agency ) ) {
                            $tax_by_agency[ $tax_agency['agency_id'] ] += $tax;
                        } else {
                            $tax_by_agency[ $tax_agency['agency_id'] ] = $tax;
                        }
                    }

                }

            }
        }

        foreach ( $tax_by_agency as $agency => $tax ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_tax_agency_details',
                [
                    'agency_id'   => $agency,
                    'trn_no'      => $voucher_no,
                    'trn_date'    => $invoice_data['return_date'],
                    'particulars' => 'sales return',
                    'debit'       => $tax,
                    'credit'      => 0,
                    'created_at'  => $invoice_data['created_at'],
                    'created_by'  => $invoice_data['created_by'],
                ]
            );
        }

        // insert data to sales return, return discount, return tax ledger
        erp_acct_insert_sales_return_data_into_ledger( $invoice_data, $voucher_no );

        // insert into sales account details
        $wpdb->insert(
            "{$wpdb->prefix}erp_acct_invoice_account_details", [
                'invoice_no'  => $invoice_data['sales_voucher_no'],
                'trn_no'      => $voucher_no,
                'trn_date'    => $invoice_data['return_date'],
                'particulars' => __( "Sales returned with voucher no ", "erp-pro" ) . $voucher_no,
                'debit'       => 0,
                'credit'      => ( $invoice_data['amount'] + $invoice_data['tax'] + $invoice_data['shipping'] + $invoice_data['shipping_tax']  ) - $invoice_data['discount'],
                'created_at'  => $invoice_data['created_at'],
                'created_by'  => $user_id,
            ]
        );

        do_action( 'erp_acct_after_sale_return', $data, $voucher_no );

        // add people transaction
        $data['date']        = $invoice_data['return_date'];
        $data['dr']          = 0;
        $data['cr']          = $invoice_data['amount'];
        $data['particulars'] = __( "Total sales return", "erp-pro" );

        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        if ( $invoice_data['discount'] ) {
            $data['dr']          = $invoice_data['discount'];
            $data['cr']          = 0;
            $data['particulars'] = __( "Sales return discount", "erp-pro" );

            erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );
        }

        if ( $invoice_data['tax'] ) {
            $data['dr']          = 0;
            $data['cr']          = $invoice_data['tax'];
            $data['particulars'] = __( "Sales return tax", "erp-pro" );

            erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );
        }

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {

        $wpdb->query( 'ROLLBACK' );

        return new WP_error( 'sales-return-exception', $e->getMessage() );
    }

    erp_acct_change_sales_status_for_return( $invoice_data['sales_voucher_no'] );

    $sales_return_invoice = erp_acct_get_sales_return_invoice( $voucher_no );

    do_action( 'erp_acct_new_transaction_sales_return', $voucher_no, $sales_return_invoice );

    return $voucher_no;
}

/**
 * Insert invoice/s data into ledger
 *
 * @since 1.1.0
 *
 * @param array $invoice_data
 * @param int $voucher_no
 * @param bool $contra
 *
 * @return mixed
 */
function erp_acct_insert_sales_return_data_into_ledger( $invoice_data, $voucher_no = 0, $contra = false ) {
    global $wpdb;

    $user_id = get_current_user_id();
    $date    = date( 'Y-m-d H:i:s' );

    $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();

    $sales_return_ledger_id          = $ledger_map->get_ledger_id_by_slug( 'sales_returns_and_allowance' );
    $sales_return_tax_ledger_id      = $ledger_map->get_ledger_id_by_slug( 'sales_return_tax' );
    $sales_return_discount_ledger_id = $ledger_map->get_ledger_id_by_slug( 'sales_return_discount' );
    $sales_shipping_ledger_id        = $ledger_map->get_ledger_id_by_slug( 'shipment' );
    $sales_shipping_tax_ledger_id    = $ledger_map->get_ledger_id_by_slug( 'shipment_tax' );

    // insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        [
            'ledger_id'   => $sales_return_ledger_id,
            'trn_no'      => $voucher_no,
            'particulars' => "Sales returned with voucher no $voucher_no",
            'debit'       => $invoice_data['amount'],
            'credit'      => 0,
            'trn_date'    => $invoice_data['return_date'],
            'created_at'  => $date,
            'created_by'  => $user_id,
            'updated_at'  => $date,
            'updated_by'  => $user_id,
        ]
    );

    // if ( $invoice_data['tax'] > 0 ) {
    //     $wpdb->insert(
    //         $wpdb->prefix . 'erp_acct_ledger_details',
    //         [
    //             'ledger_id'   => $sales_return_tax_ledger_id,
    //             'trn_no'      => $voucher_no,
    //             'particulars' => "Sales returned with voucher no " . $voucher_no,
    //             'debit'       => $invoice_data['tax'],
    //             'credit'      => 0,
    //             'trn_date'    => $invoice_data['return_date'],
    //             'created_at'  => $date,
    //             'created_by'  => $user_id,
    //             'updated_at'  => $date,
    //             'updated_by'  => $user_id,
    //         ]
    //     );
    // }

    if ( $invoice_data['discount'] > 0 ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $sales_return_discount_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => 'Sales returned with voucher no ' . $voucher_no,
                'debit'       => 0,
                'credit'      => $invoice_data['discount'],
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );
    }

    // insert shipping in ledger_details
    if ( (float) $invoice_data['shipping'] > 0 ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $sales_shipping_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => 'Sales returned with voucher no ' . $voucher_no,
                'debit'       => $invoice_data['shipping'],
                'credit'      => 0,
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );
    }

    // insert shipping tax in ledger_details
    if ( (float) $invoice_data['shipping_tax'] > 0 ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $sales_shipping_tax_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => 'Sales returned with voucher no ' . $voucher_no,
                'debit'       => $invoice_data['shipping_tax'],
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
 * Get an single sales invoice for return
 *
 * @since 1.1.0
 *
 * @param $invoice_no
 *
 * @return mixed
 */
function erp_acct_get_invoice_for_return( $invoice_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
            voucher.editable,
            voucher.currency,
            invoice.id,
            invoice.voucher_no,
            invoice.customer_id,
            invoice.customer_name,
            invoice.trn_date,
            invoice.due_date,
            invoice.billing_address,
            invoice.amount,
            invoice.discount,
            invoice.discount_type,
            invoice.tax,
            invoice.estimate,
            invoice.attachments,
            invoice.status,
            invoice.particulars,
            invoice.created_at,
            inv_acc_detail.debit,
            inv_acc_detail.credit,
            sales_return.amount as return_amount,
            sales_return.discount as return_discount,
            sales_return.tax as return_tax,
            sales_return.reason as return_reason,
            sales_return.comments as return_comments,
            sales_return.trn_date as return_trn_date

        FROM {$wpdb->prefix}erp_acct_invoices as invoice
        LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON invoice.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details as inv_acc_detail ON invoice.voucher_no = inv_acc_detail.trn_no
        LEFT JOIN {$wpdb->prefix}erp_acct_sales_return as sales_return ON invoice.id = sales_return.invoice_id
        WHERE invoice.voucher_no = %d",
        $invoice_no
    );

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['line_items'] = erp_acct_format_invoice_line_items_for_sales_return( $invoice_no );


    // calculate every line total
    foreach ( $row['line_items'] as $key => $value ) {
        $total                                   = ( $value['item_total'] + $value['tax'] ) - $value['discount'];
        $row['line_items'][ $key ]['line_total'] = $total;
    }

    $row['attachments'] = maybe_unserialize( $row['attachments'] );
    $row['total_due']   = erp_acct_get_invoice_due( $invoice_no );

    return $row;
}

/**
 * Get an single sales return invoice
 *
 * @since 1.1.0
 *
 * @param $invoice_no
 *
 * @return mixed
 */
function erp_acct_get_sales_return_invoice( $invoice_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
            voucher.editable,
            voucher.currency,
            invoice.id,
            invoice.invoice_id as sales_voucher_id,
            invoice.voucher_no,
            invoice.customer_id,
            invoice.customer_name,
            invoice.trn_date,
            invoice.amount,
            invoice.discount,
            invoice.discount_type,
            invoice.tax,
            invoice.status,
            invoice.reason,
            invoice.comments,
            invoice.created_at

        FROM {$wpdb->prefix}erp_acct_sales_return as invoice
        LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON invoice.voucher_no = voucher.id
        WHERE invoice.voucher_no = %d",
        $invoice_no
    );

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['particulars'] = ! empty( $row['reason'] ) ? $row['reason'] : $row['comments'];
    $row['line_items']  = erp_acct_format_sales_return_invoice_line_items( $invoice_no );
    $row['pdf_link']    = erp_acct_pdf_abs_path_to_url( $invoice_no['voucher_no'] );

    $ledger_map             = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();
    $shipping_ledger_id     = $ledger_map->get_ledger_id_by_slug( 'shipment' );
    $shipping_tax_ledger_id = $ledger_map->get_ledger_id_by_slug( 'shipment_tax' );

    $row['shipping'] = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT IFNULL( SUM( debit ), 0 )
            FROM {$wpdb->prefix}erp_acct_ledger_details
            WHERE ledger_id = %d
            AND trn_no = %d",
            [ $shipping_ledger_id, $row['id'] ]
        )
    );

    $row['shipping_tax'] = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT IFNULL( SUM( debit ), 0 )
            FROM {$wpdb->prefix}erp_acct_ledger_details
            WHERE ledger_id = %d
            AND trn_no = %d",
            [ $shipping_tax_ledger_id, $row['id'] ]
        )
    );

    return $row;
}

/**
 * Get sales items of sales invoice
 */
function erp_acct_format_invoice_line_items_for_sales_return( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
            inv_detail.id as invoice_details_id,
            inv_detail.product_id,
            inv_detail.qty,
            inv_detail.unit_price,
            inv_detail.discount,
            inv_detail.tax,
            inv_detail.item_total,
            inv_detail.ecommerce_type,
            SUM(inv_detail_tax.tax_rate) as tax_rate,
            product.name,
            product.product_type_id,
            product.category_id,
            product.vendor,
            product.cost_price,
            product.sale_price,
            product.tax_cat_id,
            return_inv_detail.qty as return_qty,
            return_inv_detail.unit_price as return_unit_price,
            return_inv_detail.discount as return_discount,
            return_inv_detail.tax as return_tax

        FROM  {$wpdb->prefix}erp_acct_invoice_details as inv_detail
        LEFT JOIN {$wpdb->prefix}erp_acct_sales_return_details as return_inv_detail ON inv_detail.id = return_inv_detail.invoice_details_id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_details_tax as inv_detail_tax ON inv_detail.id = inv_detail_tax.invoice_details_id
        LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
        WHERE inv_detail.trn_no = %d GROUP BY inv_detail.id",
        $voucher_no
    );

    erp_disable_mysql_strict_mode();

    $results = $wpdb->get_results( $sql, ARRAY_A );

    if ( ! empty( reset( $results )['ecommerce_type'] ) ) {
        // product name should not fetch form `erp_acct_products`
        $results = array_map(
            function ( $result ) {
                $result['name'] = get_the_title( $result['product_id'] );

                return $result;
            },
            $results
        );
    }

    return $results;
}

/**
 * Get sales return items
 *
 * @since 1.1.0
 *
 * @param $voucher_no
 *
 * @return array
 */
function erp_acct_format_sales_return_invoice_line_items( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
            inv_detail.id,
            inv_detail.invoice_details_id,
            inv_detail.product_id,
            inv_detail.qty,
            inv_detail.unit_price,
            inv_detail.discount,
            inv_detail.tax,
            inv_detail.item_total,
            inv_detail.ecommerce_type,
            product.name,
            product.product_type_id,
            product.category_id,
            product.vendor,
            product.cost_price,
            product.sale_price,
            product.tax_cat_id

        FROM  {$wpdb->prefix}erp_acct_sales_return_details as inv_detail
        LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
        WHERE inv_detail.trn_no = %d GROUP BY inv_detail.id",
        $voucher_no
    );

    erp_disable_mysql_strict_mode();

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Change sales status after returning
 *
 * @since 1.1.0
 *
 * @param int|string $sales_no
 *
 * @return void
 */
function erp_acct_change_sales_status_for_return( $sales_no ) {
    global $wpdb;

    $total_amount  = erp_acct_get_sales_amount_by_voucher( $sales_no );
    $return_amount = erp_acct_get_sales_return_amount_by_voucher( $sales_no );

    $status        = ( (float) $return_amount < (float) abs( $total_amount ) ) ? 10 : 9;

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_invoices',
        [ 'status' => $status ],
        [ 'voucher_no' => $sales_no ]
    );
}

/**
 * Retrieves total return amount of a specific sales invoice
 *
 * @since 1.1.0
 *
 * @param int|string $invoice
 *
 * @return string
 */
function erp_acct_get_sales_return_amount_by_voucher( $invoice ) {
    global $wpdb;

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(amount+tax-discount)
            FROM {$wpdb->prefix}erp_acct_sales_return
            WHERE invoice_id = %d
            GROUP BY invoice_id",
            (int) $invoice
        )
    );
}

/**
 * Retrieves total amount of a specific sales voucher
 *
 * @since 1.1.0
 *
 * @param int|string $voucher_no
 *
 * @return string
 */
function erp_acct_get_sales_amount_by_voucher( $voucher_no ) {
    global $wpdb;

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT (amount + tax - discount)
            FROM {$wpdb->prefix}erp_acct_invoices
            WHERE voucher_no = %d",
            (int) $voucher_no
        )
    );
}

/**
 * Get formatted invoice data
 *
 * @since 1.1.0
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_sales_return_data( $data, $voucher_no ) {
    $invoice_data = [];

    // We can pass the name from view... to reduce DB query load
    if ( empty( $data['customer_name'] ) ) {
        $customer      = erp_get_people( $data['customer_id'] );
        $customer_name = $customer->first_name . ' ' . $customer->last_name;
    } else {
        $customer_name = $data['customer_name'];
    }

    $invoice_data['sales_voucher_no'] = isset( $data['sales_voucher_no'] ) ? $data['sales_voucher_no'] : null;
    $invoice_data['voucher_no']       = ! empty( $voucher_no ) ? $voucher_no : 0;
    $invoice_data['customer_id']      = isset( $data['customer_id'] ) ? $data['customer_id'] : null;
    $invoice_data['customer_name']    = $customer_name;
    $invoice_data['return_date']      = isset( $data['return_date'] ) ? $data['return_date'] : date( 'Y-m-d' );
    $invoice_data['amount']           = isset( $data['amount'] ) ? $data['amount'] : 0;
    $invoice_data['discount']         = isset( $data['discount'] ) ? $data['discount'] : 0;
    $invoice_data['discount_type']    = isset( $data['discount_type'] ) ? $data['discount_type'] : null;
    $invoice_data['tax_rate_id']      = isset( $data['tax_rate_id'] ) ? $data['tax_rate_id'] : 0;
    $invoice_data['line_items']       = isset( $data['line_items'] ) ? $data['line_items'] : [];
    $invoice_data['tax']              = isset( $data['tax'] ) ? $data['tax'] : 0;
    $invoice_data['shipping']         = isset( $data['shipping'] ) ? $data['shipping'] : 0;
    $invoice_data['shipping_tax']     = isset( $data['shipping_tax'] ) ? $data['shipping_tax'] : 0;
    $invoice_data['attachments']      = ! empty( $data['attachments'] ) ? $data['attachments'] : '';
    $invoice_data['status']           = 9;
    $invoice_data['return_reason']    = ! empty( $data['return_reason'] ) ? $data['return_reason'] : '';
    $invoice_data['comments']         = sprintf( __( 'Invoice created with voucher no %s', 'erp-pro' ), $voucher_no );
    $invoice_data['created_at']       = isset( $data['created_at'] ) ? $data['created_at'] : null;
    $invoice_data['created_by']       = isset( $data['created_by'] ) ? $data['created_by'] : null;
    $invoice_data['updated_at']       = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $invoice_data['updated_by']       = isset( $data['updated_by'] ) ? $data['updated_by'] : null;

    return $invoice_data;
}
