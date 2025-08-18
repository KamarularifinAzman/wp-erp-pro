<?php
namespace WeDevs\ERP_PRO\Updates;

/**
 * Create necessry tables for purchase return
 */
function erp_hr_create_requests_tables_1_2_0() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_schema = [
        "CREATE TABLE {$wpdb->prefix}erp_hr_employee_resign_requests (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            reason varchar(255) DEFAULT NULL,
            `date` date NOT NULL,
            `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            updated_by bigint(20) unsigned DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY `user_id` (`user_id`)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}erp_hr_employee_remote_work_requests (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            reason varchar(255) DEFAULT NULL,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `days` smallint(3) unsigned NOT NULL DEFAULT '0',
            `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            updated_by bigint(20) unsigned DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY `user_id` (`user_id`)
        ) $charset_collate;"
    ];

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}

erp_hr_create_requests_tables_1_2_0();
