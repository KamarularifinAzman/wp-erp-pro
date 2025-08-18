<div class="wrap erp">
    <div class="payitem-template-container">

        <div class="row">
            <div><?php _e( 'Filter by Department or Designation', 'erp-pro' ); ?></div>
            <select name="filter_dep_des" id="filter_dep_des">
                <option value="department"><?php _e( 'Department', 'erp-pro' );?></option>
                <option value="designation"><?php _e( 'Designation', 'erp-pro' );?></option>
            </select>
        </div>

        <div class="row dep-row">
            <div><?php _e( 'Select Department', 'erp-pro' ); ?></div>
            <?php $departments = erp_hr_payroll_get_dept_dropdown_raw(); ?>

            <select name="department[]" id="departmentid" class="erp-select2 widefat" multiple="multiple" tabindex="3">
                <?php foreach ( $departments as $id => $dept ) : ?>
                    <option value="<?php echo esc_attr( $id ); ?>">
                        <?php echo esc_html( $dept ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row de-row">
            <div>
                <?php _e( 'Select Designation', 'erp-pro' ); ?>
            </div>
            <?php
                $designations = erp_hr_get_designations(array('no_object' => true));
                $designations = erp_hr_payroll_get_designation_with_employees($designations);
            ?>
            <select name="designation[]" id="designationid" class="widefat erp-select2" multiple="multiple" tabindex="4">
                <?php foreach ( $designations as $designation ) { ?>
                    <option value="<?php echo $designation['id'];?>">
                        <?php echo $designation['title']; ?>
                    </option>
                <?php }?>
            </select>
        </div>

    </div>
</div>
