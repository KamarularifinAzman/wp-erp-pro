<?php
if ( isset($_GET['prid']) ) {
    $payrunid = $_GET['prid'];
} else {
    wp_redirect( erp_payroll_get_admin_link('calendar') );
    exit;
}
?>
<div id="pay-run-wrapper-approve-tab" class="wrap erp-payroll-approve-tab erp-payroll-steps not-loaded">
    <h1>
        <?php _e( 'Pay Run', 'erp-pro' ); ?>&#45;{{ cal_info[0] ? cal_info[0].pay_calendar_name : '' }}&nbsp;&#40;{{ cal_info[0] ? cal_info[0].pay_calendar_type : '' }}&#41;
        <span class="alignright cal_status" :class="[ approve_status == 'Not Approved' ? 'cal_status_not_approve' : 'cal_status_approved']">
            {{ approve_status }}
        </span>
    </h1>

    <?php echo erp_payroll_payrun_tab( 'approve', $payrunid ); ?>

    <div class="nv-holder-top" v-if=" approve_status != 'Approved' ">
        <span v-if=" approve_status != 'Approved' "><?php _e( 'Payment Date', 'erp-pro' ); ?></span>
        <input type="text" v-datepicker v-model="payment_date" v-if=" approve_status != 'Approved' ">
    </div>

    <div class="postbox metabox-holder">
        <h3 class="openingform_header_title hndle">
            <?php _e( 'Ready to approve', 'erp-pro' ); ?>
            <span class="spinner"></span>
        </h3>
        <div class="inside">
            <?php
            $back_activity_url = erp_payroll_get_admin_link( 'payrun', [ 'tab'  => 'payslips', 'prid' => $payrunid ] );
            ?>

            <div class="row">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?php _e( 'Employee', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Department', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Designation', 'erp-pro' ); ?></th>
                        <th><?php  _e( 'Pay Basic', 'erp-pro' ); ?></th>
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
    </div>
    <div class="nv-holder">
        <div class="rrow">
            <button @click="approve" class="button button-primary alignright" v-if=" approve_status != 'Approved' ">
                <?php _e( 'Approve', 'erp-pro' ); ?>
            </button>
            <button @click="approveUndo" class="button button-primary alignright" v-if=" approve_status == 'Approved' ">
                <?php _e( 'Undo Approve', 'erp-pro' ); ?>
            </button>
        </div>
        <a href="<?php echo $back_activity_url; ?>"
           class="button button-primary alignright bbutton"><?php _e( '&larr; Back', 'erp-pro' ); ?></a>
    </div>
</div>
