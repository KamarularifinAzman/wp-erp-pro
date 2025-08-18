<?php
function erp_att_update_2_O_0_migrate_shifts() {
	global $wpdb;
	$days     = get_option( 'erp_settings_erp-hr' );
    $off_days = [];

    if ( ! $days ) {
        $days = [];
    }

	foreach ( $days as $day_name => $working_hours ) {
		if ( empty( $working_hours ) ) {
			$off_days[] = ucfirst( $day_name );
		}
	}

	$sql    = "select shift_start_time,shift_end_time,shift_title from {$wpdb->prefix}erp_attendance group by shift_start_time,shift_end_time,shift_title order by shift_start_time";
	$shifts = $wpdb->get_results( $sql );

	foreach ( $shifts as $key => $shift ) {
		$title = empty( $shift->shift_title ) ? "untitled Shift {$key}" : esc_html( $shift->shift_title );
		erp_attendance_insert_shift( $shift->shift_start_time, $shift->shift_end_time, $title, $off_days );
	}

}

//erp_att_update_2_O_0_migrate_shifts();


function erp_att_update_2_O_0_migration() {
	global $wpdb;
	$migrator    = new \WeDevs\Attendance\Updates\BP\ERP_Att_Migrate_Attendance();
	$attendances = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_attendance ORDER BY id ASC" );

	foreach ( $attendances as $attendance_id ) {
		$migrator->push_to_queue( $attendance_id );
	}

	$migrator->save()->dispatch();

}

//erp_att_update_2_O_0_migration();

function create_table_after_db_upgrade_2_0_0() {
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
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_attendance_shifts (
               	  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `name` varchar(190) NOT NULL,
				  `start_time` time NOT NULL,
				  `end_time` time NOT NULL,
				  `duration` int(11) unsigned NOT NULL,
				  `holidays` varchar(190) DEFAULT '',
				  `status` tinyint(1) NOT NULL DEFAULT '1',
				  PRIMARY KEY (`id`),
				  KEY `name` (`name`),
				  KEY `start_time` (`start_time`),
				  KEY `end_time` (`end_time`),
				  KEY `duration` (`duration`),
				  KEY `status` (`status`)
            ) $collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_attendance_shift_user (
            	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `shift_id` int(11) NOT NULL,
				  `user_id` int(11) NOT NULL,
				  `status` tinyint(1) NOT NULL DEFAULT '1',
				  PRIMARY KEY (`id`),
				  KEY `user_id` (`shift_id`),
				  KEY `shift_id` (`user_id`),
				  UNIQUE KEY `unique_index` (`user_id`,`shift_id`)
            ) $collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_attendance_date_shift (
         		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `date` date DEFAULT NULL,
				  `user_id` int(11) NOT NULL,
				  `shift_id` int(11) NOT NULL,
				  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `present` tinyint(4) DEFAULT NULL,
				  `late` int(11) DEFAULT NULL,
				  `early_left` int(11) DEFAULT NULL,
				  `day_type` varchar(250) NOT NULL DEFAULT 'working_day',
				  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `unique_index` (`date`,`user_id`),
				  KEY `date` (`date`),
				  KEY `shift_id` (`shift_id`),
				  KEY `start_time` (`start_time`),
				  KEY `end_time` (`end_time`)
            ) $collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_attendance_log (
         		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `user_id` bigint(20) NOT NULL,
				  `date_shift_id` int(11) DEFAULT NULL,
				  `checkin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `checkout` timestamp NULL DEFAULT NULL,
				  `time` INT(11),
				  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `user_id` (`user_id`),
				  KEY `checkin` (`checkin`),
				  KEY `checkout` (`checkout`),
				  KEY `time` (`time`)
            ) ENGINE=InnoDB $collate;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_attendence_shift_generated_to (
         		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `start_date` date NULL default NULL,
				  `end_date` date NULL default NULL,
				  `generated_by` bigint(20) NULL default NULL,
				  `shift_id` bigint(20) NULL default NULL,
				  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB $collate;",

		];

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach ( $table_schema as $table ) {

			dbDelta( $table );
		}
}

create_table_after_db_upgrade_2_0_0();
