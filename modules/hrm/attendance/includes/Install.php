<?php

namespace WeDevs\Attendance;

/**
 * Installation related functions and actions
 *
 * @since 1.1.0
 */
class Install {

	/**
	 * Class constructor
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_attendance', array( $this, 'activate' ) );
	}

	/**
	 * Plugin activation hook
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function activate() {
		$current_version = get_option( 'erp-attendance-version', null );

		// create table
		$this->create_table();

		// does it needs any update?
		if ( ! class_exists( '\WeDevs\Attendance\Updates' ) ) {
			include_once WPERP_ATTEND_INCLUDES . '/class-updates.php';
		}

		$updater = new Updates();
		$updater->perform_updates();
        $this->populate_email_contents();
		// update to latest version
		update_option( 'erp-attendance-version', WPERP_ATTEND_VERSION );
	}

	/**
	 * Create plugin tables
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	private function create_table() {
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
				  `updated_at` timestamp NULL,
				  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB $collate;",

		];

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach ( $table_schema as $table ) {

			dbDelta( $table );
		}
	}


	private function populate_email_contents() {
		$att_reminder = [
			'subject' => 'Attendance Reminder for {date}',
			'heading' => 'Attendance Reminder for {date}',
			'body'    => '
Hi {employee_name},
This is a gentle reminder that you did not check-in today using the Online Attendance System.
Please note that if you do not check-in, your work hour for {date} will be counted as ‘0’ (Zero).
So, we would suggest you to perform the check-in procedure right away to avoid unwanted circumstances.

Thanks.'
		];

		if ( empty( get_option( 'erp_email_settings_attendance' ) ) ) {
			update_option( 'erp_email_settings_attendance-reminder', $att_reminder );
		}
	}

}
