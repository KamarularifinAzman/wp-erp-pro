<?php
/**
 * Update job seeker status
 *
 * @since 1.1.0
 *
 * @return void
 */
function update_jobseeker_status() {
    global $wpdb;
    //get job seeker id
    $query = "SELECT applicant_id
              FROM {$wpdb->prefix}erp_application";
    $jobseekerids = $wpdb->get_results( $query, ARRAY_A );

    foreach ( $jobseekerids as $jids ) {
        //first check status row exist or not
        $jid = $jids['applicant_id'];
        $st = erp_people_get_meta( $jid, 'status' );
        if ( count( $st ) === 0 ) {
            erp_people_update_meta( $jid, 'status', 'nostatus' );
        }
    }
}
update_jobseeker_status();