<?php
namespace WeDevs\Recruitment;

/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP Recruitment
 */

/**
 * Installer Class
 *
 * @package ERP
 */
class Installer {

	/**
	 * Binding all events
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_recruitment', array( $this, 'activate_rec_now' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_recruitment', array( $this, 'deactivate' ) );
	}

	/**
	 * Placeholder for activation function
	 * Nothing being called here yet.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function activate_rec_now() {
		$current_version = get_option( 'erp-recruitment-version', null );

        $this->create_rec_tables();

        $this->prepare_role_caps();

		if ( get_page_by_title( 'Job List Page' ) == null ) {
			// create jobs page and put shortcode
			$jobs_page = array(
				'post_title'   => 'Job List Page',
				'post_content' => '[erp-job-list]',
				'post_status'  => 'publish',
				'post_type'    => 'page'
			);
			$post_id   = wp_insert_post( $jobs_page );
		}

		// does it needs any update?
		if ( ! class_exists( '\WeDevs\Recruitment\Updates' ) ) {
			include_once WPERP_REC_INCLUDES . '/class-updates.php';
		}
		$updater = new \WeDevs\Recruitment\Updates();
		$updater->perform_updates();
        $this->populate_email_contents();
		// update to latest version
		update_option( 'erp-recruitment-version', WPERP_REC_VERSION );
	}

	/**
	 * Placeholder for deactivation function
	 *
	 * Nothing being called here yet.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate() {

	}

	/**
	 * Create necessary table for ERP & HRM
	 *
	 * @since 1.0.0
	 *
	 * @return  void
	 */
	public function create_rec_tables() {
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

		$current_user_id = get_current_user_id();

		$table_schema = [
			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `job_id` int(11) unsigned DEFAULT NULL,
                 `applicant_id` int(11) unsigned DEFAULT NULL,
                 `stage` int(11) DEFAULT 1,
                 `apply_date` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 `exam_detail` text,
                 `added_by` int(11) unsigned DEFAULT 0,
                 `status` tinyint unsigned DEFAULT 0,
                 PRIMARY KEY (`id`),
                 KEY `job_id` (`job_id`),
                 KEY `applicant_id` (`applicant_id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_comment` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `comment` text,
                 `user_id` int(11) unsigned DEFAULT 0,
                 `comment_date` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_rating` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `rating` int(11) unsigned DEFAULT 0,
                 `user_id` int(11) unsigned DEFAULT 0,
                 `rating_date` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_interview` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `interview_type_id` int(11) unsigned DEFAULT NULL,
                 `interview_detail` varchar(255) DEFAULT NULL,
                 `start_date_time` datetime DEFAULT NULL,
                 `duration_minutes` varchar(15) DEFAULT NULL,
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_interviewer_relation` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `interview_id` int(11) unsigned DEFAULT NULL,
                 `interviewer_id` int(11) unsigned DEFAULT NULL,
                 PRIMARY KEY (`id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_todo` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `title` varchar(255) DEFAULT NULL,
                 `deadline_date` datetime DEFAULT NULL,
                 `status` boolean NOT NULL DEFAULT 0,
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_todo_relation` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `todo_id` int(11) unsigned DEFAULT NULL,
                 `assigned_user_id` int(11) unsigned DEFAULT NULL,
                 PRIMARY KEY (`id`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_job_stage_relation` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `jobid` int(11) DEFAULT NULL,
                 `stageid` int(11) DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 KEY `jobid` (`jobid`)
             ) $collate;",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_stage` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `title` varchar(50) DEFAULT NULL,
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 UNIQUE (`title`)
             ) $collate;",

			"INSERT INTO `{$wpdb->prefix}erp_application_stage` (`id`, `title`, `created_by`, `created_at`)
             VALUES (NULL, 'Screening', $current_user_id, NOW()),
                    (NULL, 'Phone Interview', $current_user_id, NOW()),
                    (NULL, 'Face to Face Interview', $current_user_id, NOW()),
                    (NULL, 'Make an Offer', $current_user_id, NOW())
                    ON DUPLICATE KEY UPDATE id=id"
		];

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $table_schema as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Add new role and prepare old roles to use jobs
	 */
	public function prepare_role_caps() {
		add_role( 'erp_recruiter', __( 'Recruiter', 'erp_rec' ), array(
			'manage_recruitment' => true
		) );

		$hr_caps = [
			'edit_job'          => true,
			'read_job'          => true,
			'delete_jobs'       => true,
			'edit_jobs'         => true,
			'edit_others_jobs'  => true,
			'publish_jobs'      => true,
			'read_private_jobs' => true,
			'create_jobs'       => true,
		];

		$hr_manager = get_role( 'erp_hr_manager' );
		foreach ( $hr_caps as $cap_key => $value ) {
			$hr_manager->add_cap( $cap_key, $value );
		}

		$admin_caps     = [
			'edit_job'          => true,
			'read_job'          => true,
			'delete_jobs'       => true,
			'edit_jobs'         => true,
			'edit_others_jobs'  => true,
			'publish_jobs'      => true,
			'read_private_jobs' => true,
			'create_jobs'       => true,
		];
		$administrators = get_role( 'administrator' );
		foreach ( $admin_caps as $cap_key => $value ) {
			$administrators->add_cap( $cap_key, $value );
		}
    }

    /**
     * Pregenerated email contents as default
     */
    private function populate_email_contents() {
        $default_email_content_for_new_job_application = [
            'subject' => 'New job application submitted',
            'heading' => 'New Job Application',
            'body'    => '
                        Hello Hr Manager

                        {applicant_profile_link} has applied for the position of {position_link} at {date}.

                        Thank you'
        ];

        if ( empty( get_option( 'erp_email_settings_new-job-application-submitted' ) ) ) {
            update_option( 'erp_email_settings_new-job-application-submitted', $default_email_content_for_new_job_application );
        }

        $default_email_content_for_new_job_application_to_applicant = [
            'subject' => 'You successfully applied for the job',
            'heading' => 'You successfully applied for the job',
            'body'    => '
                        Hello {applicant_name}

                        Thank you for applying for the position of {position}.We will get back to you soon.

                        Thank you'
        ];

        if ( empty( get_option( 'erp_email_settings_new-job-application-submitted-to-applicant' ) ) ) {
            update_option( 'erp_email_settings_new-job-application-submitted-to-applicant', $default_email_content_for_new_job_application_to_applicant );
        }
    }

}
