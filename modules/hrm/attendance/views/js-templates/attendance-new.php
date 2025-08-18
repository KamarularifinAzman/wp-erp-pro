 <# if ( undefined == data ) { #>
        <span><?php _e( 'Date', 'erp-pro' ); ?></span>&nbsp;<input class="attendance-date-field" name="date" type="text" size="10">&nbsp;
    <# } else { #>
        <span><?php _e( 'Date', 'erp-pro' ); ?></span>&nbsp;<input class="attendance-date-field" name="date" value="{{data.date}}" type="text" size="10">&nbsp;
    <# } #>

    <label for="check-all-present"><input type="checkbox" id="check-all-present"><?php _e( 'Check All as Present &nbsp;', 'erp-pro' ); ?></label>
    <label for="check-all-absent"><input type="checkbox" id="check-all-absent"><?php _e( 'Check All as Absent &nbsp;', 'erp-pro' ); ?></label>

    <?php
        $employees = erp_hr_get_employees();
    ?>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Employee ID', 'erp-pro' ); ?></th>
                <th><?php _e( 'Employee Name', 'erp-pro' ); ?></th>
                <th><?php _e( 'Status', 'erp-pro' ); ?></th>
                <th><?php _e( 'Check In', 'erp-pro' ); ?></th>
                <th><?php _e( 'Check Out', 'erp-pro' ); ?></th>
                <th><?php _e( 'Working Time', 'erp-pro' ); ?></th>
            </tr>
        </thead>
        <tbody>
    <# if ( undefined == data ) { #>

    <?php
        foreach ( $employees as $key => $employee ) {
    ?>
        <tr>
            <td>
                <span> <?php echo $employee->employee_id; ?></span>
            </td>

            <td>
                <span> <?php echo $employee->display_name ?></span>
            </td>

            <td>
                <input name="emp[<?php echo $employee->user_id; ?>][present]" class="radio-present" type="radio" value="yes"><?php _e( 'Present', 'erp-pro' ); ?><br>
                <input name="emp[<?php echo $employee->user_id; ?>][present]" class="radio-absent" type="radio" value="no"><?php _e( 'Absent', 'erp-pro' ); ?>
            </td>

            <td>
                <input name="emp[<?php echo $employee->user_id; ?>][checkin]" class="checkin-input attendance-time-field" type="text" size="10">
            </td>

            <td>
                <input name="emp[<?php echo $employee->user_id; ?>][checkout]" class="checkout-input attendance-time-field" type="text" size="10">
            </td>

            <td>
                <span class="working-time"></span>
            </td>
        </tr>
    <?php
        }
    ?>

    <# } else { #>

       <# for( i in data.all ) { #>
            <tr>
                <td>{{ data.all[i].employee_id }}</td>

                <td>{{ data.all[i].employee_name }}</td>

                <td>
                    <input name="emp[{{data.all[i].user_id}}][present]" class="radio-present" type="radio" value="yes" <# if ( 'yes' == data.all[i].present ) {#>checked<#}#>><?php _e( 'Present', 'erp-pro' ); ?><br>
                    <input name="emp[{{data.all[i].user_id}}][present]" class="radio-absent" type="radio" value="no" <# if ( 'no' == data.all[i].present ) {#>checked<#}#>><?php _e( 'Absent', 'erp-pro' ); ?>
                </td>

                <td>
                    <input name="emp[{{data.all[i].user_id}}][checkin]" class="checkin-input attendance-time-field" value="{{data.all[i].checkin}}" type="text" size="10">
                </td>

                <td>
                    <input name="emp[{{data.all[i].user_id}}][checkout]" class="checkout-input attendance-time-field" value="{{data.all[i].checkout}}" type="text" size="10">
                </td>

                <td>
                    <span class="working-time"></span>
                </td>

            </tr>
        <# } #>

    <# } #>
    </tbody>
    </table>

    <# if ( undefined != data ) { #>
        <input type="hidden" name="update" value="true">
    <# } #>

    <input type="hidden" name="action" id="erp-attendance-action" value="erp-hr-attendance-new">
    <?php wp_nonce_field( 'wp-erp-hr-attendance-nonce' ); ?>

