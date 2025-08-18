<?php

/**
 * get the hiring status
 * @return array
 */
function erp_rec_get_hiring_status() {
    $hr_status = array(
        // 'schedule_interview' => __( 'Schedule Interview', 'erp-pro' ),
        // 'put_on_hold'        => __( 'Put on Hold', 'erp-pro' ),
        // 'checking_reference' => __( 'Checking Reference', 'erp-pro' ),
        // 'not_a_fit'          => __( 'Not a Fit', 'erp-pro' ),
        // 'not_qualified'      => __( 'Not Qualified', 'erp-pro' ),
        // 'over_qualified'     => __( 'Over Qualified', 'erp-pro' ),
        // 'archive'            => __( 'Archive', 'erp-pro' ),
        'rejected'           => __( 'Rejected', 'erp-pro' ),
        'withdrawn'          => __( 'Withdrawn', 'erp-pro' ),
        'decline_offer'      => __( 'Declined Offer', 'erp-pro' ),
    );

    return apply_filters( 'erp_hiring_status', $hr_status );
}

/**
 * Get recruitment status drop down
 *
 * @param  int  status id
 * @param  string  selected status
 *
 * @return string the drop down
 */
function erp_hr_get_status_dropdown( $selected = '' ) {
    $status   = erp_rec_get_hiring_status();
    $dropdown = '';

    if ( $status ) {
        foreach ( $status as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

/**
 * get the minimum experience of recruitment
 *
 * @since 1.0.5 Updated experience options to show Fresher to 10 years+ individually
 *
 * @return array
 */
function erp_rec_get_recruitment_minimum_experience() {
    $min_exp = array(
        'Fresher'   => __( 'Fresher', 'erp-pro' ),
        '1 Year'  => __( '1 Year', 'erp-pro' ),
        '2 Years'  => __( '2 Years', 'erp-pro' ),
        '3 Years'  => __( '3 Years', 'erp-pro' ),
        '4 Years'  => __( '4 Years', 'erp-pro' ),
        '5 Years'  => __( '5 Years', 'erp-pro' ),
        '6 Years'  => __( '6 Years', 'erp-pro' ),
        '7 Years'  => __( '7 Years', 'erp-pro' ),
        '8 Years'  => __( '8 Years', 'erp-pro' ),
        '9 Years'  => __( '9 Years', 'erp-pro' ),
        '10 Years' => __( '10 Years', 'erp-pro' ),
        'Above 10 Years' => __( 'Above 10 Years', 'erp-pro' ),
    );

    return apply_filters( 'erp_recruitment_minimum_experience', $min_exp );
}

/**
 * get default fields
 *
 * @return array
 */
function erp_rec_get_default_fields() {

    $default_fields = array(
        'name' => array(
            'label'       => __( 'Name', 'erp-pro' ),
            'type'        => 'name',
            'required'    => true
        ),
        'email'      => array(
            'label'       => __( 'Email', 'erp-pro' ),
            'name'        => 'email',
            'type'        => 'email',
            'placeholder' => __( 'enter email address', 'erp-pro' ),
            'required'    => true
        ),
        'upload_cv'  => array(
            'label'    => __( 'Upload CV', 'erp-pro' ),
            'name'     => 'erp_rec_file', //wpuf_file
            'type'     => 'file',
            'help'     => __( 'only doc, pdf or docx file allowed and file size will be less than 2MB', 'erp-pro' ),
            'required' => true
        )
    );

    return $default_fields;
}

/**
 * get personal fields
 *
 * @return array
 */
function erp_rec_get_personal_fields() {

    $country = \WeDevs\ERP\Countries::instance();

    $personal_fields = array(
        'upload_photo'      => array(
            'label'       => __( 'Upload Photo', 'erp-pro' ),
            'name'        => 'upload_photo',
            'type'        => 'file',
            'placeholder' => '',
            'required'    => false,
            'help'        => __( 'only jpg, jpeg, png image formate allowed and image size will be less than 2MB')
        ),
        'cover_letter'    => array(
            'label'       => __( 'Cover Letter', 'erp-pro' ),
            'name'        => 'cover_letter',
            'type'        => 'textarea',
            'placeholder' => '',
            'required'    => false,
            'help'        => __( 'Why do you think you are a good fit for this job?')
        ),
        'mobile'          => array(
            'label'       => __( 'Mobile', 'erp-pro' ),
            'name'        => 'mobile',
            'type'        => 'text',
            'placeholder' => '',
            'required'    => false
        ),
        'other_email'     => array(
            'label'       => __( 'Other Email', 'erp-pro' ),
            'name'        => 'other_email',
            'type'        => 'email',
            'placeholder' => '',
            'required'    => false
        ),
        'nationality'     => array(
            'label'    => __( 'Nationality', 'erp-pro' ),
            'name'     => 'nationality',
            'type'     => 'select',
            'options'  => $country->countries,
            'required' => false
        ),
        'marital_status'  => array(
            'label'    => __( 'Marital Status', 'erp-pro' ),
            'name'     => 'marital_status',
            'type'     => 'select',
            'options'  => array(
                'single'  => __( 'Single', 'erp-pro' ),
                'married' => __( 'Married', 'erp-pro' ),
                'widowed' => __( 'Widowed', 'erp-pro' )
            ),
            'required' => false
        ),
        'hobbies'         => array(
            'label'       => __( 'Hobbies', 'erp-pro' ),
            'name'        => 'hobbies',
            'type'        => 'textarea',
            'placeholder' => '',
            'required'    => false
        ),
        'address'         => array(
            'label'       => __( 'Address', 'erp-pro' ),
            'name'        => 'address',
            'type'        => 'textarea',
            'placeholder' => '',
            'required'    => false
        ),
        'phone'           => array(
            'label'       => __( 'Phone', 'erp-pro' ),
            'name'        => 'phone',
            'type'        => 'text',
            'placeholder' => '',
            'required'    => false
        ),
        'date_of_birth'   => array(
            'label'       => __( 'Date of Birth', 'erp-pro' ),
            'name'        => 'date_of_birth',
            'type'        => 'date',
            'placeholder' => '',
            'required'    => false
        ),
        'gender'          => array(
            'label'    => __( 'Gender', 'erp-pro' ),
            'name'     => 'gender',
            'type'     => 'select',
            'options'  => array(
                'male'   => __( 'Male', 'erp-pro' ),
                'female' => __( 'Female', 'erp-pro' )
            ),
            'required' => false
        ),
        'driving_license' => array(
            'label'       => __( 'Driving License', 'erp-pro' ),
            'name'        => 'driving_license',
            'type'        => 'text',
            'placeholder' => __( 'enter driving license', 'erp-pro' ),
            'required'    => false
        ),
        'website'         => array(
            'label'       => __( 'Website', 'erp-pro' ),
            'name'        => 'website',
            'type'        => 'text',
            'placeholder' => '',
            'required'    => false
        ),
        'biography'       => array(
            'label'       => __( 'Biography', 'erp-pro' ),
            'name'        => 'biography',
            'type'        => 'textarea',
            'placeholder' => '',
            'required'    => false,
            'help'        => __( 'Let us know a little bit about yourself', 'erp-pro' )
        )
    );

    return apply_filters( 'erp_personal_fields', $personal_fields );
}

/**
 * Get count applicants number
 *
 * @param int custom post id
 *
 * @return int total application counter
 */
function erp_rec_applicant_counter( $job_id ) {
    global $wpdb;
    if ( $job_id == 0 ) {
        $query = "SELECT COUNT(job_id)
                  FROM {$wpdb->prefix}erp_application as app
                  WHERE app.status=0";
    } else {
        $query = "SELECT COUNT(job_id)
                  FROM {$wpdb->prefix}erp_application as app
                  WHERE app.status=0 AND app.job_id='" . $job_id . "'";
    }

    return $wpdb->get_var( $query );
}

/**
 * Get total number of applicants
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_rec_get_applicant_counter($job_id) {
    global $wpdb;
    if ( $job_id == 0 ) {
        $query = "SELECT COUNT(job_id)
                  FROM {$wpdb->prefix}erp_application as app
                  LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                  ON app.applicant_id = peoplemeta.erp_people_id
                  WHERE app.status=0
                  AND peoplemeta.meta_key='status'
                  AND peoplemeta.meta_value='nostatus'";
    } else {
        $query = "SELECT COUNT(job_id)
                  FROM {$wpdb->prefix}erp_application as app
                  LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                  ON app.applicant_id = peoplemeta.erp_people_id
                  WHERE app.status=0
                  AND app.job_id='" . $job_id . "'
                  AND peoplemeta.meta_key='status'
                  AND peoplemeta.meta_value='nostatus'";
    }

    return $wpdb->get_var( $query );
}

/**
 * Get count all opening number
 *
 * @return int
 */
function erp_rec_get_all_count_number() {
    global $wpdb;
    $query = "SELECT {$wpdb->prefix}posts.ID
              FROM {$wpdb->prefix}posts
              LEFT JOIN {$wpdb->prefix}postmeta as pmeta
                    ON pmeta.post_id={$wpdb->prefix}posts.ID
              WHERE {$wpdb->prefix}posts.post_type='erp_hr_recruitment' and pmeta.meta_key='_department'";
    $res = $wpdb->get_results( $query, ARRAY_A );
    $count_all = 0;
    foreach ( $res as $rdata ) {
        $count_all++;
    }

    return $count_all;
}

/**
 * Get count job expire number
 *
 * @return int
 */
function erp_rec_get_expire_count_number() {
    global $wpdb;
    $query = "SELECT ID
              FROM {$wpdb->prefix}posts
              WHERE post_type='erp_hr_recruitment'";
    $res = $wpdb->get_results( $query, ARRAY_A );
    $count_expire = 0;
    foreach ( $res as $rdata ) {
        $e_date = get_post_meta( $rdata['ID'], '_expire_date', true ) ? get_post_meta( $rdata['ID'], '_expire_date', true ) : "N/A";
        if ( 'publish' == get_post_status( $rdata['ID'] ) ) {
            if ( $e_date != "N/A" ) {
                if ( strtotime( date('Y-m-d') ) > strtotime( $e_date ) ) {
                    $count_expire++;
                }
            }
        }
    }

    return $count_expire;
}

/**
 * Get count openings number
 *
 * @return int
 */
function erp_rec_get_open_count_number() {
    global $wpdb;
    $query = "SELECT ID
              FROM {$wpdb->prefix}posts
              WHERE post_type='erp_hr_recruitment'";
    $res = $wpdb->get_results( $query, ARRAY_A );
    $count_open = 0;
    foreach ( $res as $rdata ) {
        $e_date = get_post_meta( $rdata['ID'], '_expire_date', true ) ? get_post_meta( $rdata['ID'], '_expire_date', true ) : 'N/A';
        $department = get_post_meta( $rdata['ID'], '_department', true ) ? get_post_meta( $rdata['ID'], '_department', true ) : '';
        if ( empty( $department ) ){
            continue;
        }
        if ( 'publish' == get_post_status( $rdata['ID'] ) ) {
            if ( strtotime( date('Y-m-d') ) < strtotime( $e_date ) || 'N/A' === $e_date ) {
                $count_open++;
            }
        }
    }

    return $count_open;
}

/**
 * Get count openings draft number
 *
 * @return int
 */
function erp_rec_get_draft_count_number() {
    global $wpdb;
    $query = "SELECT ID
              FROM {$wpdb->prefix}posts
              WHERE post_type='erp_hr_recruitment'";
    $res = $wpdb->get_results( $query, ARRAY_A );
    $count_draft = 0;
    foreach ( $res as $rdata ) {
        if ( 'draft' == get_post_status( $rdata['ID'] ) ) {
            $count_draft++;
        }
    }

    return $count_draft;
}

/**
 * Get count openings pending number
 *
 * @return int
 */
function erp_rec_get_pending_count_number() {
    global $wpdb;
    $query = "SELECT ID
              FROM {$wpdb->prefix}posts
              WHERE post_type='erp_hr_recruitment'";
    $res = $wpdb->get_results( $query, ARRAY_A );
    $count_pending = 0;
    foreach ( $res as $rdata ) {
        if ( 'pending' == get_post_status( $rdata['ID'] ) ) {
            $count_pending++;
        }
    }

    return $count_pending;
}

/**
 * Get total applicants number for pagination
 *
 * @param args array
 *
 * @return int
 */
function erp_rec_total_applicant_counter( $args ) {
    global $wpdb;
    $job_id             = $args['jobid'];
    $offset             = $args['offset'];
    $limit              = isset( $args['limit'] ) ? $args['limit'] : 0;
    $filter_stage       = isset( $args['stage'] ) ? $args['stage'] : 0;
    $filter_added_by_me = isset( $args['added_by_me'] ) ? $args['added_by_me'] : 0;

    $query = "SELECT COUNT(app.applicant_id)
              FROM {$wpdb->prefix}erp_application as app";

    if ( isset( $args['status'] ) ) {
        $query .= " LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                    ON app.applicant_id = peoplemeta.erp_people_id";
    }

    if ( isset( $args['status'] ) && $args['status'] == 'hired' ) {
        $query .= " WHERE app.status=1";
    } else {
        $query .= " WHERE app.status=0";
    }

    if ( $args['jobid'] != 0 ) {
        $query .= " AND app.job_id='" . $job_id . "'";
    }
    if ( isset( $filter_stage ) && $filter_stage != '' ) { //has stage
        $query .= " AND app.stage='" . $filter_stage . "'";
    }
    if ( isset( $args['status'] ) && $args['status'] == '-1' ) {
        $query .= " AND peoplemeta.meta_key='status'
                    AND peoplemeta.meta_value='nostatus'";
    }
    if ( isset( $args['status'] ) && $args['status'] != '' && $args['status'] != '-1' ) { //has status
        $query .= " AND peoplemeta.meta_key='status' AND peoplemeta.meta_value='" . $args['status'] . "'";
    }
    if ( isset( $filter_added_by_me ) && $filter_added_by_me != '' ) { //has status
        $query .= " AND app.added_by='" . get_current_user_id() . "'";
    }

    return $wpdb->get_var( $query );
}

/**
 * Get applicants information
 *
 * @param array custom post id
 *
 * @return array
 */
function erp_rec_get_applicants_information( $args ) {
    global $wpdb;

    $defaults = array(
        'number' => 5,
        'offset' => 0
    );

    $args = wp_parse_args( $args, $defaults );

    $query = "SELECT *,
                     posts.post_title as post_title,
                     base_stage.title as title,
                     application.id as applicationid,
                     people.id as peopleid,
                ( select AVG(rating)
                    FROM {$wpdb->prefix}erp_application_rating
                    WHERE application_id = applicationid ) as avg_rating,
                CONCAT( first_name, ' ', last_name ) as full_name,
                ( select meta_value
                    FROM {$wpdb->prefix}erp_peoplemeta
                    WHERE erp_people_id = peopleid AND meta_key = 'status' ) as status
                FROM {$wpdb->prefix}erp_application as application
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON application.stage=base_stage.id
                LEFT JOIN {$wpdb->prefix}posts as posts
                ON posts.ID=application.job_id
                LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                ON application.applicant_id = peoplemeta.erp_people_id
                LEFT JOIN {$wpdb->prefix}erp_application_job_stage_relation as stage
                ON application.job_id=stage.jobid
                LEFT JOIN {$wpdb->prefix}erp_peoples as people
                ON people.id=application.applicant_id";

    if ( isset( $args['status'] ) && $args['status'] == 'hired' ) {
        $query .= " WHERE application.status='1'";
    } else {
        $query .= " WHERE application.status='0'";
    }

    if ( $args['jobid'] != 0 ) {
        $query .= " AND application.job_id='" . $args['jobid'] . "'";
    }
    if ( isset( $args['stage'] ) && $args['stage'] != '' ) { //has stage
        $query .= " AND application.stage='" . $args['stage'] . "'";
    }
    if ( isset( $args['status'] ) && $args['status'] == '-1' ) {
        $query .= " AND peoplemeta.meta_key='status'
                    AND peoplemeta.meta_value='nostatus'";
    }

    if ( isset( $args['status'] ) && $args['status'] != '' && $args['status'] != '-1' ) { //has status
        $query .= " AND peoplemeta.meta_key='status' AND peoplemeta.meta_value='" . $args['status'] . "'";
    }

    if ( isset( $args['added_by_me'] ) && $args['added_by_me'] != '' ) { //added by me query
        $query .= " AND application.added_by='" . get_current_user_id() . "'";
    }
    if ( isset( $args['search_key'] ) && $args['search_key'] != '' ) { //search is not empty
        $query .= " AND people.first_name LIKE '%" . $args['search_key'] . "%' OR people.last_name LIKE '%" . $args['search_key'] . "%'";
    }

    if ( isset( $args['orderby'] ) ) {
        $query .= " GROUP BY applicationid ORDER BY " . $args['orderby'] . " " . $args['order'] . " LIMIT {$args['offset']}, {$args['number']}";
    } else {
        $query .= " GROUP BY applicationid ORDER BY application.apply_date DESC LIMIT {$args['offset']}, {$args['number']}";
    }

    return $wpdb->get_results( $query, ARRAY_A );
}

/**
 * Get total job opening number for pagination
 *
 * @param args array
 *
 * @return int
 */
function erp_rec_total_opening_counter( $args ) {
    global $wpdb;

    $query = "SELECT COUNT(post.id)
              FROM {$wpdb->prefix}posts as post
              INNER JOIN {$wpdb->prefix}postmeta as pmeta
              ON pmeta.post_id=post.id
              WHERE post.post_type='erp_hr_recruitment'
              AND pmeta.meta_key='_expire_date'";

    if ( isset( $args['status'] ) && $args['status'] != '' && $args['status'] != '-1' && ( $args['status'] == 'draft' || $args['status'] == 'pending' ) ) { //has status
        $query .= " AND post.post_status='" . $args['status'] . "'";
    }
    if ( isset( $args['status'] ) && $args['status'] == 'publish' && $args['status'] != '' && $args['status'] != '-1' ) {
        $query .= " AND post.post_status='publish' AND DATE(pmeta.meta_value) > CURDATE()";
    }
    if ( isset( $args['status'] ) && $args['status'] == 'expired' && $args['status'] != '' && $args['status'] != '-1' ) {
        $query .= " AND post.post_status='publish' AND DATE(pmeta.meta_value) < CURDATE()";
    }

    return $wpdb->get_var( $query );
}

/**
 * Get Applicant information
 *
 * @param array $args custom post id
 *
 * @since 1.3.3 Add caching functionality
 *
 * @return array
 */
function erp_rec_get_opening_information( $args ) {
    global $wpdb;

    $defaults = array(
        'number' => 5,
        'offset' => 0
    );

    $args = wp_parse_args( $args, $defaults );

    $last_changed = erp_cache_get_last_changed( 'hrm', 'recruitments', 'erp-recruitment' );
    $cache_key    = 'erp-get-recruitments-' . md5( serialize( $args ) ) . " : $last_changed";
    $job_openings = wp_cache_get( $cache_key, 'erp-recruitment' );

    if( false === $job_openings ) {
        $query = '';

        $select = "SELECT DISTINCT post.post_title as job_title,
                    post.id as id,
                    post.post_date as created_on,
                    post.post_status as post_status,
                    pmeta.meta_value as edate
                    FROM {$wpdb->prefix}posts as post
                    INNER JOIN {$wpdb->prefix}postmeta as pmeta
                    ON pmeta.post_id=post.id";

        $where = " WHERE post.post_type='erp_hr_recruitment'  AND pmeta.meta_key='_expire_date'";

        if ( isset( $args['status'] ) && $args['status'] != '' && $args['status'] != '-1' && ( $args['status'] == 'draft' || $args['status'] == 'pending' ) ) { //has status
            $where .= " AND post.post_status='" . $args['status'] . "'";
        }
        if ( isset( $args['status'] ) && $args['status'] == 'publish' && $args['status'] != '' && $args['status'] != '-1' ) {
            $where .= " AND post.post_status='publish' AND ( DATE(pmeta.meta_value) > CURDATE() OR DATE(pmeta.meta_value) IS NULL)";
        }
        if ( isset( $args['status'] ) && $args['status'] == 'expired' && $args['status'] != '' && $args['status'] != '-1' ) {
            $where .= " AND post.post_status='publish' AND ( DATE(pmeta.meta_value) < CURDATE() AND DATE(pmeta.meta_value) IS NOT NULL)";
        }
        if ( isset( $args['search_key'] ) && $args['search_key'] != '' ) { //search is not empty
            $where .= " AND post.post_title LIKE '%" . $args['search_key'] . "%'";
        }

        if ( isset( $args['orderby'] ) ) {
            $wpdb->set_sql_mode([]);

            // get erp_application table's count and order by it
            if( $args['orderby'] === 'applicants' ) {
                $args['orderby'] = 'count(application.applicant_id)';
            }

            $select .= " LEFT JOIN {$wpdb->prefix}erp_application as application
                        ON application.job_id=post.id";
            $query .= "$select $where GROUP BY post.id";
            $query .= " ORDER BY " . $args['orderby'] . " " . $args['order'] . " LIMIT {$args['offset']}, {$args['number']}";
        } else {
            $query .= "$select $where";
            $query .= " ORDER BY id DESC LIMIT {$args['offset']}, {$args['number']}";
        }

        $job_openings = $wpdb->get_results( $query, ARRAY_A );

        wp_cache_set( $cache_key, $job_openings, 'erp-recruitment' );
    }

    return $job_openings;
}

/**
 * Get applicant information by Job ID
 *
 * @param int custom post id, applicant id
 *
 * @return array
 */
function erp_rec_get_applicant_information( $application_id ) {
    global $wpdb;

    $query = "SELECT *
                FROM {$wpdb->prefix}erp_peoples as people
                LEFT JOIN {$wpdb->prefix}erp_application as app
                ON people.id = app.applicant_id
                WHERE app.id=%d";
    return $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
}

/**
 * Get total applicants in a specific job
 *
 * @since 1.1.0
 * @since 1.3.3 Add caching functionality
 *
 * @param int $jobid
 *
 * @return int
 */
function get_applicants_counter($jobid) {
    global $wpdb;

    $cache_key = "applicant-counter-by-job-$jobid";
    $counter   = wp_cache_get( $cache_key, 'erp-recruitment' );

    if( false === $counter ) {
        $query = "SELECT COUNT(applicant_id)
            FROM {$wpdb->prefix}erp_application
            WHERE job_id=%d AND status=0";

        $counter = $wpdb->get_var( $wpdb->prepare( $query, $jobid ) );
        $counter = ( $counter != null ) ? $counter : 0;

        wp_cache_set( $cache_key, $counter, 'erp-recruitment');
    }

    return $counter;
}

/**
* Get applicant information
*
* @param int custom post id,
* @param int applicant id
*
* @return array
*/
function erp_rec_get_applicant_single_information( $applicant_id, $meta_key ) {
    global $wpdb;

    $query = "SELECT meta_value
                FROM {$wpdb->prefix}erp_peoplemeta as peoplemeta
                WHERE peoplemeta.meta_key='%s'
                AND peoplemeta.erp_people_id=%s";
    return $wpdb->get_var( $wpdb->prepare( $query, $meta_key, $applicant_id ) );
}

/*
* get comment of specific applicant of specific job
* para application id, applicant id
* return array
*/
function erp_rec_get_application_comments( $application_id ) {
    global $wpdb;

    $query = "SELECT *
                FROM {$wpdb->prefix}erp_application_comment as comment
                LEFT JOIN {$wpdb->prefix}users as user
                ON comment.admin_user_id = user.ID
                WHERE comment.application_id='" . $application_id . "'";
    return $wpdb->get_results( $query, ARRAY_A );
}

/*
 * function get application stage
 * para int
 * return array
 */
function erp_rec_get_application_stage_intvw_popup( $application_id ) {
    global $wpdb;
    $query    = "SELECT stage.stageid, base_stage.title
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON stage.jobid=application.job_id
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON stage.stageid=base_stage.id
                WHERE application.id='%d'
                ORDER BY base_stage.id";
    $stages   = $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
    $dropdown = array( 0 => __( '- Select Stage -', 'erp-pro' ) );
    if ( count( $stages ) > 0 ) {
        foreach ( $stages as $value ) {
            $dropdown[$value['stageid']] = $value['title'];
        }
    }
    return $dropdown;
}

/*
 * function get app stage
 * para int
 * return array
 */
function erp_rec_get_app_stage( $application_id ) {
    global $wpdb;
    $query = "SELECT base_stage.title
                FROM {$wpdb->prefix}erp_application as app
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON app.stage=base_stage.id
                WHERE app.id='%d'";
    return $wpdb->get_var( $wpdb->prepare( $query, $application_id ) );
}

/**
 * get the minimum experience of recruitment
 *
 * @return array
 */
function erp_rec_get_interview_time_duration() {
    $interview_time_duration = array(
        '15'  => __( '15 minutes', 'erp-pro' ),
        '30'  => __( '30 minutes', 'erp-pro' ),
        '45'  => __( '45 minutes', 'erp-pro' ),
        '60'  => __( '1 hour', 'erp-pro' ),
        '105' => __( '1 hour 30 minutes', 'erp-pro' ),
        '120' => __( '2 hours', 'erp-pro' )
    );

    return apply_filters( 'interview_time_duration', $interview_time_duration );
}

/*
* check email is duplicate or not
* para email as string, job id as int
* return bool
*/
function erp_rec_is_duplicate_email( $email, $job_id ) {
    global $wpdb;

    $query = "SELECT email
                FROM {$wpdb->prefix}erp_peoples as people
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON people.id = application.applicant_id
                WHERE people.email='%s' AND application.job_id=%d";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $email, $job_id ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/*
* check rating done or not
* para int application id , int admin user id
* return bool
*/
function erp_rec_has_rating( $application_id, $admin_user_id ) {
    global $wpdb;
    $query = "SELECT id
                FROM {$wpdb->prefix}erp_application_rating
                WHERE application_id='%d' AND user_id='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $application_id, $admin_user_id ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/*
* check this stage has candidate or not
* para int job id
* return bool
*/
function erp_rec_has_candidate( $job_id, $stage_title ) {
    global $wpdb;
    $query = "SELECT app.id
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.job_id='%d'
                AND app.stage='%s'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $job_id, $stage_title ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/*
* check this job has stage or not
* para int job id
* return bool
*/
function erp_rec_count_stage( $job_id ) {
    global $wpdb;
    $query = "SELECT stage.id
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                WHERE stage.jobid='%d'";

    return count( $wpdb->get_results( $wpdb->prepare( $query, $job_id ), ARRAY_A ) );
}

/* check rating done or not
* para int application id , int admin user id
* return bool
*/
function erp_rec_has_status( $applicant_id ) {
    global $wpdb;
    $meta_key = 'status';
    $query    = "SELECT meta_id
        FROM {$wpdb->prefix}erp_peoplemeta
        WHERE meta_key='%s' AND erp_people_id='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $meta_key, $applicant_id ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/* check duplicate stage name or not
* para int stage title
* return bool
*/
function erp_rec_check_duplicate_stage( $stage_title ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_application_stage WHERE title='%s'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $stage_title ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/*
* update people table that now that people is an employee
* para employee id
* return void
*/
function erp_rec_update_people_data( $employee_id, $email, $applicant_id ) {
    global $wpdb;
    // update application table
    $data         = array(
        'status' => 1
    );
    $where        = array(
        'applicant_id' => $applicant_id
    );
    $data_format  = array(
        '%d'
    );
    $where_format = array(
        '%d'
    );
    $wpdb->update( $wpdb->prefix . 'erp_application', $data, $where, $data_format, $where_format );

    return true;
}

/*
* file uploader
* para file array
* return array
*/
function erp_rec_handle_upload( $upload_data ) {

    $uploaded_file = wp_handle_upload( $upload_data, array( 'test_form' => false ) );
    // If the wp_handle_upload call returned a local path for the image
    if ( isset( $uploaded_file['file'] ) ) {
        $file_loc    = $uploaded_file['file'];
        $file_name   = basename( $upload_data['name'] );
        $file_type   = wp_check_filetype( $file_name );
        $attachment  = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attach_id   = wp_insert_attachment( $attachment, $file_loc );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        return array( 'success' => true, 'attach_id' => $attach_id );
    }
    return array( 'success' => false, 'error' => $uploaded_file['error'] );
}

/**
 * get the hiring stages
 * @return array
 */
function erp_rec_get_hiring_stages() {
    $hr_stages = array(
        'Screening'              => __( 'Screening', 'erp-pro' ),
        'Phone Interview'        => __( 'Interview', 'erp-pro' ),
        'Face to Face Interview' => __( 'Face to Face Interview', 'erp-pro' ),
        'Made an Offer'          => __( 'Made an Offer', 'erp-pro' )
    );

    return apply_filters( 'erp_hiring_stages', $hr_stages );
}

/*
 * get stage id and title of specific applicant of specific job
 * para application id, applicant id
 * return array
 */
function erp_rec_get_application_stages( $application_id ) {
    global $wpdb;

    $query = "SELECT stage.stageid, base_stage.title
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON stage.jobid = application.job_id
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON base_stage.id = stage.stageid
                WHERE application.id=%d";
    return $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
}

/*
 * get stage id and title of specific job
 * para job id
 * return array
 */
function erp_rec_get_this_job_stages( $job_id ) {
    global $wpdb;

    $query = "SELECT stage.stageid, base_stage.title
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON stage.stageid=base_stage.id
                WHERE stage.jobid=%d";
    return $wpdb->get_results( $wpdb->prepare( $query, $job_id ), ARRAY_A );
}

/*
 * get stage id and title of all jobs
 * para
 * return array
 */
function erp_rec_get_all_stages() {
    global $wpdb;
    $query = "SELECT stage.id, stage.title
                FROM {$wpdb->prefix}erp_application_stage as stage
                ORDER BY stage.title";
    return $wpdb->get_results( $query, ARRAY_A );
}

/*
 * get candidate number in this stage
 * para job id, stage title
 * return array
 */
function erp_rec_get_candidate_number_in_this_stages( $job_id, $stage_id ) {
    global $wpdb;

    if ( $job_id == 0 ) {
        $query = "SELECT COUNT(app.id)
                  FROM {$wpdb->prefix}erp_application as app
                  LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                  ON app.applicant_id = peoplemeta.erp_people_id
                  WHERE app.status=0
                  AND app.stage='%d'
                  AND peoplemeta.meta_key='status'
                  AND peoplemeta.meta_value='nostatus'";
        return $wpdb->get_var( $wpdb->prepare( $query, $stage_id ) );
    } else {
        $query = "SELECT COUNT(app.id)
                  FROM {$wpdb->prefix}erp_application as app
                  LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                  ON app.applicant_id = peoplemeta.erp_people_id
                  WHERE app.job_id='%d'
                  AND app.stage='%d'
                  AND app.status=0
                  AND peoplemeta.meta_key='status'
                  AND peoplemeta.meta_value='nostatus'";
        return $wpdb->get_var( $wpdb->prepare( $query, $job_id, $stage_id ) );
    }

}

/*
 * get all jobs
 * para
 * return array
 */
function erp_rec_get_all_jobs() {
    $type     = 'erp_hr_recruitment';
    $args     = array(
        'post_type'      => $type,
        'post_status'    => 'publish',
        'posts_per_page' => -1
    );
    $jobs     = [ ];
    $my_query = null;
    $my_query = new WP_Query( $args );
    if ( $my_query->have_posts() ) {
        while ( $my_query->have_posts() ) {
            $my_query->the_post();
            $jobs[] = [ 'jobid' => get_the_ID(), 'jobtitle' => get_the_title() ];
        }
    }
    wp_reset_query();  // Restore global post data stomped by the_post()
    return $jobs;
}

/*
 * get stage
 * para
 * return array
 */
function erp_rec_get_stage( $jobid ) {
    global $wpdb;
    if ( isset( $jobid ) ) {
        $query       = "SELECT COUNT(id) FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid=%d";
        $row_counter = $wpdb->get_var( $wpdb->prepare( $query, $jobid ) );
        if ( $row_counter > 0 ) {
            $query = "SELECT stage.id as sid,stage.title,
                          ( SELECT stageid
                            FROM {$wpdb->prefix}erp_application_job_stage_relation
                            WHERE jobid={$jobid} AND stageid=sid ) as stage_selected,
                          ( SELECT COUNT(id)
                            FROM {$wpdb->prefix}erp_application
                            WHERE job_id={$jobid} AND stage=sid ) as candidate_number
                          FROM {$wpdb->prefix}erp_application_stage as stage ORDER BY stage.id";
            return $wpdb->get_results( $query, ARRAY_A );
        } else {
            $query = "SELECT stage.id as sid, stage.title
                FROM {$wpdb->prefix}erp_application_stage as stage
                LEFT JOIN {$wpdb->prefix}users as user
                ON stage.created_by = user.ID";

            return $wpdb->get_results( $query, ARRAY_A );
        }
    } else {
        return false;
    }
}

/*
 * get stages in creating recruitment
 * para
 * return array
 */
function erp_rec_get_stages( $jobid ) {
    global $wpdb;
    if ( isset( $jobid ) ) {
        $query      = "SELECT stage.id as sid, stage.title as title FROM {$wpdb->prefix}erp_application_stage as stage ORDER BY sid";
        $stages     = $wpdb->get_results( $query, ARRAY_A );

        $query  = "SELECT st_rel.stageid as sid, stage.title as title
                  FROM {$wpdb->prefix}erp_application_stage as stage
                  LEFT JOIN {$wpdb->prefix}erp_application_job_stage_relation as st_rel
                  ON stage.id=st_rel.stageid
                  WHERE st_rel.jobid=%d
                  ORDER BY sid";
        $st_rel = $wpdb->get_results( $wpdb->prepare( $query, $jobid ), ARRAY_A );

        $final_selected_stage = [ ];
        foreach ( $stages as $stage_value ) {
            $got_stage_id = erp_rec_searchForId( $stage_value['sid'], $st_rel );
            if ( $got_stage_id ) {
                $final_selected_stage[] = [
                    'sid'      => $stage_value['sid'],
                    'title'    => $stage_value['title'],
                    'selected' => true
                ];
            } else {
                $final_selected_stage[] = [
                    'sid'      => $stage_value['sid'],
                    'title'    => $stage_value['title'],
                    'selected' => false
                ];
            }
        }

        return $final_selected_stage;

    } else {
        return false;
    }
}

/*
 * search id in an array
 *
 * @return mixed
 */
function erp_rec_searchForId( $sid, $array ) {
    foreach ( $array as $key => $val ) {
        if ( $val['sid'] === $sid ) {
            return $sid;
        }
    }
    return false;
}

/*
 * update stage on update recruitment post
 * para post id int
 * return mix
 */

function erp_rec_update_stage( $selected_stages, $jobid ) {

    global $wpdb;
    // first delete all stage in this job id
    $where  = array(
        'jobid' => $jobid
    );
    $format = array(
        '%d'
    );

    $old_stage = erp_rec_get_stage( $jobid );

    $wpdb->delete( $wpdb->prefix . 'erp_application_job_stage_relation', $where, $format );
    // now insert stage id to this job id
    foreach ( $selected_stages as $stdata ) {
        $sql = "INSERT INTO {$wpdb->prefix}erp_application_job_stage_relation(jobid,stageid) VALUES('%d','%d')";
        $wpdb->query( $wpdb->prepare( $sql, $jobid, $stdata ) );
    }

    erp_recruitment_purge_cache( ['list' => 'recruitments'] );

    $new_stage = erp_rec_get_stage( $jobid );
    $diff      = erp_get_array_diff( $new_stage, $old_stage );

    erp_log()->add( [
        'component'     => 'HRM',
        'sub_component' => 'Recruitment Opening',
        'changetype'    => 'edit',
        'message'       => sprintf( __( 'Stage for job opening titled <strong>%1$s</strong> has been updated', 'erp' ), get_the_title( $jobid ) ),
        'created_by'    => get_current_user_id(),
        'new_value'     => $diff['new_value'],
        'old_value'     => $diff['old_value'],
    ] );
}


/*
 * show admin notice
 * para
 * return void
 */
function erp_rec_show_notice() {
    if ( isset( $_REQUEST['page'] ) == 'erp-hr-employee' && isset( $_REQUEST['action'] ) == 'view' && isset( $_REQUEST['id'] ) ) {
        if ( $_REQUEST['page'] == 'erp-hr-employee' && $_REQUEST['action'] == 'view' && is_numeric( $_REQUEST['id'] ) && $_REQUEST['id'] > 0 && isset( $_REQUEST['message'] ) ) {
            if ( $_REQUEST['message'] == 1 ) { ?>
                <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Congrats! New employee has been created successfully', 'erp-pro' ); ?></p>
                </div><?php }
        }
    }
}

/**
 * show admin notice post error during edit in job opening
 *
 * @return void
 */
function erp_rec_show_post_error_notice() {

}

/**
 * Get applicant id by job id
 *
 * @since 1.0.2
 *
 * @return array
 */
function erp_rec_get_applicant_id($jobid) {
    global $wpdb;
    $query = "SELECT applicant_id
              FROM {$wpdb->prefix}erp_application
              WHERE job_id='%d'";
    return $wpdb->get_results( $wpdb->prepare( $query, $jobid ), ARRAY_A );
}

/**
 * Get applicant id by job id
 *
 * @since 1.0.2
 *
 * @return array
 */
function erp_rec_get_all_applicant_id() {
    global $wpdb;
    $query = "SELECT applicant_id
              FROM {$wpdb->prefix}erp_application";
    return $wpdb->get_results( $query, ARRAY_A );
}

/**
 * Remove job opening
 *
 * @since 1.0.0
 *
 * @return void
 */
function delete_candidate_info( $jobid ) {
    global $wpdb;
    wp_delete_post( $jobid );

    erp_recruitment_purge_cache( [ 'list' => 'recruitments', 'recruitment_id' => $jobid ] );

    $query = "SELECT id as appid, applicant_id
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.job_id='" . $jobid . "'";
    $udata = $wpdb->get_results( $query, ARRAY_A );

    foreach ( $udata as $peoplemetadata ) {
        $wpdb->delete( $wpdb->prefix . 'erp_peoples', array( 'id' => $peoplemetadata['applicant_id'] ), array( '%d' ) );
        $wpdb->delete( $wpdb->prefix . 'erp_peoplemeta', array( 'erp_people_id' => $peoplemetadata['applicant_id'] ), array( '%d' ) );
        $wpdb->delete( $wpdb->prefix . 'erp_application_rating', array( 'application_id' => $peoplemetadata['appid'] ), array( '%d' ) );
    }

    $wpdb->delete( $wpdb->prefix . 'erp_application', array( 'job_id' => $jobid ), array( '%d' ) );
    $wpdb->delete( $wpdb->prefix . 'erp_application_job_stage_relation', array( 'jobid' => $jobid ), array( '%d' ) );
}

/**
 * Show admin progressbar
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function erp_rec_opening_admin_progressbar( $selected ) {
    $steps = array(
        'job_description'             => __( 'Job description', 'erp-pro' ),
        'hiring_workflow'             => __( 'Hiring workflow', 'erp-pro' ),
        'job_information'             => __( 'Job information', 'erp-pro' ),
        'candidate_basic_information' => __( 'Basic information', 'erp-pro' ),
        'questionnaire_selection'     => __( 'Question set', 'erp-pro' ),
    );

    $step_counter = 1;
    $html         = '';
    $html .= '<ul class="recruitment-step-progress">';
    foreach ( $steps as $key => $value ) {
        $html .= sprintf( '<li class="%s"><span class="step-number">%d</span><span class="step-content">%s</span></li>', ( $key == $selected ) ? 'active' : 'not-active', $step_counter, $value );
        $step_counter++;
    }

    $html .= '</ul>';

    return $html;
}

/**
 * Get candidate list of today
 *
 * @since 1.1.0
 *
 * @return array
 */
function erp_hr_rec_get_candidate_list() {
    global $wpdb;

    $query = "SELECT applicant_id, job_id
              FROM {$wpdb->prefix}erp_application
              WHERE DATE(apply_date)=CURDATE()";
    $res = $wpdb->get_results( $query, ARRAY_A );
    if ( count($res) > 0 ) {
        return $res;
    } else {
        return [];
    }
}

/**
 * Get current user to-do
 *
 * @since 1.1.0
 *
 * @return array
 */
function get_todos() {
    global $wpdb;
    $user_id = ( isset( $_GET['id'] ) ) ? $_GET['id'] : get_current_user_id();

    $cache_key = "erp-get-todos-by-user-$user_id";
    $todos     = wp_cache_get( $cache_key, 'erp-recruitment' );

    if ( false === $todos ) {
        $query = "SELECT todo.title,
                todo.deadline_date,
                todo.created_at,
                user.display_name
        FROM {$wpdb->prefix}erp_application_todo as todo
        LEFT JOIN {$wpdb->prefix}erp_application_todo_relation as rtodo
        ON todo.id=rtodo.todo_id
        LEFT JOIN {$wpdb->prefix}users as user
        ON todo.created_by=user.id
        WHERE rtodo.assigned_user_id=%d AND
        todo.status=0";

        $res   = $wpdb->get_results( $wpdb->prepare( $query, $user_id ), ARRAY_A );
        $todos = count( $res ) > 0 ? $res : [];

        wp_cache_set( $cache_key, $todos, 'erp-recruitment' );
    }

    return $todos;
}

/**
 * Delete candidate
 *
 * @since 1.1.0
 *
 * @return void
 */
function delete_candidate($jobseekeremail) {
    global $wpdb;
    //get job seeker id
    $query = "SELECT id, first_name, last_name
              FROM {$wpdb->prefix}erp_peoples
              WHERE email=%s";

    $jobseeker = $wpdb->get_row( $wpdb->prepare( $query, $jobseekeremail ), ARRAY_A );

    $wpdb->delete( $wpdb->prefix . 'erp_peoples', [ 'id' => $jobseeker['id'] ], ['%d'] );
    $wpdb->delete( $wpdb->prefix . 'erp_peoplemeta', [ 'erp_people_id' => $jobseeker['id'] ], ['%d'] );
    $wpdb->delete( $wpdb->prefix . 'erp_application', [ 'applicant_id' => $jobseeker['id'] ], ['%d'] );

    erp_recruitment_purge_cache( ['list' => 'recruitments'] ); // applicant deletion

    erp_log()->add( [
        'component'     => 'HRM',
        'sub_component' => 'Recruitment',
        'changetype'    => 'delete',
        'message'       => sprintf( __( 'A jobseeker named <strong>%1$s %2$s</strong> has been deleted', 'erp' ), $jobseeker['first_name'], $jobseeker['last_name'] ),
        'created_by'    => get_current_user_id(),
    ] );
}

/**
 * Create recruitment url
 *
 * @param  string $slug Recruitment URL
 * @return string
 */
function erp_rec_url( $slug = '' ) {
    if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
        return admin_url( 'admin.php?page=' . $slug );
    }

    if ( empty( $slug ) ) {
        return admin_url( 'admin.php?page=erp-hr&section=recruitment' );
    }

    return admin_url( 'admin.php?page=erp-hr&section=recruitment&sub-section=' . $slug );
}


/** =========================================
 * Recruiter role related functionality
 * ==========================================
 */


/**
 * Add erp_recruiter role
 *
 * @param array $roles
 *
 * @return array
 */
function erp_rec_recruiter_role_to_hr_roles( $roles ) {
    $roles['erp_recruiter'] = [
        'name'         => __( 'Recruiter', 'erp' ),
        'public'       => false,
        'capabilities' => [ 'manage_recruitment' => true ]
    ];

    return $roles;
}

/**
 * Map recruitment meta cap
 *
 * @param array   $caps
 * @param string  $cap
 * @param integer $user_id
 * @param mixed   $args
 *
 * @return array
 */
function erp_rec_map_meta_cap( $caps, $cap, $user_id, $args ) {
    if ( 'manage_recruitment' === $cap ) {
        if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, erp_hr_get_manager_role() ) ) {
            $caps = [ $cap ];
        } else {
            $caps = [ 'do_not_allow' ];
        }
    }

    return $caps;
}

/**
 * User profile recruiter role checkbox
 *
 * @param object $profile_user
 *
 * @return void
 */
function erp_rec_user_profile_role( $profile_user ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $checked = in_array( 'erp_recruiter', $profile_user->roles ) ? 'checked' : '';
    ?>

    <label for="erp-recruiter">
        <input type="checkbox" id="erp-recruiter" <?php echo $checked; ?> name="erp_recruiter" value="erp_recruiter">
        <span class="description"><?php _e( 'Recruiter', 'erp' ); ?></span>
    </label>

    <?php
}

/**
 * Employee permission tab recruiter role
 *
 * @param object $user
 *
 * @return void
 */
function erp_rec_recruiter_role_in_permission( $user ) {
    if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
        return;
    }

    $is_recruiter = user_can( $user->ID, 'erp_recruiter' ) ? 'on' : 'off';

    erp_html_form_input( array(
        'label' => __( 'Recruiter', 'erp' ),
        'name'  => 'erp_recruiter',
        'type'  => 'checkbox',
        'tag'   => 'div',
        'value' => $is_recruiter,
        'help'  => __( 'This Employee is Recruiter', 'erp'  )
    ) );
}

/**
 * Set employee permission tab recruiter role after update
 *
 * @param object $post
 *
 * @param object $user
 *
 * @return void
 */
function erp_rec_recruiter_role_permission_set( $post, $user ) {
    $enable_recruiter   = ! empty( $post['erp_recruiter'] ) ? filter_var( $post['erp_recruiter'], FILTER_VALIDATE_BOOLEAN ) : false;
    $erp_recruiter_role = 'erp_recruiter';

    if ( current_user_can( erp_hr_get_manager_role() ) ) {
        if ( $enable_recruiter ) {
            $user->add_role( $erp_recruiter_role );
        } else {
            $user->remove_role( $erp_recruiter_role );
        }
    }
}

/**
 * Update user role
 *
 * @param integer $user_id
 *
 * @param object $post
 *
 * @return void
 */
function erp_rec_update_user( $user_id, $post ) {
    $new_erp_recruiter_role = ! empty( $post['erp_recruiter'] ) ? sanitize_text_field( $post['erp_recruiter'] ) : false;

    if ( ! $new_erp_recruiter_role ) {
        return;
    }

    if ( ! current_user_can( 'promote_user', $user_id ) ) {
        return;
    }

    $user = get_user_by( 'id', $user_id );

    if ( $new_erp_recruiter_role ) {
        $user->add_role( 'erp_recruiter' );
    } else {
        $user->remove_role( 'erp_recruiter' );
    }
}

/**
 * Sending email after any new job application submitted*
 * @return null
 */
function send_email_after_applied_new_job( $data ) {
    global $wpdb;

    if ( empty( $data ) ) {
        return null;
    }

    $job_id         = ( isset( $data['job_id'] ) ) ? $data['job_id'] : 0;
    $applicant_id   = ( isset( $data['applicant_id'] ) ) ? $data['applicant_id'] : 0;

    $sql = "SELECT
                app.id,
                post.post_title,
                people.first_name,
                people.last_name,
                people.email,
                app.apply_date,
                post.guid
            FROM {$wpdb->prefix}erp_application as app
            LEFT JOIN {$wpdb->prefix}posts as post
            ON post.id = app.job_id
            LEFT JOIN {$wpdb->prefix}erp_peoples as people
            ON people.id = app.applicant_id
            WHERE app.job_id = {$job_id} AND app.applicant_id = {$applicant_id}";

    $result = $wpdb->get_row( $sql );

    $data   = [
        'applicant_name'  => $result->first_name . ' ' . $result->last_name,
        'date'            => date( 'M j, Y, h:i a', strtotime( $result->apply_date ) ),
        'position'        => $result->post_title,
        'applicant_email' => $result->email,
        'applicant_id'    => $applicant_id,
        'application_id'  => $result->id,
        'job_id'          => $job_id,
        'job_post_url'    => $result->guid,
    ];

    try {
        send_new_job_application_email_to_hr( $data );
        send_confirmation_of_submission_email_to_applicant( $data );
    } catch ( \Exception $e ) {
        error_log( print_r( $e->getMessage(), true ) );
    }
}

/**
 * Sending email to hr after any new job application submitted*
 * @return null
 */
function send_new_job_application_email_to_hr( $data ) {
    $args = array(
        'role'    => 'erp_hr_manager',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    $hr_managers = get_users( $args );
    $email_recipient = "";
    foreach( $hr_managers as $hr_manager ) {
        $email_recipient .= $hr_manager->user_email . ',';
    }

    $data['recipient'] = $email_recipient;
    $emailer           = wperp()->emailer->get_email( 'NewJobApplicationSubmitted' );
    if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
        $emailer->trigger( $data );
    }
}

/**
 * Sending email to applicant after any new job application submitted*
 * @return null
 */
function send_confirmation_of_submission_email_to_applicant( $data ) {
    $data['recipient'] = $data['applicant_email'];
    $emailer           = wperp()->emailer->get_email( 'ConfirmationOfSuccessfulSubmission' );
    if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
        $emailer->trigger( $data );
    }
}

/**
 * Copy a specific job
 * @return null
 */
function copy_job() {

    if ( isset( $_GET['action'] ) && isset( $_GET['jobid'] ) && ! empty( $_GET['action'] ) && ! empty( $_GET['jobid'] ) ) {
        $jobid = (int) $_GET['jobid'];

        if ( is_int( $jobid ) && $jobid != 0 ) {

            global $wpdb;

            $sql = "SELECT
                      post.post_title,
                      post.post_content,
                      ( SELECT GROUP_CONCAT(stageid SEPARATOR '|') from {$wpdb->prefix}erp_application_job_stage_relation as jsr WHERE jsr.jobid = post.ID ) as stageid
                    FROM {$wpdb->prefix}posts as post
                    WHERE post.ID = {$jobid}";

            $job_item                 = $wpdb->get_row( $sql );
            $job_meta                 = get_post_meta( $jobid );
            $job_meta['_expire_date'] = '';


            $post_data = [
                'post_title'   => $job_item->post_title . '( copy )',
                'post_content' => $job_item->post_content,
                'post_type'    => 'erp_hr_recruitment',
                'post_status'  => 'publish'
            ];

            $postid = wp_insert_post( $post_data, true );

            erp_recruitment_purge_cache( ['list' => 'recruitments'] );

            if ( $postid && $postid != 0 ) {
                $stageid = explode( '|', $job_item->stageid );
                foreach ( $stageid as $id ) {
                        $wpdb->insert(
                        $wpdb->prefix . 'erp_application_job_stage_relation',
                        [
                           'jobid'   => $postid,
                           'stageid' => $id
                        ]
                    );
                }
            }

            if ( is_array( $job_meta ) && count( $job_meta ) > 0 ) {
                foreach ( $job_meta as $jm_key => $jm_value ) {
                    if ( $jm_key == '_personal_fields' ) {
                        $value = unserialize( $jm_value[0] );
                        add_post_meta( $postid, $jm_key, $value );
                    } else {
                        add_post_meta( $postid, $jm_key, $jm_value[0] );
                    }
                }
            }

            $location = add_query_arg( array( 'status' => 'all', 'msg_type' => 'success', 'msg' => __( 'Successfully Copied', 'erp-pro' ) ), erp_rec_url() );
            wp_redirect( $location );
            exit;

        }
    }
}

/**
 * Job table message
 * @return null
 */
function job_msg () {
    if ( isset( $_GET['msg_type'] ) && $_GET['msg_type'] == 'success' && isset( $_GET['msg'] ) && ! empty( $_GET['msg'] ) ) {
        ?>
        <div class="updated">
            <p><strong><?php echo  sanitize_text_field( $_GET['msg'] )  ;?>.</strong></p>
        </div>
        <?php
    }
}

/**
 * Add log when recruitment deleted
 *
 * @since 1.3.2
 *
 * @param int $post_id
 *
 * @return void
 */
function erp_rec_delete_log( $post_id ) {
    $post = \get_post( $post_id );

    if ( 'erp_hr_recruitment' != $post->post_type ) {
        return;
    }

    erp_log()->add( [
        'sub_component' => 'recruitment',
        'message'       => sprintf( __( '<strong>%s</strong> job opening has been deleted', 'erp' ), $post->post_title ),
        'created_by'    => get_current_user_id(),
        'changetype'    => 'delete',
    ] );
}

/**
 * ERP Left sidebar css menu overlap css issue fix
 * @return null
 */
function erp_left_sidebar_css_fix () {
    if ( isset( $GLOBALS['post_type'] ) && 'erp_hr_recruitment' !== $GLOBALS['post_type'] )
        return;
    ?>
    <style>
        .wp-editor-expand #wp-content-editor-tools {
            z-index: 1;
        }

        .wp-editor-expand div.mce-toolbar-grp {
            z-index: 1;
        }
    </style>
    <?php
}

/**
 * Purge cache data for Recruitment addons
 *
 * Remove all cache for Recruitment addons
 *
 * @since 1.3.3
 *
 * @param array $args
 *
 * @return void
 */
function erp_recruitment_purge_cache( $args = [] ) {

    $group = 'erp-recruitment';

    if ( isset( $args['recruitment_id'] ) ) {
        wp_cache_delete( "erp-recruitment-by-" . $args['recruitment_id'], $group );
    }

    if ( isset( $args['todo_by_user_id'] ) ) {
        wp_cache_delete( "erp-get-todos-by-user-" . $args['todo_by_user_id'], $group );
    }

    if ( isset( $args['list'] ) ) {
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => $args['list'] ] );
    }
}

/**
 * Get Referred Source Data from Application submission
 *
 * @param array $args
 *
 * @since 1.3.3
 *
 * @return array
 */
function erp_rec_get_referred_by_data( $args = [] ) {

    $defaults = [
        'referrer'     => null,
        'query_string' => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $medium_types = [
        'search'   => [ 'Google', 'Baidu', 'Bing', 'Yahoo', 'Ask', 'Yandex', 'DuckDuckGo', 'AOL', 'Naver', 'Seznam', 'Ecosia' ],
        'social'   => [ 'Facebook', 'Twitter', 'Linkedin', 'Tumblr', 'Instagram', 'QQ', 'WeChat', 'WhatsApp', 'Skype', 'Viber', 'Pinterest' ],
        'email'    => [ 'Gmail', 'ProtonMail', 'Outlook', 'Yahoo', 'AOLMail', 'Zoho', 'iCloud', 'GMX' ],
        'internal' => [ $_SERVER['HTTP_HOST'] ]
    ];

    $referred_data = [
        'medium'       => 'unknown',         // eg; search,     social
        'source'       => '',                // eg; Google,     Facebook
        'website_url'  => '',                // eg; google.com, facebook.com
        'source_url'   => $args['referrer'], // eg; https://google.com?search=test+sd
        'query'        => $args['query_string'],
        'referrer_url' => $args['referrer']
    ];

    if ( empty( $args['referrer'] ) ) {
        return $referred_data;
    }

    $referrer       = $args['referrer'] . '?' . $args['query_string'];
    $referrer_parts = parse_url( $referrer );

    if ( ! isset( $referrer_parts['scheme'] ) || ! in_array( strtolower( $referrer_parts['scheme'] ), ['http', 'https'] ) ) {
        return $referred_data;
    }

    foreach ( $medium_types as $key_type => $mediums ) {
        foreach ( $mediums as $medium ) {
            if ( strpos( $referrer, strtolower( $medium ) ) ) {
                $referred_data['medium']      = $key_type;
                $referred_data['source']      = $medium;
                $referred_data['website_url'] = strtolower( $medium ). '.com';
            }
        }
    }

    if ( empty( $referred_data['source'] ) ) {
        $referred_data['website_url'] = str_ireplace( 'www.', '', parse_url( $referrer, PHP_URL_HOST ) );
        $referred_data['source']      = $referrer;
    }

    return $referred_data;
}
