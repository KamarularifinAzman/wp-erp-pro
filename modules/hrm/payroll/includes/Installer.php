<?php
namespace WeDevs\Payroll;

/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP Payroll
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
        add_action( 'erp_pro_activated_module_payroll', array( $this, 'activate_now' ) );
        add_action( 'erp_pro_deactivated_module_payroll', array( $this, 'deactivate' ) );
    }

    /**
     * Placeholder for activation function
     * Nothing being called here yet.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate_now() {
        global $wpdb;

        $this->create_payroll_tables();
        //check payitem table blank or not, if blank then seed some payitems
        $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem";

        $payitem_rows = count( $wpdb->get_results( $query, ARRAY_A ) );

        if ( $payitem_rows == 0 ) {
            $query = "INSERT INTO `{$wpdb->prefix}erp_hr_payroll_payitem` (`id`, `type`, `payitem`, `pay_item_add_or_deduct`)
                      VALUES (NULL, 'Allowance', 'Travel Allowance', 1),
                            (NULL, 'Allowance', 'Accomodation Allowance', 1),
                            (NULL, 'Allowance', 'City Compensatory Allowance', 1),
                            (NULL, 'Allowance', 'Pay Adjustment', 1),
                            (NULL, 'Allowance', 'OverTime', 1),
                            (NULL, 'Allowance', 'Variable Pay', 1),
                            (NULL, 'Allowance', 'Bonus', 1),
                            (NULL, 'Allowance', 'Holiday Pay', 1),
                            (NULL, 'Allowance', 'Service Charge', 1),
                            (NULL, 'Deduction', 'Provident Fund', 0),
                            (NULL, 'Deduction', 'Loan', 0),
                            (NULL, 'Deduction', 'Advance Pay', 0),
                            (NULL, 'Deduction', 'Advance', 0),
                            (NULL, 'Deduction', 'Miscelleneous Deduction', 0),
                            (NULL, 'Deduction', 'Give as you earn', 0),
                            (NULL, 'Non-Taxable Payments', 'Expenses', 0),
                            (NULL, 'Non-Taxable Payments', 'Redundancy', 0),
                            (NULL, 'Non-Taxable Payments', 'Millage', 0),
                            (NULL, 'Tax', 'Income Tax', 2),
                            (NULL, 'Tax', 'Fedaral Tax', 2),
                            (NULL, 'Tax', 'State Tax', 2)
                            ON DUPLICATE KEY UPDATE id=id";

            $wpdb->query( $query );
        }

        if ( ! class_exists( 'WeDevs\Payroll\Updates' ) ) {
			include_once WPERP_PAYROLL_INCLUDES . '/class-updates.php';
		}

		$updater = new Updates();
        $updater->perform_updates();

        update_option( 'erp-payroll-version', WPERP_PAYROLL_VERSION );

        $payslip = [
            'subject' => 'Employee payslip notification',
            'heading' => 'Employee payslip notification heading',
            'body'    => 'Dear {full_name },
                      Please find your salary slip for current month as attachment.
                      Regards,
                      HR Department'
        ];

        update_option( 'erp_email_settings_payslip-custom', $payslip );
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
    public function create_payroll_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_schema = [
            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_pay_calendar` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pay_calendar_name VARCHAR(64) DEFAULT NULL,
                pay_calendar_type VARCHAR(16) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_calendar_type_settings` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pay_calendar_id INT(11) NOT NULL,
                pay_day INT(1) NOT NULL DEFAULT 0,
                custom_month_day INT(1) NOT NULL DEFAULT 0,
                pay_day_mode INT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_pay_calendar_employee` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pay_calendar_id INT(1) NOT NULL,
                empid BIGINT(20) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_fixed_payment` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pay_item_id INT(11) NOT NULL,
                pay_item_amount DECIMAL(20,2) NOT NULL,
                empid INT(11) NOT NULL,
                pay_item_add_or_deduct INT(1) NOT NULL,
                note VARCHAR(255) NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pay_item_id INT(11) NOT NULL,
                pay_item_amount DECIMAL(20,2) NOT NULL,
                empid INT(11) NOT NULL,
                pay_item_add_or_deduct INT(1) NOT NULL,
                payrun_id INT(11) NOT NULL,
                note VARCHAR(255) NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_payrun` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pay_cal_id INT(11) unsigned NOT NULL,
                payment_date date DEFAULT NULL,
                from_date date DEFAULT NULL,
                to_date date DEFAULT NULL,
                approve_status int(1) unsigned DEFAULT 0,
                jr_tran_id int(11) unsigned DEFAULT 0,
                updated_at timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_payrun_detail` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                payrun_id INT(11) unsigned NOT NULL,
                pay_cal_id INT(11) unsigned NOT NULL,
                payment_date date DEFAULT NULL,
                empid INT(11) unsigned NOT NULL,
                pay_item_id INT(11) NOT NULL,
                pay_item_amount DECIMAL(20,2) NOT NULL,
                pay_item_add_or_deduct INT(1) NOT NULL,
                allowance DECIMAL(20,2) NOT NULL DEFAULT '0',
                deduction DECIMAL(20,2) NOT NULL DEFAULT '0',
                note VARCHAR(255) NULL,
                approve_status int(1) unsigned DEFAULT 0,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_payroll_payitem` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                type VARCHAR(255) NOT NULL,
                payitem VARCHAR(255) NOT NULL,
                pay_item_add_or_deduct INT(1) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",
        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }
}
