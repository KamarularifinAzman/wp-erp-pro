<?php
namespace WeDevs\ERP\WooCommerce\Update;

function start_db_migration_1_3_2() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_schema = [
        "CREATE TABLE {$wpdb->prefix}erp_acct_product_sync_types (
                id int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) DEFAULT NULL,
                slug varchar(100) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;",
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }

    //insert two new rows for woocommerce and system product type
    if ( ! $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}erp_acct_product_sync_types" ) ) {
        $date       = date( 'Y-m-d' );
        $user_id    = get_current_user_id();
        $wpdb->query(
            "INSERT INTO {$wpdb->prefix}erp_acct_product_sync_types (`name`, slug, created_at, created_by) VALUES
            ('System', 'system', '$date', '$user_id'), ('WooCommerce', 'woocommerce', '$date', '$user_id')"
        );
    }

    // get system sync_type_id
    $system_sync_type_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM `{$wpdb->prefix}erp_acct_product_sync_types` WHERE slug = %s",
            array( 'system' )
        )
    );

    $table = $wpdb->prefix . 'erp_acct_products';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'product_sync_type_id', $cols ) ) {
        $wpdb->query(
            "ALTER TABLE $table ADD `product_sync_type_id` INT(11) NOT NULL DEFAULT '$system_sync_type_id'  AFTER `product_type_id`,  ADD   INDEX  `product_sync_type_id` (`product_sync_type_id`);"
        );
    }

    if ( ! in_array( 'synced_product_id', $cols ) ) {
        $wpdb->query(
            "ALTER TABLE $table ADD `synced_product_id` BIGINT(20) NOT NULL DEFAULT '0'  AFTER `product_sync_type_id`;"
        );
    }

}
start_db_migration_1_3_2();
