<?php
/**
 * Version 1.1.0 updated
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_att_update_1_1_0() {
    global $wpdb;

    // check if erp_attendance table exists
    $query = "select count(*) from information_schema.tables where table_schema = '{$wpdb->dbname}' and table_name = '{$wpdb->prefix}erp_attendance'";

    if ( $wpdb->get_var( $query ) ) {
        $attendance_tbl      = $wpdb->prefix . 'erp_attendance';
        $attendance_tbl_cols = $wpdb->get_col( "DESC " . $attendance_tbl );

        if ( ! empty( $attendance_tbl_cols ) && ! in_array( 'shift_title' , $attendance_tbl_cols ) ) {
            add_filter( 'erp-attendance-updates-wpdb-query', function( $queries ) {

                $queries[] = "ALTER TABLE  `" . WPERP_ATTEND_DB_PREFIX . "erp_attendance`"
                           . " ADD  `shift_title` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `date` ,"
                           . " ADD  `shift_start_time` TIME NOT NULL AFTER  `shift_title` ,"
                           . " ADD  `shift_end_time` TIME NOT NULL AFTER  `shift_start_time`;";

                return $queries;
            } );

            add_action( 'erp-attendance-updates-end', 'erp_att_update_1_1_0_table_data' );
        }
    }

}

erp_att_update_1_1_0();

/**
 * Insert table data for new columns
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_att_update_1_1_0_table_data() {
    global $wpdb;

    $shift_title = __( 'Office Shift', 'erp-pro' );
    $start_time = erp_get_option( 'office_starts', '', '10:00' );
    $end_time = erp_get_option( 'office_ends', '', '18:00' );

    $query = "UPDATE {$wpdb->prefix}erp_attendance"
           . " SET shift_title = '{$shift_title}',"
           . "     shift_start_time = '{$start_time}',"
           . "     shift_end_time = '{$end_time}'"
           . " WHERE shift_title IS NULL"
           . "     AND shift_start_time = '00:00:00'"
           . "     AND shift_end_time = '00:00:00'";

    $wpdb->query( $query );
}
