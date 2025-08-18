<?php
namespace WeDevs\ERP_PRO\Updates;


function erp_crm_alter_customer_activities_table_1_0_1() {
    global $wpdb;

    // Add done_at columns in `erp_crm_customer_activities` table
    $act_tbl      = $wpdb->prefix . 'erp_crm_customer_activities';
    $act_tbl_cols = $wpdb->get_col( "DESC $act_tbl" );

    if ( ! in_array( 'done_at', $act_tbl_cols ) ) {
        $wpdb->query(
            "ALTER TABLE $act_tbl ADD `done_at` datetime DEFAULT NULL AFTER `sent_notification`;"
        );
    }
}

function erp_crm_create_life_stage_table_1_0_1() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_schema = [
        "CREATE TABLE {$wpdb->prefix}erp_people_life_stages (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            slug varchar(100) DEFAULT NULL,
            title varchar(100) DEFAULT NULL,
            title_plural varchar(100) DEFAULT NULL,
            position smallint(6) unsigned DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;",
    ];

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}

function erp_crm_insert_default_life_stages_1_0_1() {
    global $wpdb;

    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_people_life_stages` LIMIT 0, 1" ) ) {
        $wpdb->query(
            "INSERT INTO `{$wpdb->prefix}erp_people_life_stages` (`id`, `slug`, `title`, `title_plural`, `position`)
            VALUES
                (1, 'customer', 'Customer', 'Customers', 1),
                (2, 'lead', 'Lead', 'Leads', 2),
                (3, 'opportunity', 'Opportunity', 'Opportunities', 3),
                (4, 'subscriber', 'Subscriber', 'Subscribers', 4)"
        );
    }
}

erp_crm_alter_customer_activities_table_1_0_1();
erp_crm_create_life_stage_table_1_0_1();
erp_crm_insert_default_life_stages_1_0_1();
