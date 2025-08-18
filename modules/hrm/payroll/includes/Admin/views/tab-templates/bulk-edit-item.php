<?php
$pay_items      = get_pay_items( array() );
$dept_options   = erp_hr_get_departments_dropdown_raw( __( 'All Department', 'erp-document' ) );
$desig_options  = erp_hr_get_designation_dropdown_raw( __( 'All Designations', 'erp-document' ) );
?>

<div id="bulk_edit_items" class="wrap payroll-report-container erp">
    <h1><?php _e( 'Bulk pay item edit', 'erp-pro' ); ?></h1>

    <div id="bulk_edit_items_container" class="erp-grid-container">
        <div class="row">
            <div class="col-6">
                <div class="postbox">
                    <div class="inside">
                        <div id="bulk_edit_items_wrapper" class="information-container">
                            <div id="candidate-overview-zone">
                                <select name="pay_items" id="pay_items" class="erp-select-field">
                                    <option value="-1">Select a pay item</option>
                                    <?php foreach ( $pay_items as $pay_item ) { ?>
                                    <option value="<?php echo $pay_item->id ;?>"><?php echo $pay_item->payitem ;?></option>
                                    <?php  } ?>
                                </select>

                                <select name="emp_dept" id="emp_dept" class="erp-select-field">
                                    <?php foreach ( $dept_options as $dept_option_key => $dept_option_value ) { ?>
                                        <option value="<?php echo $dept_option_key ;?>"><?php echo $dept_option_value ;?></option>
                                    <?php  } ?>
                                </select>

                                <select name="emp_desig" id="emp_desig" class="erp-select-field">
                                    <?php foreach ( $desig_options as $desig_option_key => $desig_option_value ) { ?>
                                        <option value="<?php echo $desig_option_key ;?>"><?php echo $desig_option_value ;?></option>
                                    <?php  } ?>
                                </select>

                                <input autocomplete="off" type="search" id="emp_name" name="emp_name" value="" placeholder="Search an employee">

                                <button class="button" v-on:click.prevent="searchByItem"><?php _e( 'Search', 'erp-pro' ); ?></button>

                                <span class="spinner render_spinner" id="spinner-1"></span>

                                <div v-if="employeeLoaded" style="margin-top: 10px;">
                                    <select name="set_payment" id="set_payment" class="erp-select-field" v-model="setPayment" @change="togglePaymentInput">
                                        <option value="fixed"><?php _e( 'Fixed Payment', 'erp-pro' ); ?></option>
                                        <option value="atts"><?php _e( 'Attendance Based Payment', 'erp-pro' ); ?></option>
                                    </select>
                                    
                                    <span v-if="showFixedInput">
                                        <input placeholder="Set fixed payment for all fields" type="number" id="set_fixed_value_to_all_field" @input="preventToTypeNull">
                                        <button class="button" id="set_fixed_value_to_all_button" v-on:click.prevent="setFixedValToAll"><?php _e( 'Set', 'erp-pro' ); ?></button>
                                    </span>

                                    <span v-if="showAttsInput">
                                        <input placeholder="Set payment per day" v-model="paymentRate" type="number" id="set-payment-per-day" @input="preventToTypeNull" min="0">
                                        <input type="text" placeholder="Date range for attendance" id="atts-date-range" name="atts_date">
                                        <button class="button" id="set_payment_per_day_btn" v-on:click.prevent="searchByItem"><?php _e( 'Set', 'erp-pro' ); ?></button>
                                    </span>

                                    <span class="spinner render_spinner" id="spinner-2"></span>
                                </div>

                                <table id="default-report" class="wp-list-table widefat fixed striped table-rec-reports">
                                    <thead>
                                        <tr>
                                            <th width="6%;"><?php _e( 'SL', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Employee', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Department', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Designation', 'erp-pro' ); ?></th>
                                            <th v-if="attsLoaded"><?php _e( 'Days Worked', 'erp-pro' ); ?></th>
                                            <th v-if="attsLoaded"><?php _e( 'Payment Per Day', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Total Payment', 'erp-pro' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="not-loaded">
                                    <tr v-for="(index,rdata) in items">
                                        <td class="align-center">{{ index + 1 }}</td>
                                        <td class="align-center">{{ rdata.first_name }} {{ rdata.last_name }}</td>
                                        <td class="align-center">{{ rdata.department }}</td>
                                        <td class="align-center">{{ rdata.designation }}</td>
                                        <td v-if="attsLoaded" class="align-center">{{ rdata.days_worked  }}</td>
                                        <td v-if="attsLoaded" class="align-center">{{ paymentRate }}</td>
                                        <td class="align-center">
                                            <input class="pay_item_value" type="number" v-model="rdata.pay_item_value" min="0" @input="preventToTypeNull">
                                        </td>
                                    </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="align-center" colspan="{{ attsLoaded ? 5 : 3 }}">&nbsp;</td>
                                            <td class="align-center"><?php _e( 'Total :', 'erp-pro' ); ?></td>
                                            <td class="align-center">{{ grossTotal | custom_currency }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>
                            <div class="nv-holder">
                                <span class="spinner update_spinner"></span>
                                <button v-on:click.prevent="updateBulkItem" id="update_button" class="button button-primary alignright">
                                    <?php _e( 'Update', 'erp-pro' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- inside -->
                </div>
                <!-- postbox -->
            </div>
            <!-- col-6 -->
        </div>
        <!-- row -->
    </div>
    <!-- erp-grid-container -->
</div>
