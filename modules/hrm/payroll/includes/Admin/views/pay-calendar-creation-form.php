<?php
$days              = [ 'Sunday', 'Monday', 'Tuesday', 'Wedenesday', 'Thursday', 'Friday', 'Saturday' ];
$pay_cal_types     = erp_payroll_get_pay_calendar_types_dropdown_raw( __( 'Select Type', 'erp-pro' ) );
$dept_options      = erp_hr_get_departments_dropdown_raw( __( 'All Department', 'erp-pro' ) );
$desig_options     = erp_hr_get_designation_dropdown_raw( __( 'All Designations', 'erp-pro' ) );
?>

<div id="pay-calendar-add-edit-wrapper" class="wrap erp-payroll-pay-calendar not-loaded">
    <h1><?php _e( 'Pay Calendar Settings', 'erp-pro' );?></h1>

    <div id="dashboard-widgets-wrap" class="erp-grid-container payitem-container">
        <div class="col-6">

            <div class="nv-holder">
                <div class="row-col">
                    <div class="row">
                        <label><?php _e( 'Calendar Name', 'erp-pro' );?></label>
                        <input type="text" v-model="cal_name">
                    </div>

                    <div class="row">
                        <label><?php _e( 'Calendar Type', 'erp-pro' );?></label>
                        <select v-model="cal_type" @change="changeCalType" :disabled="!createAndUpdateButtonController">
                            <?php foreach ( $pay_cal_types as $key => $pay_type ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo $pay_type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- WEEKLY/BIWEEKLY/HOURLY SETTING -->
                    <div class="row" v-if="!showForMonthly">
                        <label><?php _e( 'Normal Pay Day', 'erp-pro' );?></label>
                        <select v-model="weekday">
                            <option value=""><?php _e( 'Select week day', 'erp-pro' ); ?></option>
                            <?php foreach ( $days as $key => $day ) : ?>
                                <option value="<?php echo $key;?>"><?php echo $day;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <!-- MONTHLY SETTING -->
                    <div class="row" v-if="showForMonthly">
                        <label><?php _e( 'Pay Day Mode', 'erp-pro' );?></label>
                        <?php $days_mode = [
                            __( 'Last working day of the month', 'erp-pro' ),
                            __( 'Last day of the week each month', 'erp-pro' ),
                            __( 'Specific day each month', 'erp-pro' )
                        ];?>
                        <select v-model="paydaymode" @change="paymodeChanger">
                            <option value=""><?php _e( 'Select a pay mode', 'erp-pro' ); ?></option>
                            <?php foreach ( $days_mode as $key => $day ) : ?>
                                <option value="<?php echo $key;?>"><?php echo $day;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="row" v-if="showForMonthlySpecificday">
                        <label><?php _e( 'Pay day (1-31)', 'erp-pro' );?></label>
                        <input type="number" min="1" max="31" v-model="specific_monthly_pay_date">
                    </div>
                </div>
                <button @click.prevent="openModal" class="button button-primary button-large alignright open_modal"><?php _e( 'Add Employee', 'erp-pro' );?></button>
                <span class="spinner pay_cal_spinner"></span>
            </div>

            <div class="nv-holder">
                <select name="emp_dept" id="emp_dept" class="erp-select-field">
                    <?php foreach ( $dept_options as $dept_option_key => $dept_option_value ) { ?>
                        <option value="<?php echo $dept_option_value ;?>"><?php echo $dept_option_value ;?></option>
                    <?php  } ?>
                </select>

                <select name="emp_desig" id="emp_desig" class="erp-select-field">
                    <?php foreach ( $desig_options as $desig_option_key => $desig_option_value ) { ?>
                        <option value="<?php echo $desig_option_value ;?>"><?php echo $desig_option_value ;?></option>
                    <?php  } ?>
                </select>

                <input autocomplete="off" type="search" id="emp_name" name="emp_name" value="" placeholder="Search Employee" class="erp-text-field">

                <button class="button" v-on:click.prevent="searchEmpByParam"><?php _e( 'Search', 'erp-pro' ); ?></button>
            </div>

            <div class="postbox pc-postbox metabox-holder">
                <h3 class="hndle" v-if="createAndUpdateButtonController"><?php _e( 'Pay Calendar Setup', 'erp-pro' );?></h3>
                <h3 class="hndle" v-if="!createAndUpdateButtonController"><?php _e( 'Pay Calendar Modification', 'erp-pro' );?></h3>
                <div class="inside">
                    <div class="row modal-section">

                        <!-- Modal -->
                        <div id="myModal" class="modal" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content" style="max-height: 600px; overflow: scroll">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"><?php _e( 'Select Employee', 'erp-pro' );?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <label><strong><?php _e( 'Select Available Employees', 'erp-pro' );?></strong></label>
                                            <div class="row by_employee no-show">
                                                <select name="selected_emp[]" class="emp-select-dropdown" multiple>
                                                    <option v-for="(key, name) in allowedEmps" :value="key">{{ name }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row half-col">
                                            <label><strong><?php _e( 'Select Department', 'erp-pro' );?></strong></label>
                                            <?php $departments = erp_hr_payroll_get_dept_dropdown_raw(); ?>
                                            
                                            <ul class="checkbox-list">
                                                <?php foreach ( $departments as $id => $dept ) : ?>
                                                    <li>
                                                        <label>
                                                            <input type="checkbox" name="department[]" v-model="dept" value="<?php echo esc_attr( $id ); ?>">
                                                            <?php echo esc_html( $dept ); ?>
                                                        </label>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <div class="row half-col">
                                            <label><strong><?php _e( 'Select Designation', 'erp-pro' );?></strong></label>
                                            <?php
                                            $designations = erp_hr_get_designations(array('no_object' => true));
                                            //filter designation here that only pass those designations those have employees
                                            $designations = erp_hr_payroll_get_designation_with_employees($designations);
                                            ?>
                                            <ul class="checkbox-list">
                                                <?php foreach ( $designations as $designation ) { ?>
                                                    <li>
                                                        <label>
                                                            <input type="checkbox" name="designation[]" v-model="desig" value="<?php echo $designation['id'];?>">
                                                            <?php echo $designation['title']; ?>
                                                        </label>
                                                    </li>
                                                <?php }?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button @click="bringEmpToList" v-if="createAndUpdateButtonController" type="button" class="btn btn-default button button-primary" data-dismiss="modal">
                                            <?php _e( 'Add employee to list', 'erp-pro' );?>
                                        </button>
                                        <button @click="bringEmpToListEditMode" v-if="!createAndUpdateButtonController" type="button" class="btn btn-default button button-primary" data-dismiss="modal">
                                            <?php _e( 'Add employee', 'erp-pro' );?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="erp-list list-table-like separate not-loaded emp-list">
                            <li class="table-header">
                                <label><?php _e( 'SL', 'erp-pro' );?></label>
                                <label><input type="checkbox" v-model="selectAll"></label>
                                <label>
                                    <?php _e( 'Employee', 'erp-pro' );?>&#40;{{ totalEmp }}&#41;
                                </label>
                                <label><?php _e( 'Email', 'erp-pro' );?></label>
                                <label><?php _e( 'Department', 'erp-pro' );?></label>
                                <label><?php _e( 'Designation', 'erp-pro' );?></label>
                                <label><?php _e( 'Basic Pay Rate', 'erp-pro' );?></label>
                            </li>
                            <li v-for="(index,edata) in bringEmpData">
                                <label> {{ index + 1 }}</label>
                                <label><input type="checkbox" v-model="selected" :value="edata.id"></label>
                                <label>
                                    <?php if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {
                                        $emp_url = add_query_arg( [
                                            'page'        => 'erp-hr',
                                            'section'     => 'people',
                                            'sub-section' => 'employee',
                                            'action'      => 'view',
                                            'tab'         => 'payroll',
                                            'id'          => '{{ edata.id }}'
                                        ], admin_url( 'admin.php' ) );
                                    } else {
                                        $emp_url = add_query_arg( [
                                            'page'   => 'erp-hr-employee',
                                            'action' => 'view',
                                            'tab'    => 'payroll',
                                            'id'     => '{{ edata.id }}'
                                        ], admin_url( 'admin.php' ) );
                                    }
                                    ?>
                                    <a href="<?php echo $emp_url; ?>">{{ edata.display_name }}</a>
                                     </label>
                                <label>{{ edata.user_email }}</label>
                                <label>{{ edata.dept_name }}</label>
                                <label>{{ edata.desig_name }}</label>
                                <label>{{ edata.pay_basic  | custom_currency}}</label>
                            </li>
                        </ul>
                    </div>
                </div><!-- inside -->
            </div><!-- postbox -->

            <div class="nv-holder">
                <button @click="createPayCal" class="button button-primary alignright" v-if="createAndUpdateButtonController">
                    <?php _e( 'Create Pay Calendar', 'erp-pro' );?>
                </button>
                <button @click="updateActionPayCal" class="button button-primary alignright" v-if="!createAndUpdateButtonController">
                    <?php _e( 'Update Pay Calendar', 'erp-pro' );?>
                </button>
            </div>
        </div>

    </div><!-- erp-grid-container -->

</div>
