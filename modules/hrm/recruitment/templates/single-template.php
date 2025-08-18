<?php
global $post;

$localized_exp_type = [
    'required'  => __( 'Required', 'erp-pro' ),
    'preferred' => __( 'Preferred', 'erp-pro'),
];

$job_id             = get_the_ID();
$employment_types   = erp_hr_get_employee_types();
$employment_type    = get_post_meta( $job_id, '_employment_type', true);
$expire_date        = get_post_meta( $job_id, '_expire_date', true);
$is_set_expire_date = empty($expire_date)? false: true ;
$expire_timestamp   = !empty( $expire_date ) ? strtotime( $expire_date ) : false;
$location           = get_post_meta( $job_id, '_location', true );
$number_of_vacancy  = get_post_meta( $job_id, '_vacancy', true);
$vacancy            = ( $number_of_vacancy != '' ) ? $number_of_vacancy : 'N/A';
$min_experience     = get_post_meta( $job_id, '_minimum_experience', true);
$experience_field   = get_post_meta( $job_id, '_experience_field', true );
$default_exp_type   = $min_experience !== 'Fresher' ? 'required' : '';
$experience_type    = get_post_meta( $job_id, '_experience_type', true ) ? get_post_meta( $job_id, '_experience_type', true) : $default_exp_type;
$experience_type    = esc_html( isset( $localized_exp_type[ $experience_type ] ) ? $localized_exp_type[ $experience_type ] : $experience_type );

$referred_site_url   = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null;
$referred_utm_string = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : null;

set_transient( 'referred_site_url', $referred_site_url, 30 * MINUTE_IN_SECONDS );
set_transient( 'referred_utm_string', $referred_utm_string , 30 * MINUTE_IN_SECONDS );
?>

<div class="erp-recruitment-single" itemscope itemtype="http://schema.org/JobPosting">
    <meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />

    <?php do_action( 'erp_rec_single_job_listing_meta_before' ); ?>

    <ul class="erp-recruitment-meta">
        <li class="employment-type <?php echo sanitize_title( $employment_type ); ?>" itemprop="employmentType">
            <?php echo isset( $employment_types[ $employment_type ] ) ? $employment_types[ $employment_type ] : ''; ?>

            <?php if ( get_post_meta( $job_id, '_remote_job', true) == 1 ) : ?>
                <small class="employment-remote"><?php _e( '(allows remote)','erp-pro' ); ?></small>
            <?php endif;?>
        </li>

        <?php if ( !empty( $location ) ) { ?>
        <li class="erp-recruitment-location" itemprop="jobLocation">
            <span class="rec-icon-location"></span>
            <?php echo $location; ?>
        </li>
        <?php } ?>

        <li class="erp-recruitment-vacancy">
            <span class="rec-icon-users"></span>
            <?php printf( __('No. of Vacancies: %s', 'erp-pro'), $number_of_vacancy ); ?>
        </li>

        <?php if ( $min_experience != '' ) :?>
            <li>
                <span class="rec-icon-briefcase"></span>
                <?php printf( __( 'Experience: %s', 'erp-pro'), $min_experience ); ?>
                <?php
                if ( ! empty( $experience_type ) ) :
                    printf( ' (%s)', $experience_type );
                endif;

                if ( ! empty( $experience_field ) ) :
                    printf( __( ' in %s', 'erp-pro'), $experience_field );
                endif;
                ?>
            </li>
        <?php endif;?>

        <li class="date-posted" itemprop="datePosted">
            <span class="rec-icon-calendar"></span>
            <date><?php printf( __( 'Posted %s ago', 'erp-pro' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date>
        </li>
    </ul>

    <?php do_action( 'erp_rec_single_job_listing_meta_after' ); ?>

    <div class="erp-recruitment-description" itemprop="description">
        <?php echo apply_filters( 'erp_rec_job_description', get_the_content() ); ?>

        <?php
        if ( $expire_timestamp ) {
            printf( '<p class="job-application-deadline"> ' . __( '<strong><em>Application Deadline</em></strong>: %s', 'erp-pro' ) . '</p>', date_i18n( get_option( 'date_format' ), $expire_timestamp ) );
        }
        ?>
    </div>

    <?php
    $expire_date = date( 'Y-m-d', $expire_timestamp );
    $today       = date( 'Y-m-d', time() );
    if (!$is_set_expire_date || ($expire_timestamp && ( $expire_date >= $today )) ) { ?>
        <div class="erp-recruitment-application">
            <input type="button" class="application_button button" id="btn_apply_job" name="btn_apply_job" value="<?php echo esc_attr( 'Apply for this position', 'erp-pro'); ?>"/>

            <div class="erp-recruitment-from-wrapper" id="job_seeker_table_wrapper">
                <?php include __DIR__ . '/job-application-form.php'; ?>
            </div>

            <div id="jobseeker_insertion_message"></div>
        </div>
    <?php } ?>
</div>

