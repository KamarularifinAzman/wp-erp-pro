<?php
namespace WeDevs\DocumentManager;
/**
 * Installer Class
 *
 * @package ERP
 */
class DocumentInstaller {

    /**
     * Binding all events
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_document_manager', array( $this, 'activate_doc_now' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_document_manager', array( $this, 'deactivate' ) );
    }

    /**
     * Placeholder for activation function
     * Nothing being called here yet.
     *
     * @since 0.1
     *
     * @return 0.1
     */
    public function activate_doc_now() {
        $this->create_doc_tables();
        // update to latest version
        update_option( 'erp-document-manager-version', WPERP_DOC );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Create necessary table for ERP & HRM
     *
     * @since 0.1
     *
     * @return  void
     */
    public function create_doc_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = [

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_employee_dir_file_relationship` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `eid` int(11) unsigned NOT NULL,
                 `dir_id` int(11) unsigned NOT NULL,
                 `dir_name` varchar(255) DEFAULT '',
                 `attachment_id` int(11) unsigned NOT NULL,
                 `parent_id` int(11) unsigned NOT NULL,
                 `is_dir` tinyint(1) unsigned NOT NULL,
                 `eid_type` VARCHAR(20) NOT NULL DEFAULT 'employee',
                 `created_by` int(11) unsigned NOT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `eid` (`eid`)
             ) $collate;",


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

}
