<?php

/**
 * Get feature warning message
 * 
 * @since 1.0.0
 * 
 * @return void
 */
function erp_pro_hr_leave_show_policy_feature_warning_msg() {
    $leave_settings_url = admin_url('admin.php?page=erp-settings#/erp-hr/leave'); ?>

    <h3 class="leave-policy-feature-warning">
        <?php
            echo sprintf(
                '# %s %s %s',
                esc_html__('Please enable from', 'erp-pro'),
                '<a href="' . esc_url( $leave_settings_url ) . '" target="blank">' .
                    esc_html__('settings', 'erp-pro') .
                '</a>',
                esc_html__('to use this section', 'erp-pro')
            );
        ?>
    </h3>
    <?php
}
