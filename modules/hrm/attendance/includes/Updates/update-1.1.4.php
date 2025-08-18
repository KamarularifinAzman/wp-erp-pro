<?php
/**
 * Version 1.1.4 updated
 *
 * @return void
 */
function erp_att_update_1_1_4() {
	global $wpdb;
	$sql = "select id from {$wpdb->prefix}erp_attendance";
	$result = $wpdb->get_col($sql);


	//checkin checkout shiftstart shiftend


}

erp_att_update_1_1_4();