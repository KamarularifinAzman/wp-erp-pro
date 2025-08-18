<?php
/**
 * Payslip
 *
 * @since 1.3.0
 *
 * @return void
 */
function erp_hr_payroll_update_1_3_0() {
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
erp_hr_payroll_update_1_3_0();
