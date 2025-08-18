<?php

function alter_table() {
    global $wpdb;
    $wpdb->get_results( "ALTER TABLE {$wpdb->prefix}erp_employee_dir_file_relationship ADD eid_type VARCHAR(20) NOT NULL DEFAULT 'employee' AFTER is_dir" );
}

alter_table();