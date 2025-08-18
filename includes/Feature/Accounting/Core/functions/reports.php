<?php

/**
 * Generates sales return report
 * 
 * @since 1.2.3
 *
 * @param array $args
 * 
 * @return array
 */
function erp_acct_get_sales_return_report( $args = [] ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = erp_current_datetime()->modify( 'first day of january' )->format( 'Y-m-d' );
    } else {
        $closest_fy_date    = erp_acct_get_closest_fn_year_date( $args['start_date'] );
        $args['start_date'] = $closest_fy_date['start_date'];
    }

    if ( empty( $args['end_date'] ) ) {
        $args['end_date']   = erp_current_datetime()->modify( 'last day of this month' )->format( 'Y-m-d' );
    }

    $sql = "SELECT 
                refund.voucher_no,
                refund.trn_date,
                refund.customer_name,
                refund_details.tax,
                refund_details.discount,
                refund_details.item_total as price,
                product.name as product,
                refund_details.qty as qty
            FROM {$wpdb->prefix}erp_acct_sales_return_details as refund_details
            LEFT JOIN {$wpdb->prefix}erp_acct_sales_return as refund
            ON refund_details.trn_no = refund.voucher_no
            LEFT JOIN {$wpdb->prefix}erp_acct_products as product
            ON refund_details.product_id = product.id
            WHERE refund.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";

    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results;
}

/**
 * Generates purchase return report
 * 
 * @since 1.2.3
 *
 * @param array $args
 * 
 * @return array
 */
function erp_acct_get_purchase_retuen_report( $args = [] ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = erp_current_datetime()->modify( 'first day of january' )->format( 'Y-m-d' );
    } else {
        $closest_fy_date    = erp_acct_get_closest_fn_year_date( $args['start_date'] );
        $args['start_date'] = $closest_fy_date['start_date'];
    }

    if ( empty( $args['end_date'] ) ) {
        $args['end_date']   = erp_current_datetime()->modify( 'last day of this month' )->format( 'Y-m-d' );
    }

    $sql = "SELECT
                refund.voucher_no,
                refund.trn_date,
                refund.vendor_name,
                refund_details.tax as vat,
                refund_details.discount,
                refund_details.price as price,
                product.name as product,
                refund_details.qty as qty
            FROM {$wpdb->prefix}erp_acct_purchase_return_details as refund_details
            LEFT JOIN {$wpdb->prefix}erp_acct_purchase_return as refund
            ON refund_details.trn_no = refund.voucher_no
            LEFT JOIN {$wpdb->prefix}erp_acct_products as product
            ON refund_details.product_id = product.id
            WHERE refund.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";

    $results = $wpdb->get_results( $sql, ARRAY_A );

    array_walk( $results, function( &$result ) {
        $result['price'] = ( (double) $result['price'] * (double) $result['qty'] ) - (double) $result['discount'] - (double) $result['tax'];
    } );

    return $results;
}

/**
 * Generates purchase vat report
 * 
 * @since 1.2.3
 *
 * @param array $args
 * 
 * @return array
 */
function erp_acct_get_purchase_vat_report( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) || empty( $args['end_date'] ) ) {
        return [];
    }

    $sql['from']  = "{$wpdb->prefix}erp_acct_purchase AS purchase";
    $sql['where'] = "purchase.trn_date BETWEEN '%s' AND '%s'";
    $sql['extra'] = '';
    $values       = [ $args['start_date'], $args['end_date'] ];

    if ( ! empty( $args['vendor_id'] ) ) {

        $sql['select'] = 'purchase.trn_date, purchase.voucher_no, purchase.tax AS tax_amount, purchase.vendor_id, purchase.vendor_name';
        $sql['where'] .= " AND purchase.tax > 0 AND purchase.vendor_id = %d";
        $values[]      = $args['vendor_id'];

    } else if ( ! empty( $args['category_id'] ) ) {

        $sql['select'] = 'purchase.trn_date, purchase.voucher_no, sum(details.tax) AS tax_amount, details.tax_cat_id';
        $sql['from']  .= " RIGHT JOIN {$wpdb->prefix}erp_acct_purchase_details AS details ON purchase.voucher_no = details.trn_no";
        $sql['where'] .= " AND details.tax > 0 AND details.tax_cat_id = %d";
        $sql['extra'] .= "GROUP BY details.trn_no";
        $values[]      = $args['category_id'];

    } else if ( ! empty( $args['agency_id'] ) ) {

        $sql['select'] = 'purchase.trn_date, purchase.voucher_no, sum(details.tax) AS tax_amount, tax.agency_id';
        $sql['from']  .= " RIGHT JOIN {$wpdb->prefix}erp_acct_purchase_details AS details ON purchase.voucher_no = details.trn_no";
        $sql['from']  .= " INNER JOIN {$wpdb->prefix}erp_acct_purchase_details_tax AS tax ON details.id = tax.invoice_details_id";
        $sql['where'] .= " AND details.tax > 0 AND tax.agency_id = %d";
        $sql['extra'] .= "GROUP BY details.trn_no";
        $values[]      = $args['agency_id'];

    } else {

        $sql['select'] = 'purchase.trn_date, purchase.voucher_no, purchase.tax AS tax_amount';
        $sql['where'] .= " AND purchase.tax > 0";

    }

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT {$sql['select']} FROM {$sql['from']} WHERE {$sql['where']} {$sql['extra']}",
            $values
        ),
        ARRAY_A
    );
}

/**
 * get sales tax report
 *
 * @param int    $agency_id
 * @param string $start_date
 * @param string $end_date
 *
 * @return mixed
 */
function erp_acct_get_purchase_vat_report_agency( $agency_id, $start_date, $end_date ) {
    global $wpdb;

    // opening balance
    $sql1 = $wpdb->prepare(
        "SELECT SUM(debit - credit) AS opening_balance
        FROM {$wpdb->prefix}erp_acct_tax_agency_details
        WHERE agency_id = %d AND trn_date < '%s'",
        $agency_id,
        $start_date
    );

    $db_opening_balance = $wpdb->get_var( $sql1 );
    $opening_balance    = (float) $db_opening_balance;

    // agency details
    $details = $wpdb->get_results( $wpdb->prepare( "SELECT trn_no, particulars, debit, credit, trn_date, created_at FROM {$wpdb->prefix}erp_acct_tax_agency_details WHERE agency_id = %d AND trn_date BETWEEN '%s' AND '%s'", $agency_id, $start_date, $end_date ), ARRAY_A );

    $total_debit  = 0;
    $total_credit = 0;

    // Please refactor me
    foreach ( $details as $key => $detail ) {
        $total_debit += (float) $detail['debit'];
        $total_credit += (float) $detail['credit'];

        if ( '0.00' === $detail['debit'] ) {
            // so we're working with credit
            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance            = $opening_balance + ( - (float) $detail['credit'] );
                $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + ( - (float) $detail['credit'] );

                // after calculation with credit
                if ( $opening_balance >= 0 ) {
                    $details[ $key ]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
                }
            } else {
                // opening balance is 0
                $details[ $key ]['balance'] = '0 Dr';
            }
        }

        if ( '0.00' === $detail['credit'] ) {
            // so we're working with debit

            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance            = $opening_balance + (float) $detail['debit'];
                $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (float) $detail['debit'];

                // after calculation with debit
                if ( $opening_balance >= 0 ) {
                    $details[ $key ]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
                }
            } else {
                // opening balance is 0
                $details[ $key ]['balance'] = '0 Dr';
            }
        }
    }

    // Assign opening balance as first row
    if ( (float) $db_opening_balance > 0 ) {
        $balance = $db_opening_balance . ' Dr';
    } elseif ( (float) $db_opening_balance < 0 ) {
        $balance = abs( $db_opening_balance ) . ' Cr';
    } else {
        $balance = '0 Dr';
    }

    array_unshift(
        $details,
        [
            'trn_no'      => null,
            'particulars' => 'Opening Balance =',
            'debit'       => null,
            'credit'      => null,
            'trn_date'    => $start_date,
            'balance'     => $balance,
            'created_at'  => null,
        ]
    );

    return [
        'details' => $details,
        'extra'   => [
            'total_debit'  => $total_debit,
            'total_credit' => $total_credit,
        ],
    ];
}