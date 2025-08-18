<?php
if ( isset($_GET['prid']) ) {
    $payrunid = $_GET['prid'];
} else {
    wp_redirect( erp_payroll_get_admin_link('calendar') );
    exit;
}
?>
<div id="pay-run-wrapper-payslips-tab" class="wrap erp-payroll-payslips erp-payroll-steps not-loaded">
    <h1>
        <?php _e( 'Pay Run', 'erp-pro' ); ?>&#45;{{ cal_info[0] ? cal_info[0].pay_calendar_name : '' }}&nbsp;&#40;{{ cal_info[0] ? cal_info[0].pay_calendar_type : '' }}&#41;
        <span class="alignright cal_status" :class="[ approve_status == 'Not Approved' ? 'cal_status_not_approve' : 'cal_status_approved']">
            {{ approve_status }}
        </span>
    </h1>
    <?php echo erp_payroll_payrun_tab( 'payslips', $payrunid ); ?>

    <div class="postbox metabox-holder payslip-postbox">
        <h3 class="openingform_header_title hndle"><?php _e( 'Employee Payslip', 'erp-pro' ); ?></h3>
        <div class="inside">
            <?php
            $back_activity_url = erp_payroll_get_admin_link( 'payrun', [ 'tab'  => 'variable_input', 'prid' => $payrunid ] );

            $next_activity_url = erp_payroll_get_admin_link( 'payrun', [ 'tab'  => 'approve', 'prid' => $payrunid ] );
            ?>

            <div class="row">
                <div class="seventy-left-row">
                    <multiselect
                        :options="employeelist"
                        :selected="employeelist[0]"
                        :multiple="false"
                        :searchable="true"
                        :placeholder=""
                        key="empid"
                        label="display_name"
                        @update="changeSelectedEmp">
                    </multiselect>

<!--                    <select v-model="selectedemp" @change="changeSelectedEmp">-->
<!--                        <option v-for="emp in employeelist" value="{{ emp.empid }}">{{ emp.display_name }}</option>-->
<!--                    </select>-->
                    <button class="button print-btn" @click="printPayslip"><?php _e( 'Print Payslip', 'erp-pro' ); ?></button>
                </div>
            </div>

            <div id="printPayslipArea" class="row">
                <h2 class="erp-pay-slip-label" style="text-align: center"><?php _e( 'Payslip', 'erp' ) ?></h2>
                <div class="erp-payslip-header">
                    <h2>{{ extra_info.company_name }}</h2>
                </div>

                <address class="address">
                {{ extra_info.company_address }}
                </address>
                <br>
                <b><?php _e( 'Employee Name', 'erp' ) ?></b>
                <br>
                {{ ( '' == selectedemp_name ) ? employeelist[0].display_name : selectedemp_name }}
                <br>
                <b><?php _e( 'Employee Address', 'erp' ) ?></b>
                <address class="address">
                    {{ extra_info.emp_address }}
                </address>
                <div class="inner-row">
                    <span class="draft-text">{{ draft_text }}</span>
                    <ul class="payslip-list">
                        <li>
                            <label><b><?php _e( 'Department', 'erp-pro' ); ?></b></label>
                            <label><b><?php _e( 'Designation', 'erp-pro' ); ?></b></label>
                            <label><b><?php _e( 'Period', 'erp-pro' ); ?></b></label>
                        </li>
                        <li>
                            <label>{{ emp_info[0] ? emp_info[0].dept : '-' }}</label>
                            <label>{{ emp_info[0] ? emp_info[0].desig : '-' }}</label>
                            <label>{{ cal_info[0] ? cal_info[0].from_date : '' }}&nbsp;<?php _e( 'to', 'erp-pro' ); ?>
                                &nbsp;{{ cal_info[0] ? cal_info[0].to_date : '' }}</label>
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
                            <label><?php echo get_transient( 'payment_date' ); ?></label>
                            <label>{{ emp_info[0] ? emp_info[0].tax_number : '-' }}</label>
                            <label>{{ emp_info[0] ? emp_info[0].bank_acc_number: '-' }}</label>
                            <label>{{ emp_info[0] ? emp_info[0].payment_method : '-' }}</label>
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
                                <label class="text-alignright">{{ pay_basic | custom_currency }}</label>
                            </li>
                            <li v-for="plist in additional_info">
                                <label class="text-alignleft">{{ plist.payitem }}</label>
                                <label class="text-alignright">{{ plist.pay_item_amount | custom_currency }}</label>
                            </li>
                        </ul>

                    </div>
                    <div class="half-right-row">
                        <ul class="paylist">
                            <li>
                                <label><b><?php _e( 'Deductions', 'erp-pro' ); ?></b></label>
                            </li>
                            <li v-for="plist in deduct_info">
                                <label class="text-alignleft">{{ plist.payitem }}</label>
                                <label class="text-alignright">{{ plist.pay_item_amount | custom_currency }}</label>
                            </li>
                            <li class="final-total-row">
                                <label class="text-alignleft"><?php _e( 'Total Deduction', 'erp-paryoll' ); ?></label>
                                <label class="text-alignright">{{ total_deduction | custom_currency }}</label>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="inner-row">
                    <div class="half-left-row" style="clear: left">
                        <ul class="paylist paylist-final-amount">
                            <li>
                                <label class="text-alignleft"><b><?php _e( 'Total Payment', 'erp-paryoll' ); ?></b></label>
                                <label class="text-alignright"><b>{{ total_payment | custom_currency }}</b></label>
                            </li>
                        </ul>
                    </div>
                    <div class="half-right-row">
                        <ul class="paylist paylist-final-amount">
                            <li>
                                <label class="text-alignleft"><b><?php _e( 'Net Pay', 'erp-pro' ); ?></b></label>
                                <label class="text-alignright"><b>{{ net_pay | custom_currency }}</b></label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nv-holder">
        <a href="<?php echo $next_activity_url; ?>"
           class="button button-primary alignright nbutton"><?php _e( 'Next &rarr;', 'erp-pro' ); ?></a>
        <a href="<?php echo $back_activity_url; ?>"
           class="button button-primary alignright bbutton"><?php _e( '&larr; Back', 'erp-pro' ); ?></a>
    </div>
</div>
