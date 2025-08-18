<?php
$year  = ( isset( $_POST['year'] ) && ! empty( $_POST['year'] ) ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : date( 'Y' );
$month = ( isset( $_POST['month'] ) && ! empty( $_POST['month'] ) ) ? sanitize_text_field( wp_unslash( $_POST['month'] ) ) : date( 'm' );
$payslip_data = get_employee_payslip_by_year_month( $year, $month );

if ( get_current_user_id() == intval( $_GET['id'] ) || current_user_can( 'erp_hr_manager' ) ) {
?>
<div id="basic-payslip-info-wrapper" class="wrap basic-payroll-info not-loaded payslip">
        <div class="postbox leads-actions">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Detail Information About Payslip', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <div class="inside">
                <div class="payslip_search">
                    <form action="" method="post">
                        <div class="list-inline">
                            <label><strong><?php _e( 'Year', 'erp-pro' ); ?></strong></label> :
                            <select required="required" name="year">
                                <option value="">Select a year</option>
                                <?php
                                $initial_year = 2016;
                                $limit = 11;
                                for ($i = 0; $i < $limit; $i++) {
                                    $current_year = $initial_year + $i;
                                    if ( $current_year == $year  ) {
                                        $y_selected = 'selected="selected"';
                                    } else {
                                        $y_selected = '';
                                    }
                                    echo "<option {$y_selected} value='" . $current_year . "'>" . $current_year . "</option>";
                                }

                                ?>
                            </select>
                        </div>
                        <div class="list-inline">
                            <label><strong><?php _e( 'Month', 'erp-pro' ); ?></strong></label> :
                            <select required="required" name="month">
                                <option value="">Select a month</option>
                                <?php
                                $month_counter = 1;
                                foreach (erp_months_dropdown() as $month_name) {

                                    if ( $month_counter == $month  ) {
                                        $m_selected = 'selected="selected"';
                                    } else {
                                        $m_selected = '';
                                    }
                                    echo "<option {$m_selected} value='" . $month_counter . "'>" . $month_name . "</option>";
                                    $month_counter++;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="list-inline">
                            <button type="submit" class="button button-primary"><?php _e( 'Search', 'erp-pro' ); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="paslip_list">
                <table class="widefat striped">
                    <thead>
                    <tr>
                        <th><?php _e( 'SL', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Month', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Bank A/C', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Net Pay', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Action', 'erp-pro' ); ?></th>
                    </tr>
                    </thead>
                    <tbody id="paslip_list_body">
                    <?php
                    if ( count( $payslip_data ) > 0 ) {
                        $sl = 1;
                        foreach ($payslip_data as $pd) {
                            ?>
                            <tr>
                                <td><?php echo $sl; ?></td>
                                <td><?php echo DateTime::createFromFormat( '!m', $month )->format( 'F' ) . ' ' . $year ; ?></td>
                                <td><?php echo get_user_meta( intval( $_GET['id'] ), 'bank_acc_number', true ); ?></td>
                                <td><?php echo erp_acct_get_currency_symbol() . ($pd->total ); ?> /-</td>
                                <td>
                                    <a onclick="get_preview(<?php echo $pd->payrun_id; ?>, <?php echo intval($_GET['id']); ?>, window.print)"
                                       href="javascript:void(0)" class="payslip_print"><span class="fa fa-print"></span></a>
                                    |
                                    <a onclick="get_preview(<?php echo $pd->payrun_id; ?>, <?php echo intval($_GET['id']); ?>)"
                                       href="javascript:void(0)" class="payslip_preview"><span class="fa fa-eye"></span></a>
                                </td>
                            </tr>
                            <?php
                            $sl++;
                        }
                    } else {
                    ?>
                      <tr>
                          <td style="text-align: center" colspan="4"> <?php _e('Sorry! Currently there is no available data.', 'erp-pro'); ?> </td>
                      </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
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
                                    <label class="text-alignleft"><?php  _e( 'Pay Basic', 'erp-pro' );  ?></label>
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
    @media print {
        * {
            visibility: hidden;
        }
        #printPayslipArea {
            visibility: visible;
            margin:0;
            padding:0;
            position:fixed;
            top : 20%;
            left : 0
        }
    }
</style>
<?php
} else {
?>
    <div id="error-page">
        <div class="wp-die-message"><?php _e( 'Sorry, you are not allowed to access this page.', 'erp-paryoll' ); ?></div>
    </div>
<?php
}
