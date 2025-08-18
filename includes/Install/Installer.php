<?php
namespace WeDevs\ERP_PRO\Install;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * ERP Pro Installer file
 */

class Installer {

    /**
     * Load automatically when class initiate
     *
     * @since 0.0.1
     */
    public function do_install() {
        // add your required files here, this method will be called during
        $this->create_table_schema();
        $this->insert_table_data();

        // update current version
        update_option( 'erp-pro-plugin-version', ERP_PRO_PLUGIN_VERSION );
    }

    /**
     * Use this method to create db tables
     *
     * @since 1.0.1
     * @return void
     */
    public function create_table_schema() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_schema = [
            "CREATE TABLE {$wpdb->prefix}erp_people_life_stages (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                slug varchar(255) DEFAULT NULL,
                title varchar(100) DEFAULT NULL,
                title_plural varchar(100) DEFAULT NULL,
                position smallint(6) unsigned DEFAULT 0,
                PRIMARY KEY  (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_return (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_id int(20) NOT NULL,
                voucher_no int(20) NOT NULL,
                vendor_id int(20) DEFAULT NULL,
                vendor_name varchar(255) DEFAULT NULL,
                trn_date date  NOT NULL,
                amount decimal(20,2) NOT NULL,
                discount decimal(20,2) DEFAULT 0,
                discount_type varchar(255) DEFAULT NULL,
                tax decimal(20,2) DEFAULT 0,
                reason text DEFAULT NULL,
                comments text DEFAULT NULL,
                status int(20) DEFAULT NULL COMMENT '0 means drafted, 1 means confirmed return',
                created_at datetime DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (id)
              ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_return_details (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_details_id int(20) NOT NULL,
                trn_no int(20) NOT NULL,
                product_id int(20) NOT NULL,
                qty decimal(10,2) NOT NULL,
                price decimal(20,2) NOT NULL,
                discount decimal(20,2) DEFAULT 0,
                tax decimal(20,2) DEFAULT 0,
                created_at datetime DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_sales_return (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_id int(20) NOT NULL,
                voucher_no int(20) NOT NULL,
                customer_id int(20) DEFAULT NULL,
                customer_name varchar(255) DEFAULT NULL,
                trn_date date  NOT NULL,
                amount decimal(20,2) NOT NULL,
                discount decimal(20,2) DEFAULT 0,
                discount_type varchar(255) DEFAULT NULL,
                tax decimal(20,2) DEFAULT 0,
                reason text DEFAULT NULL,
                comments text DEFAULT NULL,
                status int(20) DEFAULT NULL COMMENT '0 means drafted, 1 means confirmed return',
                created_at datetime DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate",

            "CREATE TABLE {$wpdb->prefix}erp_acct_sales_return_details (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_details_id int(20) NOT NULL,
                trn_no int(20) NOT NULL,
                product_id int(20) NOT NULL,
                qty decimal(10,2) NOT NULL,
                unit_price decimal(20,2) NOT NULL,
                discount decimal(20,2) DEFAULT 0,
                tax decimal(20,2) DEFAULT 0,
                item_total decimal(20,2) NOT NULL,
                ecommerce_type varchar(255) DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate",

            "CREATE TABLE {$wpdb->prefix}erp_hr_employee_resign_requests (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                reason varchar(255) DEFAULT NULL,
                `date` date NOT NULL,
                `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by bigint(20) unsigned DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY `user_id` (`user_id`)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_employee_remote_work_requests (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                reason varchar(255) DEFAULT NULL,
                `start_date` date NOT NULL,
                `end_date` date NOT NULL,
                `days` smallint(3) unsigned NOT NULL DEFAULT '0',
                `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by bigint(20) unsigned DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY `user_id` (`user_id`)
            ) $charset_collate;",
        ];

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }

    /**
     * Use this method to create db table data
     *
     * @since 1.0.1
     * @return void
     */
    public function insert_table_data() {
        global $wpdb;

        // insert default life stages for people
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_people_life_stages` LIMIT 0, 1" ) ) {
            $wpdb->query(
                "INSERT INTO `{$wpdb->prefix}erp_people_life_stages` (`id`, `slug`, `title`, `title_plural`, `position`)
                VALUES
                    (1, 'customer', 'Customer', 'Customers', 1),
                    (2, 'lead', 'Lead', 'Leads', 2),
                    (3, 'opportunity', 'Opportunity', 'Opportunities', 3),
                    (4, 'subscriber', 'Subscriber', 'Subscribers', 4)"
            );
        }
    }

    /**
     * Maybe Activate modules
     *
     * For the first time activation after installation,
     * activate all pro modules.
     *
     * @since 0.0.1
     *
     * @return void
     * */
    public function maybe_activate_modules() {
        global $wpdb;

        $modules = ! empty( wp_erp_pro()->module ) ? wp_erp_pro()->module : \WeDevs\ERP_PRO\Module::init();

        $has_installed = $wpdb->get_var( $wpdb->prepare(
            "select option_id from {$wpdb->options} where option_name = %s",
            $modules::ACTIVE_MODULES_DB_KEY
        ) );

        if ( $has_installed ) {
            return;
        }

        // install all available modules
        $modules->activate_modules( $modules->get_available_modules( false ) );
    }
}

