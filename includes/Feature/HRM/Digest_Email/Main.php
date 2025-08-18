<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Digest_Email;


class Main {

    /**
     * Constructor class
     */
    public function __construct() {
        // Send Weekly digest email to HR manager
        add_action( 'erp_daily_scheduled_events', array( $this, 'send_digest_email_to_hr' ) );
        add_filter( 'erp_settings_email_section_fields', array( $this, 'get_digest_email_setting' ), 10, 2 );
        add_filter( 'erp_settings_email_sections', array( $this, 'hrm_digest_email_setting' ) );
    }

    /**
     * Get Employees based on hiring date period
     *
     * @since 1.0.0
     * 
     * @param string $from_date
     * @param string $to_date
     *
     * @return mixed
     */
    public function get_employees_by_hiring_date( $from_date, $to_date ) {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT *
            FROM {$wpdb->prefix}erp_hr_employees
            WHERE `hiring_date` BETWEEN '{$from_date}' AND '{$to_date}'
            AND status = 'active'
            ORDER BY hiring_date"
        );
    }

    /**
     * Get Employees based on current month birthday
     *
     * @since 1.0.0
     *
     * @param string $current_date
     * @param string $after_7_days_date
     * 
     * @return mixed
     */
    public function get_employees_by_birth_month( $current_date, $after_7_days_date ) {
        global $wpdb;
        
        $current_month_date      = date( 'm d', strtotime( $current_date ) );
        $after_7_days_month_date = date( 'm d', strtotime( $after_7_days_date ) );
        $results_arr             = [];
        
        $results = $wpdb->get_results(
            "SELECT *
            FROM `{$wpdb->prefix}erp_hr_employees`
            WHERE DATE_FORMAT(`date_of_birth`, \"%m %d\") BETWEEN '{$current_month_date}' AND '{$after_7_days_month_date}'
            AND status='active' ORDER BY date_of_birth"
        );

        foreach ($results as $result) {
            $results_arr[] = ( object ) [
                'user_id'            => $result->user_id,
                'date_of_birth'      => $result->date_of_birth,
                'date_of_birth_only' => date('d', strtotime($result->date_of_birth)),
            ];
        }
        
        usort( $results_arr, function ( $a, $b ) {
            return $a->date_of_birth_only > $b->date_of_birth_only;
        } );
        
        return ( object ) $results_arr;
    }

    /**
     * Get Trainee & Contractual Employees based on End date is about to end
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function get_about_to_end_employees( $current_date, $ex_type, $type = 'trainee' ) {
        $c_t_employees      = erp_hr_get_contractual_employee();
        $filtered_employees = [];
        $total_days         = ( 'week' == $ex_type ) ? 7 : 30;
        
        foreach ( $c_t_employees as $user ) {
            $date1      = date_create( $current_date );
            $end_date   = get_user_meta( $user->user_id, 'end_date', true );
            $date2      = date_create( $end_date );
            $diff       = date_diff( $date1, $date2 );
            
            if ( $diff->days > 0 && $diff->days < $total_days && $user->type == $type ) {
                $filtered_employees[] = ( object ) [
                    'user_id'  => $user->user_id,
                    'end_date' => $end_date
                ];
            }
        }
        
        usort( $filtered_employees, function ( $a, $b ) {
            return $a->end_date > $b->end_date;
        } );

        return ( object ) $filtered_employees;
    }

    /**
     * Get Hiring anniversary of the employees
     *
     * @since 1.0.0
     *
     * @return mixed
     *
     */
    function get_upcomming_hiring_date_anniversary( $current_date, $after_7_days_date )
    {
        global $wpdb;
        $current_month_date      = date('m d', strtotime( $current_date ) );
        $after_7_days_month_date = date('m d', strtotime( $after_7_days_date ) );
        $results_arr             = [];
        
        $results = $wpdb->get_results(
            "SELECT *
            FROM `{$wpdb->prefix}erp_hr_employees`
            WHERE DATE_FORMAT(`hiring_date`, \"%m %d\") BETWEEN '{$current_month_date}' AND '{$after_7_days_month_date}'
            AND status='active' ORDER BY hiring_date"
        );

        foreach ( $results as $result ) {
            $results_arr[] = ( object )[
                'user_id'          => $result->user_id,
                'hiring_date'      => $result->hiring_date,
                'hiring_date_only' => date( 'd', strtotime( $result->hiring_date ) ),
            ];
        }
        
        usort( $results_arr, function( $a, $b ) {
            return $a->hiring_date_only > $b->hiring_date_only;
        } );
        
        return ( object ) $results_arr;
    }

    /**
     * Generate email section body for weekly digest email
     *
     * @since 1.0.0
     *
     * @return mixed
     *
     */
    public function generate_mail_section_body( $data, $start_tag, $heading, $end_tag = null ) {

        $table = "<table class='table'>";

        if ( count( ( array ) $data ) > 0 ) {
            foreach ( $data as $dt ) {
                if ( $end_tag != null ) {
                    $end_date = " - " . date(' M j', strtotime( $dt->$end_tag ) );
                } else {
                    $end_date = '';
                }
                
                $employee    = new \WeDevs\ERP\HRM\Employee( $dt->user_id );
                $image       =  $employee->get_avatar_url();
                $full_name   =  $employee->get_full_name();
                $start_date  =  date( 'M j', strtotime( $dt->$start_tag ) );
                $table      .= "<tr>
                                    <td class='image'><img src='{$image}' alt=''></td>
                                    <td class='name'>{$full_name}</td>
                                    <td class='date'>{$start_date} {$end_date}</td>
                                </tr>";
            }
        } else {
            $table .= "<tr><td colspan='6' class='name'>" . esc_html__( 'Currently there is no upcoming information.', 'erp-pro' ) . "</td></tr>";
        }

        $table .= "</table>";

        return $table;
    }


    /**
     * Get approved email of this week
     *
     * @since 1.0.0
     *
     * @return mixed
     *
     */
    function get_approved_leave_by_week( $current_date, $after_7_days_date ) {

        $args = array(
            'status'        => 1, // get only approved
            'start_date'    => erp_current_datetime()->setTime(0, 0)->getTimestamp(),
            'end_date'      => strtotime( $after_7_days_date )
        );
        
        $leave_list     = erp_hr_get_leave_requests( $args )['data'];
        $leave_list_arr = [];
        
        foreach ( $leave_list as $leave ) {
            $leave_list_arr[] = ( object )[
                'user_id'    => $leave->user_id,
                'start_date' => date( 'Y-m-d', $leave->start_date ),
                'end_date'   => date( 'Y-m-d', $leave->end_date ),
            ];
        }
        return ( object ) $leave_list_arr;
    }

    /**
     * Generate email body for weekly digest email
     *
     * @since 1.0.0
     *
     * @return mixed
     *
     */
    public function get_digest_email_body( $current_date, $after_7_days_date, $type ) {

        $employees_by_hiring_date         = $this->get_employees_by_hiring_date( $current_date, $after_7_days_date );
        $html_for_new_member_joining      = $this->generate_mail_section_body( $employees_by_hiring_date, 'hiring_date', 'New Team Member Joining' );

        $employees_by_birth_month         = $this->get_employees_by_birth_month( $current_date, $after_7_days_date );
        $html_for_birth_month             = $this->generate_mail_section_body( $employees_by_birth_month, 'date_of_birth', 'Birthday This '. $type );

        $leave_request                    = $this->get_approved_leave_by_week( $current_date, $after_7_days_date );
        $html_for_leave_request           = $this->generate_mail_section_body( $leave_request, 'start_date', 'Who is Out This ' . $type, 'end_date' );

        $c_t_employees                    = $this->get_about_to_end_employees( $current_date, $type, 'contract' );
        $html_for_c_t_employees_contract  = $this->generate_mail_section_body( $c_t_employees, 'end_date', 'Contract About to End' );

        $c_t_employees                    = $this->get_about_to_end_employees( $current_date, $type, 'trainee' );
        $html_for_c_t_employees_trainee   = $this->generate_mail_section_body( $c_t_employees, 'end_date', 'Trainee About to End' );

        $next_hiring_date_anniversary     = $this->get_upcomming_hiring_date_anniversary( $current_date, $after_7_days_date );
        $html_for_hiring_date_anniversary = $this->generate_mail_section_body( $next_hiring_date_anniversary, 'hiring_date', 'Work Anniversary This ' . $type );
        
        ob_start();
        
        include_once ERP_PRO_TEMPLATE_DIR . '/digest-email.php';
        
        return ob_get_clean();
    }

    /**
     * Send weekly digest email to hr
     *
     * @since 0.0.7
     *
     * @return mixed
     *
     */
    public function send_digest_email_to_hr() {

        $send_email                = false;
        $type_of_mail              = 'Weekly';
        $total_days                = '+6 days';
        $hrm_digest_email_Settings = get_option( 'erp_settings_erp-email_hrm_digest_email' );

        if ( isset( $hrm_digest_email_Settings['hrm_digest_email_enable_disable'] ) && 'no' == $hrm_digest_email_Settings['hrm_digest_email_enable_disable'] ) {
            return ;
        }

        if ( isset( $hrm_digest_email_Settings['hrm_digest_email'] ) ) {

            $hrm_digest_email = $hrm_digest_email_Settings['hrm_digest_email'];

            if ( 'week' == $hrm_digest_email ) {
                if ( current_time( 'l' ) == 'Monday' ) {
                    $send_email     = true;
                    $type_of_mail   = 'Weekly';
                    $total_days     = '+6 days';
                }
            }

            if ( 'month' == $hrm_digest_email ) {
                if ( current_time( 'd' ) == 1 ) {
                    $send_email     = true;
                    $type_of_mail   = 'Monthly';
                    $total_days     = '+30 days';
                }
            }
        }

        if ( $send_email == false ) {
            return false;
        }

        $current_date           = current_time( 'Y-m-d' );
        $after_7_days_date      = date( 'Y-m-d', strtotime( $total_days ) );
        $current_m_d            = date( 'M d', strtotime( $current_date ) );
        $after_7_days_date_m_d  = date( 'M d', strtotime( $after_7_days_date ) );
        $current_year           = date( 'Y', strtotime( $current_date ) );

        $args = array(
            'role'    => 'erp_hr_manager',
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        );

        $hr_managers = get_users($args);

        $email_recipient = "";
        foreach ($hr_managers as $hr_manager) {
            $email_recipient .= $hr_manager->user_email . ',';
        }

        $email              = new \WeDevs\ERP\Email();
        $email->id          = 'weekly-digest-email-to-hr';
        $email->title       = __( 'What\'s happening this ' . $hrm_digest_email . ' at ' . get_bloginfo('name'), 'erp-pro' );
        $email->description = __( 'Send '. $type_of_mail .' digest email to HR Manager with general information', 'erp-pro' );
        $email->subject     = __( 'What\'s happening this ' . $hrm_digest_email . ' at ' . get_bloginfo('name'), 'erp-pro' );
        $email->heading     = __( '', 'erp-pro' );
        $email->recipient   = $email_recipient;

        $email_body = $email->get_template_content( WPERP_INCLUDES . '/email/email-body.php', [
            'email_heading' => $email->heading,
            'email_body'    => wpautop(  $this->get_digest_email_body( $current_date, $after_7_days_date, $hrm_digest_email ) ),
        ] );

        $email->send( $email->get_recipient(), $email->get_subject(), $email_body, $email->get_headers(), $email->get_attachments() );
    }



    /**
     * Add hrm digest setting tab under hrm
     *
     * @since 1.0.0
     *
     * @return mixed
     *
     */
    public function hrm_digest_email_setting( $sections ) {
        $sections['hrm_digest_email'] = __( 'HRM Digest Email', 'erp-pro' );
        return $sections;
    }

    /**
     * Make hrm digest email fields
     *
     * @since 0.0.7
     *
     * @return mixed
     *
     */
    public function get_digest_email_setting( $fields, $section ) {
        $fields['hrm_digest_email'][] = [
            'title'   => __('HRM Digest Email Options', 'erp-pro'),
            'type'    => 'title',
            'desc'    => __('', 'erp-pro')
        ];

        $fields['hrm_digest_email'][] = [
            'title'   => __( 'Enable digest email', 'erp-pro' ),
            'type'    => 'radio',
            'id'      => 'hrm_digest_email_enable_disable',
            'desc'    => '',
            'default' => 'no'
        ];

        $fields['hrm_digest_email'][] = [
            'title'   => __( 'Email sending schedule', 'erp-pro' ),
            'id'      => 'hrm_digest_email',
            'type'    => 'select',
            'options' => [ 'month' => 'Monthly', 'week' => 'Weekly' ],
            'default' => 'week'
        ];

        $fields['hrm_digest_email'][] = [
            'type'    => 'sectionend', 'id' => 'script_styling_options'
        ];

        return $fields;
    }

}
