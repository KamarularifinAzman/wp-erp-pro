<?php
$prid           = ( isset( $payrun_id ) ) ? $payrun_id : 0;
$eid            = ( isset( $emp_id ) ) ? $emp_id : 0;
if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
    $currency_symbol = erp_acct_get_currency_symbol();
} else {
    $currency_symbol = erp_ac_get_currency_symbol();
}
$result          = get_payslip_preview_by_specific_year_month( $prid, $eid );
$total_payment   = $result['emp_details']['basic_pay'];
$total_deduction = 0;
?>
<br>
<br>
<br>
<div id="pay-run-wrapper-payslips-tab_emp" class="wrap erp-payroll-payslips erp-payroll-steps" style="width: 100%;margin: 0 auto;">
        <div class="postbox metabox-holder payslip-postbox">
            <div class="inside">
                <div id="printPayslipArea" class="row" style="float:left;width:100%;">
                    <h2 class="erp-pay-slip-label" style="text-align: center"><?php _e( 'Payslip', 'erp-pro' ) ?></h2>
                    <div class="erp-payslip-header">
                        <h2 id="company_name"><?php echo $result['company_name'] ?></h2>
                    </div>

                    <address class="address"  id="company_address">
                        <?php echo $result['company_address'] ?>
                    </address>
                    <br>
                    <b><?php _e( 'Employee Name', 'erp-pro' ) ?></b>
                    <br>
                    <span id="emp_name"><?php echo $result['emp_name'] ?></span>
                    <br>
                    <b><?php _e( 'Employee Address', 'erp-pro' ) ?></b>
                    <address class="address" id="emp_address">
                        <?php echo $result['emp_address'] ?>
                    </address>
                    <div class="inner-row">
                        <span class="draft-text" style="float:left;font-size:21px;color:#c4c4c4;padding-top:10px;padding-bottom:10px;text-transform:uppercase;"></span>
                        <ul class="payslip-list" style="list-style:none;padding-left: 0;">
                            <li style="float:left;width:100%;padding:5px; list-style-type: none;">
                                <label style="float:left;width:25%;"><b><?php _e( 'Department', 'erp-pro' ); ?></b></label>
                                <label style="float:left;width:25%;"><b><?php _e( 'Designation', 'erp-pro' ); ?></b></label>
                                <label style="float:left;width:25%;"><b><?php _e( 'Period', 'erp-pro' ); ?></b></label>
                            </li>
                            <li style="float:left;width:100%;padding:5px; list-style-type: none;">
                                <label style="float:left;width:25%;"><?php echo $result['emp_details']['dept'] ?></label>
                                <label style="float:left;width:25%;"><?php echo $result['emp_details']['desig'] ?></label>
                                <label style="float:left;width:25%;"><?php echo $result['emp_calendar_info']['from_date'] ?> to <?php echo $result['emp_calendar_info']['to_date'] ?></label>
                            </li>
                        </ul>
                        <ul class="payslip-list" style="list-style:none;padding-left: 0;">
                            <li style="float:left;width:100%;padding:5px; list-style-type: none;">
                                <label style="float:left;width:25%;"><b><?php _e( 'Payment Date', 'erp-pro' ); ?></b></label>
                                <label style="float:left;width:25%;"><b><?php _e( 'Tax Number', 'erp-pro' ); ?></b></label>
                                <label style="float:left;width:25%;"><b><?php _e( 'Bank Account Number', 'erp-pro' ); ?></b></label>
                                <label style="float:left;width:25%;"><b><?php _e( 'Payment Method', 'erp-pro' ); ?></b></label>
                            </li>
                            <li style="float:left;width:100%;padding:5px; list-style-type: none;">
                                <label style="float:left;width:25%;"><?php echo $result['emp_calendar_info']['payment_date'] ?></label>
                                <label style="float:left;width:25%;"><?php echo $result['emp_details']['tax_number'] ?></label>
                                <label style="float:left;width:25%;"><?php echo $result['emp_details']['bank_acc_number'] ?></label>
                                <label style="float:left;width:25%;"><?php echo $result['emp_details']['payment_method'] ?></label>
                            </li>
                        </ul>
                    </div>

                    <div class="inner-row">
                        <div class="half-left-row" style="clear: left;float:left;width:48%;">
                            <ul class="paylist" style="list-style: none;padding-left: 0;">
                                <li style=" list-style-type: none;">
                                    <label><b><?php _e( 'Payments', 'erp-pro' ); ?></b></label>
                                </li>
                                <li style=" list-style-type: none;">
                                    <label class="text-alignleft" style="float: left;width: 48%;"><?php _e( 'Pay Basic', 'erp-pro' ); ?></label>
                                    <label class="text-alignright" id="emp_basic_pay" style="float: left;width: 48%;"><?php echo $currency_symbol . $result['emp_details']['basic_pay'] ;?></label>
                                </li>
                                <?php
                                foreach ( $result['emp_added_payrun'] as $emp_added_payrun ) {
                                ?>
                                <li style=" list-style-type: none;">
                                    <label class="text-alignleft" style="float: left;width: 48%;"><?php echo $emp_added_payrun['payitem'] ;?></label>
                                    <label class="text-alignright" style="float: left;width: 48%;"><?php echo $currency_symbol . $emp_added_payrun['pay_item_amount'] ;?></label>
                                </li>
                                <?php
                                    $total_payment += $emp_added_payrun['pay_item_amount'];
                                }
                                ?>
                            </ul>

                        </div>
                        <div class="half-right-row" style="float:left;width:48%;">
                            <ul class="paylist" style="list-style: none;padding-left: 0;">
                                <li style=" list-style-type: none;">
                                    <label><b><?php _e( 'Deductions', 'erp-pro' ); ?></b></label>
                                </li>
                                <?php
                                foreach ( $result['emp_deducted_payrun'] as $emp_deducted_payrun ) {
                                    ?>
                                    <li style=" list-style-type: none;">
                                        <label class="text-alignleft" style="float: left;width: 48%;"><?php echo $emp_deducted_payrun['payitem'] ;?></label>
                                        <label class="text-alignright" style="float: left;width: 48%;"><?php echo $currency_symbol . $emp_deducted_payrun['pay_item_amount'] ;?></label>
                                    </li>
                                    <?php
                                    $total_deduction += $emp_deducted_payrun['pay_item_amount'];
                                }
                                ?>
                                <li class="final-total-row" style=" list-style-type: none;">
                                    <label class="text-alignleft" style="float: left;width: 48%;"><?php _e( 'Total Deduction', 'erp-paryoll' ); ?></label>
                                    <label id="total_deduction" class="text-alignright" style="float: left;width: 48%;"><?php echo $currency_symbol . number_format( $total_deduction, 2 ); ?></label>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="inner-row">
                        <div class="half-left-row" style="clear: left;float:left;width:48%;">
                            <ul class="paylist paylist-final-amount" style="list-style: none;padding-left: 0;">
                                <li style=" list-style-type: none;">
                                    <label class="text-alignleft" style="float: left;width: 48%;"><b><?php _e( 'Total Payment', 'erp-paryoll' ); ?></b></label>
                                    <label id="total_payment" class="text-alignright" style="float: left;width: 48%;"><b><?php echo $currency_symbol . number_format( $total_payment, 2 ); ?></b></label>
                                </li>
                            </ul>
                        </div>
                        <div class="half-right-row" style="float:left;width:48%;">
                            <ul class="paylist paylist-final-amount" style="list-style: none;padding-left: 0;">
                                <li style=" list-style-type: none;">
                                    <label class="text-alignleft" style="float: left;width: 48%;"><b><?php _e( 'Net Pay', 'erp-pro' ); ?></b></label>
                                    <label id="total_net_payment" class="text-alignright" style="float: left;width: 48%;"><b><?php echo $currency_symbol . number_format( ( $total_payment - $total_deduction ), 2 ); ?></b></label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
