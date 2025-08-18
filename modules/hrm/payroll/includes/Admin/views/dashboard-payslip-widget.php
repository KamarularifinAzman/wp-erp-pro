<?php
$year  = ( isset( $_POST['year'] ) && ! empty( $_POST['year'] ) ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : date( 'Y' );
$month = ( isset( $_POST['month'] ) && ! empty( $_POST['month'] ) ) ? sanitize_text_field( wp_unslash( $_POST['month'] ) ) : date( 'm' );
$payslip_data = get_employee_payslip_by_year_month( $year, $month, get_current_user_id(), true, 0, 5 );
?>
<div class="dashboard-payslip-widget">
    <ul class="erp-list list-two-side list-sep">
    <?php if ( count( $payslip_data ) > 0 ) {
        $limit = 5;
        foreach ( $payslip_data as $psd ) { ?>
            <li>
                <a onclick="get_preview(<?php echo $psd->payrun_id; ?>, <?php echo get_current_user_id(); ?>)" href="javascript:void(0)"> <?php echo date("F Y", strtotime( $psd->payment_date ) ) ;?> </a>
                <span><?php echo $psd->payment_date ;?></span>
            </li>
        <?php }
    } else { ?>
            <li> <?php esc_html_e( 'Sorry! There is no available payslip right now.', 'erp-pro' ) ;?> </li>
        <?php
    }
    ?>
    </ul>
</div>
<script type="text/html" id="payslip_preview">
    <div id="pay-run-wrapper-payslips-tab_emp" class="wrap erp-payroll-payslips erp-payroll-steps">
        <div class="postbox metabox-holder payslip-postbox">
            <div class="inside">
                <div id="printPayslipArea" class="row">
                    <h2 class="erp-pay-slip-label" style="text-align: center"><?php _e( 'Payslip', 'erp-pro' ) ?></h2>
                    <div class="erp-payslip-header">
                        <h2 id="company_name"><?php _e( 'Loading...', 'erp-pro' ) ?></h2>
                    </div>

                    <address class="address"  id="company_address">
                        <?php _e( 'Loading...', 'erp-pro' ) ?>
                    </address>
                    <br>
                    <b><?php _e( 'Employee Name', 'erp-pro' ) ?></b>
                    <br>
                    <span id="emp_name"><?php _e( 'Loading...', 'erp-pro' ) ?></span>
                    <br>
                    <b><?php _e( 'Employee Address', 'erp-pro' ) ?></b>
                    <address class="address" id="emp_address">
                        <?php _e( 'Loading...', 'erp-pro' ) ?>
                    </address>
                    <div class="inner-row">
                        <span class="draft-text"></span>
                        <ul class="payslip-list">
                            <li>
                                <label><b><?php _e( 'Department', 'erp-pro' ); ?></b></label>
                                <label><b><?php _e( 'Designation', 'erp-pro' ); ?></b></label>
                                <label><b><?php _e( 'Period', 'erp-pro' ); ?></b></label>
                            </li>
                            <li>
                                <label id="emp_dept"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                <label id="emp_desig"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                <label id="emp_period"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                            </li>
                        </ul>
                        <ul class="payslip-list">
                            <li>
                                <label><b><?php _e( 'Payment Date', 'erp-pro' ); ?></b></label>
                                <label><b><?php _e( 'Tax Number', 'erp-pro' ); ?></b></label>
                                <label><b><?php _e( 'Bank Account Number', 'erp-pro' ); ?></b></label>
                                <label><b><?php _e( 'Payment Method', 'erp-pro' ); ?></b></label>
                            </li>
                            <li>
                                <label id="emp_payment_date"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                <label id="emp_tax_number"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                <label id="emp_bank_acc_number"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                <label id="emp_payment_method"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                            </li>
                        </ul>
                    </div>

                    <div class="inner-row">
                        <div class="half-left-row" style="clear: left">
                            <ul class="paylist">
                                <li>
                                    <label><b><?php _e( 'Payments', 'erp-pro' ); ?></b></label>
                                </li>
                                <li>
                                    <label class="text-alignleft"><?php _e( 'Pay Basic', 'erp-pro' ); ?></label>
                                    <label class="text-alignright" id="emp_basic_pay"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                </li>
                                <li id="emp_added_payrun">
                                    <label class="text-alignleft"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                    <label class="text-alignright"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                </li>
                            </ul>

                        </div>
                        <div class="half-right-row">
                            <ul class="paylist">
                                <li>
                                    <label><b><?php _e( 'Deductions', 'erp-pro' ); ?></b></label>
                                </li>
                                <li id="emp_deducted_payrun">
                                    <label class="text-alignleft"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                    <label class="text-alignright"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                </li>
                                <li class="final-total-row">
                                    <label class="text-alignleft"><?php _e( 'Total Deduction', 'erp-paryoll' ); ?></label>
                                    <label id="total_deduction" class="text-alignright"><?php _e( 'Loading...', 'erp-pro' ) ?></label>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="inner-row">
                        <div class="half-left-row" style="clear: left">
                            <ul class="paylist paylist-final-amount">
                                <li>
                                    <label class="text-alignleft"><b><?php _e( 'Total Payment', 'erp-paryoll' ); ?></b></label>
                                    <label id="total_payment" class="text-alignright"><b><?php _e( 'Loading...', 'erp-pro' ) ?></b></label>
                                </li>
                            </ul>
                        </div>
                        <div class="half-right-row">
                            <ul class="paylist paylist-final-amount">
                                <li>
                                    <label class="text-alignleft"><b><?php _e( 'Net Pay', 'erp-pro' ); ?></b></label>
                                    <label id="total_net_payment" class="text-alignright"><b><?php _e( 'Loading...', 'erp-pro' ) ?></b></label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
<style>
    #view_employee_payslip_modal span.activate {
        display: none;
    }
</style>
