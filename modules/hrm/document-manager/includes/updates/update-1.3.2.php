<?php

function erp_doc_manager_update_1_3_2() {
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty( $wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $collate .= " COLLATE $wpdb->collate";
        }
    }

    $table_schema = [

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_dir_file_share (
            	  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                  `owner_id` int(11) NOT NULL,
                  `shared_with_id` int(11) NOT NULL,
                  `dir_file_id` varchar(100) NOT NULL,
                  `source` varchar(50) NOT NULL,
                  `eid_type` varchar(50) NOT NULL,
                  `details` longtext NOT NULL,
                  `created_at` datetime NOT NULL,
                  `updated_at` datetime NOT NULL,
                   UNIQUE KEY `owner_id` (`owner_id`,`shared_with_id`,`dir_file_id`) 
            ) $collate;"

    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    foreach ( $table_schema as $table ) {

        dbDelta( $table );
    }
}

erp_doc_manager_update_1_3_2();