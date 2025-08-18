<?php
/**
 * Hook into payment methods to add People as amethod
 */
add_filter( 'erp_acct_pay_methods', 'erp_acct_add_reimbursement_as_method' );
function erp_acct_add_reimbursement_as_method( $rows ) {
    $rows[] = [
        'id'   => 4,
        'slug' => 'reimbursement',
        'name' => __( 'Reimbursement', 'erp' )
    ];

    return $rows;
}

/**
 * Hook to send pdf on new transaction
 */
add_action( 'erp_acct_new_transaction_people_trn', 'erp_acct_send_email_on_transaction', 10, 2 );

/**
 * ===================================================
 * People Transaction Helpers
 * ===================================================
 */


add_action( 'erp_acct_after_payment_create', 'erp_acct_rec_payment_people_trns', 10, 2 );

function erp_acct_rec_payment_people_trns( $data, $voucher_no ) {
    global $wpdb;

    if ( isset( $data['trn_by'] ) && $data['trn_by'] !== 4 ) {
        return;
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
        'people_id'    => $data['deposit_to'],
        'trn_no'       => $data['voucher_no'],
        'debit'        => 0,
        'credit'       => $data['amount'],
        'trn_date'     => $data['trn_date'],
        'trn_by'       => $data['trn_by'],
        'particulars'  => $data['particulars'],
        'voucher_type' => $data['voucher_type'],
        'created_at'   => $data['created_at'],
        'created_by'   => $data['created_by'],
        'updated_at'   => $data['updated_at'],
        'updated_by'   => $data['updated_by']
    ) );
}


add_action( 'erp_acct_after_pay_bill_create', 'erp_acct_pay_bill_people_trns', 10, 2 );

function erp_acct_pay_bill_people_trns( $data, $voucher_no ) {
    global $wpdb;

    if ( isset( $data['trn_by'] ) && $data['trn_by'] !== 4 ) {
        return;
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
        'people_id'    => $data['trn_by_ledger_id'],
        'trn_no'       => $data['voucher_no'],
        'debit'        => $data['amount'],
        'credit'       => 0,
        'trn_date'     => $data['trn_date'],
        'trn_by'       => $data['trn_by'],
        'particulars'  => $data['particulars'],
        'voucher_type' => $data['voucher_type'],
        'created_at'   => $data['created_at'],
        'created_by'   => $data['created_by'],
        'updated_at'   => $data['updated_at'],
        'updated_by'   => $data['updated_by']
    ) );
}


add_action( 'erp_acct_after_pay_purchase_create', 'erp_acct_pay_purchase_people_trns', 10, 2 );

function erp_acct_pay_purchase_people_trns( $data, $voucher_no ) {
    global $wpdb;

    if ( isset( $data['trn_by'] ) && $data['trn_by'] !== 4 ) {
        return;
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
        'people_id'    => $data['trn_by_ledger_id'],
        'trn_no'       => $data['voucher_no'],
        'debit'        => $data['amount'],
        'credit'       => 0,
        'trn_date'     => $data['trn_date'],
        'trn_by'       => $data['trn_by'],
        'particulars'  => $data['particulars'],
        'voucher_type' => $data['voucher_type'],
        'created_at'   => $data['created_at'],
        'created_by'   => $data['created_by'],
        'updated_at'   => $data['updated_at'],
        'updated_by'   => $data['updated_by']
    ) );
}


add_action( 'erp_acct_expense_people_transaction', 'erp_acct_expense_people_trns', 10, 2 );

function erp_acct_expense_people_trns( $data, $voucher_no ) {
    global $wpdb;

    if ( isset( $data['trn_by'] ) && $data['trn_by'] !== 4 ) {
        return;
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
        'people_id'    => $data['trn_by_ledger_id'],
        'trn_no'       => $data['voucher_no'],
        'debit'        => 0,
        'credit'       => $data['amount'],
        'trn_date'     => $data['trn_date'],
        'trn_by'       => $data['trn_by'],
        'particulars'  => $data['particulars'],
        'voucher_type' => $data['voucher_type'],
        'created_at'   => $data['created_at'],
        'created_by'   => $data['created_by'],
        'updated_at'   => $data['updated_at'],
        'updated_by'   => $data['updated_by']
    ) );
}

/**
 * Add People transaction to quick menu
 */
add_filter( 'erp_acct_quick_menu', function( $menus ) {
    $menu = [
        "people_transaction" => [
            'title' => __( 'Reimbursement', 'erp-pro' ),
            'slug'  => 'reimbursements',
            'url'   => 'transactions/reimbursements/new',
        ]
    ];

    return array_merge( $menus, $menu );
});

/**
 * ===================================================
 * People Transaction Report
 * ===================================================
 */

/**
 * get people_trn report
 *
 * @param int $people_id
 * @param string $start_date
 * @param string $end_date
 * @return mixed
 */
function erp_acct_get_people_trn_report( $people_id, $start_date, $end_date ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = (float) erp_acct_reimbursement_report_opening_balance_by_fn_year_id( $closest_fy_date['id'], $people_id );

    // should we go further calculation, check the diff
    if ( erp_acct_has_date_diff( $start_date, $closest_fy_date['start_date'] ) ) {
        $prev_date_of_start = date( 'Y-m-d', strtotime( '-1 day', strtotime( $start_date ) ) );

        $sql1 = $wpdb->prepare( "SELECT SUM(debit - credit) AS balance
            FROM {$wpdb->prefix}erp_acct_people_account_details
            WHERE people_id = %d AND trn_date BETWEEN '%s' AND '%s'",
            $people_id, $closest_fy_date['start_date'], $prev_date_of_start
        );

        $prev_people_trn_details = $wpdb->get_var( $sql1 );
        $opening_balance     += (float) $prev_people_trn_details;
    }

    $raw_opening_balance = $opening_balance;

    // people_trn details
    $sql2 = $wpdb->prepare( "SELECT
        trn_no, voucher_type, trn_by, particulars, debit, credit, trn_date, created_at
        FROM {$wpdb->prefix}erp_acct_people_account_details
        WHERE people_id = %d AND trn_date BETWEEN '%s' AND '%s'",
        $people_id, $start_date, $end_date
    );

    $details = $wpdb->get_results( $sql2, ARRAY_A );

    $total_debit  = 0;
    $total_credit = 0;

    // Please refactor me
    foreach ( $details as $key => $detail ) {
        $total_debit  += (float) $detail['debit'];
        $total_credit += (float) $detail['credit'];

        if ( '0.00' === $detail['debit'] ) {
            // so we're working with credit
            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance          = $opening_balance + ( -(float) $detail['credit'] );
                $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + ( -(float) $detail['credit'] );

                // after calculation with credit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';
                }

            } else {
                // opening balance is 0
                $details[$key]['balance'] = '0 Dr';
            }
        }

        if ( '0.00' === $detail['credit'] ) {
            // so we're working with debit
            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance          = $opening_balance + (float) $detail['debit'];
                $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (float) $detail['debit'];

                // after calculation with debit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';
                }

            } else {
                // opening balance is 0
                $details[$key]['balance'] = '0 Dr';
            }
        }
    }

    // Assign opening balance as first row
    if ( (float) $raw_opening_balance > 0 ) {
        $balance = $raw_opening_balance . ' Dr';
    } elseif ( (float) $raw_opening_balance < 0 ) {
        $balance = abs( $raw_opening_balance ) . ' Cr';
    } else {
        $balance = '0 Dr';
    }

    array_unshift( $details, [
        'trn_no'      => null,
        'particulars' => 'Opening Balance =',
        'debit'       => null,
        'credit'      => null,
        'trn_date'    => $start_date,
        'balance'     => $balance,
        'created_at'  => null
    ] );

    return [
        'details' => $details,
        'extra'   => [
            'total_debit'  => $total_debit,
            'total_credit' => $total_credit
        ]
    ];
}


/**
 * People Transaction report opening balance helper
 * @param $id
 * @param $people_id
 * @return string|null
 */
function erp_acct_reimbursement_report_opening_balance_by_fn_year_id( $id, $people_id ) {
    global $wpdb;

    $sql = "SELECT SUM(debit - credit) AS balance FROM {$wpdb->prefix}erp_acct_opening_balances
        WHERE financial_year_id = %d AND ledger_id = %d AND type = 'people' GROUP BY ledger_id";

    return $wpdb->get_var( $wpdb->prepare( $sql, $id, $people_id ) );
}

/**
 * Deregister confilicted scripts
 * @return string|null
 */
function remove_confilicted_scripts() {
    if ( isset( $_GET['section'] ) && isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['section'] ) ) == 'reimbursement' && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'erp-hr' ) {
        wp_deregister_script( 'erp-vuejs' );
        wp_deregister_script( 'erp-recruitment-app-script' );
        wp_deregister_script( 'erp-recruitment-script' );
        wp_deregister_script( 'erp-recruitment-dynamic-field-script' );
        wp_deregister_script( 'erp-recruitment-barrating-script' );
        wp_deregister_script( 'multi-step-form-script' );
        wp_deregister_script( 'alertify-lib' );
        wp_deregister_script( 'erp-document-entry' );
        wp_deregister_script( 'erp-document-upload' );
        wp_deregister_script( 'erp-document' );
    }
}
add_action('admin_footer', 'remove_confilicted_scripts');
