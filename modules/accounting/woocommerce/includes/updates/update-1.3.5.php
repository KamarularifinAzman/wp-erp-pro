<?php
namespace WeDevs\ERP\WooCommerce\Update;

/*
 * Modify slug and name columns in `erp_acct_product_sync_types` table
 */
function erp_acct_alter_table_product_sync_types_1_3_5() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC {$wpdb->prefix}erp_acct_product_sync_types" );

    if ( is_wp_error( $cols ) || empty( $cols ) ) {
        return;
    }

    if ( in_array( 'name', $cols ) ) {
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}erp_acct_product_sync_types` MODIFY `name` VARCHAR(100);" );
    }

    if ( in_array( 'slug', $cols ) ) {
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}erp_acct_product_sync_types` MODIFY `slug` VARCHAR(100);" );
    }
}

erp_acct_alter_table_product_sync_types_1_3_5();