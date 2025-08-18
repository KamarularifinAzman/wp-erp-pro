<div class="wrap erp">
    <div class="payitem-template-container">
        <div class="row">
            <label><?php _e( 'Pay item (required)', 'erp-pro' ); ?></label>
            <input name="pi_name" id="pi_name" value="" tabindex="1" type="text">
        </div>
        <div class="row">
            <label>
                <?php _e( 'Select Pay item category (required)', 'erp-pro' ); ?>
            </label>
            <?php $pi_cat = []; // erp_payroll_get_pay_item_category_name();?>
            <select name="pi_category" id="pi_category" tabindex="2">
                <?php foreach ( $pi_cat as $pay_item_category ) : ?>
                    <option value="<?php echo $pay_item_category['id'];?>">
                        <?php echo $pay_item_category['payitem_category'];?>
                    </option>
                <?php endforeach;?>
            </select>
        </div>

        <?php if ( ! version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) { ?>
            <div class="row">
                <label>
                    <?php _e( 'Account for Reporting', 'erp-pro' ); ?>
                </label>
                <?php
                $dropdown      = erp_ac_get_chart_dropdown( [
                    'exclude' => [ 1, 2, 4, 5 ]
                ] );
                $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
                    'name'     => 'payitem_wages_account',
                    'class'    => 'erp-select2 erp-ac-account-dropdown',
                    'required' => false
                ) );
                echo $dropdown_html;?>
            </div>
        <?php } ?>

        <div class="row">
            <div>
                <?php _e( 'Department', 'erp-pro' ); ?>
                <span>
                    <label class="select-all-dde">
                        <input type="checkbox" id="all-department">
                        <?php _e( 'Apply to all department', 'erp-pro' ); ?>
                    </label>
                </span>
            </div>
            <?php $departments = erp_hr_payroll_get_dept_dropdown_raw(); ?>

            <select name="department[]" id="departmentid" class="erp-select2 widefat" multiple="multiple" tabindex="3">
                <?php foreach ( $departments as $id => $dept ) : ?>
                    <option value="<?php echo esc_attr( $id ); ?>">
                        <?php echo esc_html( $dept ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="alldepartmentselected" id="alldepartmentselected" value="">
        </div>
        <div class="row de-row">
            <div>
                <?php _e( 'Designation', 'erp-pro' ); ?>
                <span>
                    <label class="select-all-dde">
                        <input type="checkbox" id="all-designation">
                        <?php _e( 'Apply to all designation', 'erp-pro' ); ?>
                    </label>
                </span>
            </div>
            <?php
                $designations = erp_hr_get_designations(array('no_object' => true));
                //filter designation here that only pass those designations those have employees
                $designations = erp_hr_payroll_get_designation_with_employees($designations);
            ?>
            <select name="designation[]" id="designationid" class="widefat erp-select2" multiple="multiple" tabindex="4">
                <?php foreach ( $designations as $designation ) { ?>
                    <option value="<?php echo $designation['id'];?>">
                        <?php echo $designation['title']; ?>
                    </option>
                <?php }?>
            </select>
            <input type="hidden" name="alldesignationselected" id="alldesignationselected" value="">
        </div>
        <div class="row de-row">
            <div>
                <?php _e( 'Employee', 'erp-pro' ); ?>
                <span>
                    <label class="select-all-dde">
                        <input type="checkbox" id="all-employee">
                        <?php _e( 'Apply to all employee', 'erp-pro' ); ?>
                    </label>
                </span>
            </div>
            <?php $employees = erp_hr_get_employees(array('no_object' => true));?>
            <select name="employee[]" id="employeeid" class="widefat erp-select2" multiple="multiple" tabindex="5">
                <option value="0"><?php _e('-- All Employee --', 'erp-pro');?></option>
                <?php foreach ( $employees as $emp ) { ?>
                    <option value="<?php echo $emp->user_id;?>">
                        <?php echo $emp->display_name; ?>
                    </option>
                <?php }?>
            </select>
            <input type="hidden" name="allemployeeselected" id="allemployeeselected" value="">
        </div>
        <div class="row">
            <label><?php _e( 'Amount', 'erp-pro' ); ?></label>
            <input type="text" maxlength="9" name="amount" id="amount" value="" tabindex="7" />
        </div>
        <div class="row">
            <label><?php _e( 'Description', 'erp-pro' ); ?></label>
            <textarea name="pi_description" id="pi_description" class="widefat" tabindex="8"></textarea>
        </div>
    </div>
</div>
