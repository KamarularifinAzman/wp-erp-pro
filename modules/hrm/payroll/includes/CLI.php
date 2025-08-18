<?php
namespace WeDevs\Payroll;

class CLI extends \WP_CLI_Command {

    /**
     * Wrapper for CLI colorize api
     *
     * @since 1.0.0
     *
     * @param string $msg
     *
     * @return void
     */
    private function info( $msg ) {
        echo \WP_CLI::colorize( "%c{$msg}%n\n" );
    }

    /**
     * Wrapper for CLI log api
     *
     * @since 1.0.0
     *
     * @param string $msg
     *
     * @return void
     */
    private function log( $msg ) {
        \WP_CLI::log( $msg );
    }

    /**
     * Wrapper for CLI error api
     *
     * @since 1.0.0
     *
     * @param string $msg
     *
     * @return void
     */
    private function error( $msg ) {
        \WP_CLI::error( $msg );
    }

    /**
     * Wrapper for CLI success api
     *
     * @since 1.0.0
     *
     * @param string $msg
     *
     * @return void
     */
    private function success( $msg ) {
        \WP_CLI::success( $msg );
    }

    /**
     * Truncate plugin tables
     *
     * @since 1.0.3
     *
     * @return void
     */
    public function truncate() {
        global $wpdb;

        // truncate table
        $tables = [
            'erp_hr_payroll_pay_calendar',
            'erp_hr_payroll_calendar_type_settings',
            'erp_hr_payroll_pay_calendar_employee',
            'erp_hr_payroll_fixed_payment',
            'erp_hr_payroll_additional_allowance_deduction',
            'erp_hr_payroll_payrun',
            'erp_hr_payroll_payrun_detail',
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table );
        }

        $this->success( __( 'Tables deleted successfully!', 'erp-pro' ) );
    }

}

\WP_CLI::add_command( 'erp payroll', 'WeDevs\Payroll\CLI' );
