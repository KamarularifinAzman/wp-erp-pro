<?php
namespace WeDevs\ERP_PRO\Updates;

/**
 * Create necessry tables for purchase return
 */
function erp_acct_create_tables_1_1_0() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_schema = [
        "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_return (
            id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_id int(20) NOT NULL,
            voucher_no int(20) NOT NULL,
            vendor_id int(20) DEFAULT NULL,
            vendor_name varchar(255) DEFAULT NULL,
            trn_date date  NOT NULL,
            amount decimal(20,2) NOT NULL,
            discount decimal(20,2) DEFAULT 0,
            discount_type varchar(255) DEFAULT NULL,
            tax decimal(20,2) DEFAULT 0,
            reason text DEFAULT NULL,
            comments text DEFAULT NULL,
            status int(20) DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            created_by int(20) DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            updated_by int(20) DEFAULT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_return_details (
            id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_details_id int(20) NOT NULL,
            trn_no int(20) NOT NULL,
            product_id int(20) NOT NULL,
            qty int(20) NOT NULL,
            price decimal(20,2) NOT NULL,
            discount decimal(20,2) DEFAULT 0,
            tax decimal(20,2) DEFAULT 0,
            created_at datetime DEFAULT NULL,
            created_by int(20) DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            updated_by int(20) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}erp_acct_sales_return (
            id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_id int(20) NOT NULL,
            voucher_no int(20) NOT NULL,
            customer_id int(20) DEFAULT NULL,
            customer_name varchar(255) DEFAULT NULL,
            trn_date date  NOT NULL,
            amount decimal(20,2) NOT NULL,
            discount decimal(20,2) DEFAULT 0,
            discount_type varchar(255) DEFAULT NULL,
            tax decimal(20,2) DEFAULT 0,
            reason text DEFAULT NULL,
            comments text DEFAULT NULL,
            status int(20) DEFAULT NULL COMMENT '0 means drafted, 1 means confirmed return',
            created_at datetime DEFAULT NULL,
            created_by int(20) DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            updated_by int(20) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate",

        "CREATE TABLE {$wpdb->prefix}erp_acct_sales_return_details (
            id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_details_id int(20) NOT NULL,
            trn_no int(20) NOT NULL,
            product_id int(20) NOT NULL,
            qty int(20) NOT NULL,
            unit_price decimal(20,2) NOT NULL,
            discount decimal(20,2) DEFAULT 0,
            tax decimal(20,2) DEFAULT 0,
            item_total decimal(20,2) NOT NULL,
            ecommerce_type varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            created_by int(20) DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            updated_by int(20) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate",
    ];

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}

erp_acct_create_tables_1_1_0();
