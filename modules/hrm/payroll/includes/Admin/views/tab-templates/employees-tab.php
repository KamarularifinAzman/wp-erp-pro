<?php
if ( !empty($_GET['prid']) || !empty($_GET['calid']) ) {
    $payrunid   = empty($_GET['prid']) ? 0 : $_GET['prid'];
    $calid      = empty($_GET['calid']) ? 0 : $_GET['calid'];
    $pay_items  = get_pay_items( array() );
}
else {
    wp_redirect( erp_payroll_get_admin_link('calendar') );
    exit;
}
?>
<div id="pay-run-wrapper-employees" class="wrap erp-payroll-employees erp-payroll-steps not-loaded">
    <h1>
        <?php _e( 'Pay Run', 'erp-pro' ); ?>
        <span class="alignright cal_status" :class="[ approve_status == 'Not Approved' ? 'cal_status_not_approve' : 'cal_status_approved']">
            {{ approve_status }}
        </span>
    </h1>
    <?php echo erp_payroll_payrun_tab( 'employees', $payrunid ); ?>

    <div class="nv-holder-top" v-if=" approve_status != 'Approved' ">
        <div class="separator">
            <span><?php _e( 'From Date', 'erp-pro' ); ?></span>
            <input type="text" v-datepicker v-model="from_date" class="erp-payroll-date">
            <span class="second-field"><?php _e( 'To Date', 'erp-pro' ); ?></span>
            <input type="text" v-datepicker v-model="to_date" class="erp-payroll-date second-date">
        </div>
        <div class="separator">
            <span><?php _e( 'Payment Date', 'erp-pro' ); ?></span>
            <input type="text" v-datepicker v-model="payment_date" class="erp-payroll-date">
            <!-- <span></span> -->
            <button @click="verifyDate" class="button button-primary alignleft"><?php _e( 'Generate Employee List', 'erp-pro' ); ?></button>
        </div>

        <!-- <div class="nv-holder">
            <button @click="verifyDate" class="button button-primary alignleft"><?php _e( 'Apply', 'erp-pro' ); ?></button>
        </div> -->

        <div v-if="dateVerified">
            <?php if( isset( $_GET['calid'] ) && ! empty( $_GET['calid'] ) ) { ?>
            <div class="wrapper_pay_item">
                <div class="wrapper_pay_item_row">
                    <div class="wrapper_pay_item_col">
                        <?php _e( 'Do you want to specify pay item fields ?', 'erp-pro' ); ?>
                    </div>
                    <div class="wrapper_pay_item_col">
                        <input type="checkbox" style="margin-top: 2px;" id="is_specific_field">
                    </div>
                    <div class="wrapper_pay_item_col select_pay_item_class">
                        <?php _e( 'Select pay items', 'erp-pro' ); ?>
                    </div>
                    <div class="wrapper_pay_item_col select_pay_item_class">
                        <select name="pay_item_dropdown[]" id="pay_item_dropdown" class="pay_item_dropdown" multiple="multiple">
                            <?php foreach ( $pay_items as $pay_item ) : ?>
                                <option value="<?php echo $pay_item->id; ?>"><?php echo $pay_item->payitem; ?></option>
                            <?php  endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="postbox metabox-holder">
                <h3 class="openingform_header_title hndle">
                    <?php _e( 'Active Employees', 'erp-pro' ); ?>
                    <span class="spinner"></span>
                </h3>
                <div class="inside">
                    <table class="table">
                        <thead>
                        <tr>
                            <th><?php _e( 'Employee', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Department', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Designation', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Pay Rate', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Time Worked', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Pay Basic', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Payment', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Deduction', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Tax', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Net Pay', 'erp-pro' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="empdata in employeelist"
                        >
                            <td>
                                <?php if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {
                                    $emp_url = add_query_arg( [
                                        'page'        => 'erp-hr',
                                        'section'     => 'people',
                                        'sub-section' => 'employee',
                                        'action'      => 'view',
                                        'tab'         => 'payroll',
                                        'id'          => '{{ empdata.empid }}'
                                    ], admin_url( 'admin.php' ) );
                                } else {
                                    $emp_url = add_query_arg( [
                                        'page'   => 'erp-hr-employee',
                                        'action' => 'view',
                                        'tab'    => 'payroll',
                                        'id'     => '{{ empdata.empid }}'
                                    ], admin_url( 'admin.php' ) );
                                }
                                ?>
                                <a href="<?php echo $emp_url; ?>">{{ empdata.display_name }}</a>
                            </td>
                            <td>{{ empdata.dept }}</td>
                            <td>{{ empdata.desig }}</td>
                            <td>{{ empdata.pay_rate ? empdata.pay_rate : empdata.pay_basic | custom_currency }}</td>
                            <td>{{ empdata.cal_type == 'hourly' ? empdata.time_worked : '-' }}</td>
                            <td>{{ empdata.pay_basic | custom_currency }}</td>
                            <td>{{ empdata.payment | custom_currency }}</td>
                            <td>{{ empdata.deduction | custom_currency }}</td>
                            <td>{{ empdata.tax | custom_currency }}</td>
                            <td>{{ totalRowNetPay(empdata.pay_basic, empdata.payment, empdata.deduction, empdata.tax) | custom_currency }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ totalBasic | custom_currency }}</td>
                            <td>{{ totalPayment | custom_currency }}</td>
                            <td>{{ totalDeduction | custom_currency }}</td>
                            <td>{{ totalTax | custom_currency }}</td>
                            <td>{{ totalNetTotal | custom_currency }}</td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="nv-holder">
                <button @click="updateDateandGoNextStep" class="button button-primary alignright">
                    <?php _e( 'Next &rarr;', 'erp-pro' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>
