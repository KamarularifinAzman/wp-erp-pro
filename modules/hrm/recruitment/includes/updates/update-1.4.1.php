<?php

function erp_hr_rec_update_default_email_content_for_new_job_application() {
    $email_content_for_new_job_application         = get_option( 'erp_email_settings_new-job-application-submitted' );

    $email_content_for_new_job_application['body'] = '
                                                        Hello Hr Manager,

                                                        {applicant_profile_link} has applied for the position of {position_link} at {date}.

                                                        Thank you';

    update_option( 'erp_email_settings_new-job-application-submitted', $email_content_for_new_job_application );
}

erp_hr_rec_update_default_email_content_for_new_job_application();