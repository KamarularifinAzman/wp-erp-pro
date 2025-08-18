<?php

namespace WeDevs\Attendance\Updates\BP;

class ERP_Att_Migrate_Attendance extends \WP_Background_Process {
	protected $action = 'erp_att_migrate_attendance_v2';

	protected function task( $attendance_id ) {
		global $wpdb;
		$attendance = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}erp_attendance WHERE id ='%s'", $attendance_id ) );
		$shift      = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}erp_attendance_shifts WHERE start_time='%s' AND end_time='%s'", $attendance->shift_start_time, $attendance->shift_end_time ) );
		if ( ! $attendance || ! $shift ) {
			return false;
		}

		erp_attendance_insert_shifting_for_user( $shift->id, $attendance->user_id, $attendance->date, $attendance->date, true );
		erp_attendance_assign_shift( $attendance->user_id, $shift->id, true );
		if ( ! empty( $attendance->checkin ) ) {
			$checkin = strtotime( "$attendance->date $attendance->checkin" );
			erp_attendance_punch( $attendance->user_id, $checkin );
		}

		if ( ! empty( $attendance->checkout ) ) {
			$checkout = strtotime( "$attendance->date $attendance->checkout" );
			erp_attendance_punch( $attendance->user_id, $checkout );
		}
	}


	protected function complete() {
		parent::complete();
	}
}
