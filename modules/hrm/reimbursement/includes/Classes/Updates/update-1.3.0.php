<?php

function erp_reimb_get_old_transactions_id() {
    global $wpdb;
    global $bg_process_reimb;

    $table_name = "{$wpdb->prefix}erp_ac_transactions";

    $table_exists = $wpdb->get_var(
        $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
    );

    if ( ! $table_exists ) return;

    $requests = $wpdb->get_results(
        "SELECT id FROM $table_name WHERE form_type = 'reimbur_invoice'"
    );

    if ( empty( $requests ) ) {
        return;
    }

    foreach ( $requests as $request ) {
        $bg_process_reimb->push_to_queue( $request->id );
    }

    $bg_process_reimb->save()->dispatch();
}

/**
 * Version 1.3.0 updated
 *
 * @return void
 */
function erp_reimb_update_1_3_0() {
    erp_reimb_get_old_transactions_id();
}

erp_reimb_update_1_3_0();