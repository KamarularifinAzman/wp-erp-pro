<?php

/** Actions */

add_action( 'admin_notices', 'erp_rec_show_notice' );
add_action( 'admin_notices', 'erp_rec_show_post_error_notice' );
add_action( 'erp_user_profile_role', 'erp_rec_user_profile_role' );
add_action( 'erp_hr_permission_management', 'erp_rec_recruiter_role_in_permission' );
add_action( 'erp_hr_after_employee_permission_set', 'erp_rec_recruiter_role_permission_set', 10, 2 );
add_action( 'erp_update_user', 'erp_rec_update_user', 10, 2 );
add_action( 'erp_rec_applied_job', 'send_email_after_applied_new_job', 10, 1 );
add_action( 'admin_init', 'copy_job' );
add_action( 'before_job_table', 'job_msg' );
add_action( 'before_delete_post', 'erp_rec_delete_log' );
// ERP Left sidebar css menu overlap css issue fix
add_action( 'admin_footer-post.php', 'erp_left_sidebar_css_fix' );

/** Filters */

add_filter('erp_hr_get_roles', 'erp_rec_recruiter_role_to_hr_roles');
// add_filter('map_meta_cap', 'erp_rec_map_meta_cap', 10, 4);
