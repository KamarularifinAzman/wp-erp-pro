<?php
/**
 * Version 1.1.3 updated
 *
 * @since 1.1.3
 *
 * @return void
 */
function erp_att_update_1_1_3() {
    //Attendance reminder
    $att_reminder = [
        'subject' => 'Attendance Reminder for {date}',
        'heading' => 'Attendance Reminder for {date}',
        'body'    => '
Hi {employee_name},
This is a gentle reminder that you did not check-in today using the Online Attendance System.
Please note that if you do not check-in, your work hour for today will be counted as ‘0’ (Zero).
So, we would suggest you to perform the check-in procedure right away to avoid unwanted circumstances. 

Thanks.'
    ];

    if( empty(get_option('erp_email_settings_attendance'))){
        update_option( 'erp_email_settings_attendance-reminder', $att_reminder );
    }
}

erp_att_update_1_1_3();