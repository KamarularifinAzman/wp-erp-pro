<?php
if ( isset($_GET['prid']) ) {
    $payrunid = $_GET['prid'];
} else {
    wp_redirect( erp_payroll_get_admin_link('calendar') );
    exit;
}
?>
<div id="pay-run-wrapper-variable-input-tab" class="wrap erp-payroll-variable-input erp-payroll-steps not-loaded">
    <h1>
        <?php _e( 'Pay Run', 'erp-pro' ); ?>&#45;{{ cal_info[0] ? cal_info[0].pay_calendar_name : '' }}&nbsp;&#40;{{ cal_info[0] ? cal_info[0].pay_calendar_type : '' }}&#41;
        <span class="alignright cal_status" :class="[ approve_status == 'Not Approved' ? 'cal_status_not_approve' : 'cal_status_approved']">
            {{ approve_status }}
        </span>
        <span class="spinner"></span>
    </h1>
    <?php echo erp_payroll_payrun_tab( 'variable_input', $payrunid ); ?>

    <div class="single-emp-info-left-side">
        <div class="postbox metabox-holder">
            <h3 class="openingform_header_title hndle">
                <?php _e( 'Employee profile information', 'erp-pro' ); ?>
            </h3>
            <div class="inside">
                <div class="row">
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
                </div>

                <ul class="add_ded_info">
                  <li>
                        <label>
                            <span class="lpart-normal"><?php _e( 'Pay Basic', 'erp-pro' );  ?></span>
                            <span class="rpart-normal">{{ pay_basic | custom_currency }}</span>
                        </label>
                    </li>
                    <li v-for="addinfo in additional_info">
                        <label>
                            <span class="lpart-normal">
                                <i @click="deleteAddItem(addinfo)" class="fa fa-times-circle" v-if=" approve_status != 'Approved' "></i>&nbsp;{{ addinfo.payitem }}
                            </span>
                            <span class="rpart-normal">{{ addinfo.pay_item_amount | custom_currency }}</span>
                            <label class="lbl-note">{{ addinfo.note }}</label>
                        </label>
                    </li>
                    <li>
                        <label>
                            <span class="lpart"><?php _e( 'Total Payment', 'erp-paryoll' ); ?></span>
                            <span class="rpart">{{ total_payment | custom_currency }}</span>
                        </label>
                    </li>
                </ul>

                <ul class="add_ded_info">
                    <li v-for="dedinfo in deduct_info">
                        <label>
                            <span class="lpart-normal">
                                <i @click="deleteDeductItem(dedinfo)" v-if="removeBtn_status" class="fa fa-times-circle"></i>&nbsp;{{ dedinfo.payitem }}
                            </span>
                            <span class="rpart-normal">{{ dedinfo.pay_item_amount | custom_currency }}</span>
                            <label class="lbl-note">{{ dedinfo.note }}</label>
                        </label>
                    </li>
                    <li>
                        <label>
                            <span class="lpart"><?php _e( 'Total Deduction', 'erp-paryoll' ); ?></span>
                            <span class="rpart">{{ total_deduction | custom_currency }}</span>
                        </label>
                    </li>
                    <li class="net-pay">
                        <label>
                            <span class="lpart"><?php _e( 'Net Pay', 'erp-paryoll' ); ?></span>
                            <span class="rpart">{{ net_pay | custom_currency }}</span>
                        </label>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="single-emp-info-right-side">
        <div class="postbox metabox-holder">
            <h3 class="openingform_header_title hndle"><?php _e( 'Additional allowance or deduction for this pay run only', 'erp-pro' ); ?></h3>
            <div class="inside">
                <?php
                $back_activity_url = erp_payroll_get_admin_link( 'payrun', [ 'tab'  => 'employees', 'prid' => $payrunid ] );
                $next_activity_url = erp_payroll_get_admin_link( 'payrun', [ 'tab'  => 'payslips', 'prid' => $payrunid ] );
                ?>

                <div class="row" v-if=" approve_status != 'Approved' ">
                    <h4 class="dynamic-header"><?php _e( 'Additional Pay', 'erp-pro' ); ?></h4>
                    <select v-model="additional_basic_pay_title">
                        <option v-for="payitem in payAllowanceItemList" value="{{ payitem.id }}">
                            {{ payitem.payitem }}
                        </option>
                    </select>
                    <input type="number" min="1" max="100000" v-model="additional_basic_pay_amount">
                    <input type="text" v-model="additional_basic_pay_amount_note" placeholder="Note">
                    <button class="button" @click="addAdditionBasicPay"><i class="fa fa-plus"></i></button>
                </div>

                <div class="row" v-if=" approve_status != 'Approved' ">
                    <h4 class="dynamic-header"><?php _e( 'Payments (Non-Taxable)', 'erp-pro' ); ?></h4>
                    <select v-model="additional_payment_non_taxable_title">
                        <option v-for="payitem in payNonTaxbleItemList" value="{{ payitem.id }}">
                            {{ payitem.payitem }}
                        </option>
                    </select>
                    <input type="number" min="1" max="100000" v-model="additional_payment_non_taxable_amount">
                    <input type="text" v-model="additional_payment_non_taxable_amount_note" placeholder="Note">
                    <button class="button" @click="addAdditionalPaymentNonTaxable"><i class="fa fa-plus"></i></button>
                </div>

                <div class="row" v-if=" approve_status != 'Approved' ">
                    <h4 class="dynamic-header"><?php _e( 'Additional Deduction', 'erp-pro' ); ?></h4>
                    <select v-model="additional_deduction_title">
                        <option v-for="payitem in payDeductionItemList" value="{{ payitem.id }}">
                            {{ payitem.payitem }}
                        </option>
                    </select>
                    <input type="number" min="1" max="100000" v-model="additional_deduction_amount">
                    <input type="text" v-model="additional_deduction_amount_note" placeholder="Note">
                    <button class="button" @click="addAdditionalDeduction"><i class="fa fa-plus"></i></button>
                </div>
            </div>
        </div>

        <div class="nv-holder">
            <div class="row">
                <a href="<?php echo $next_activity_url; ?>" class="button button-primary alignright nbutton"><?php _e( 'Next &rarr;', 'erp-pro' ); ?></a>
                <a href="<?php echo $back_activity_url; ?>" class="button button-primary alignright bbutton"><?php _e( '&larr; Back', 'erp-pro' ); ?></a>
            </div>
        </div>
    </div>

</div>
