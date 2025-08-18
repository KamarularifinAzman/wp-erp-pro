<div class="wrap erp wp-erp-wrap">
    <h1><?php _e( 'Job Overview', 'erp-pro' ); ?></h1>
    <div class="postbox">
        <div class="inside">
            <?php $job_id = $_GET['jobid']; ?>
            <h2><?php echo get_the_title( $job_id ); ?></h2>
            <div class="row-separator job-description">
                <?php
                    $job_content = get_post( $job_id );
                    echo ( $job_content->post_content == "" ) ? __( 'No description found!', 'erp-pro' ) : $job_content->post_content ;
                ?>
            </div>

            <div class="row-separator applicants-number">
                <span class="job-template-caption"><?php _e( 'Applicants applied:', 'erp-pro' ); ?></span>
                <?php echo $applicants_number = get_applicants_counter($job_id); ?>
            </div>
            <div class="row-separator job-post-date">
                <span class="job-template-caption"><?php _e( 'Job posted:', 'erp-pro' ); ?></span>
                <?php
                    $job_content = get_post( $job_id );
                    echo erp_format_date( $job_content->post_date );
                ?>
            </div>
            <div class="row-separator job-expire-date">
                <span class="job-template-caption"><?php _e( 'Expire date:', 'erp-pro' ); ?></span>
                <?php
                    $e_date = get_post_meta( $job_id, '_expire_date', true ) ? get_post_meta( $job_id, '_expire_date', true ) : "N/A";
                    echo $e_date == "N/A" ? $e_date : erp_format_date( $e_date );
                ?>
            </div>
            <div class="row-separator days-left">
                <?php
                    $expire_date_output = '';
                    $post_id = $job_id;
                    $remaining_days = 0;
                    $e_date = get_post_meta( $post_id, '_expire_date', true ) ? get_post_meta( $post_id, '_expire_date', true ) : "N/A";
                    if ( $e_date != "N/A" ) {
                        $edata = date( "M, d Y", strtotime( $e_date ) );
                        $future_date = date_create( $e_date );
                        $current_date = date_create( date( 'Y-m-d' ) );
                        if ( 'publish' == get_post_status($post_id) ) {
                            if ( strtotime( date('Y-m-d') ) < strtotime( $e_date ) ) {
                                $rdays = date_diff( $current_date, $future_date );
                                $remaining_days = $rdays->format( "%a" );
                                $expire_date_output = sprintf( '%s<div class="row-actions-days"><span>(in %s)</span></div>', $edata, $remaining_days );
                            } else {
                                $expire_date_output = sprintf( '%s', $edata );
                            }
                        } else {
                            $expire_date_output = sprintf( '%s', $edata );
                        }
                    }
                ?>
                <span class="job-template-caption"><?php echo $remaining_days . __( ' Days left', 'erp-pro' ); ?></span>
            </div>
        </div>
    </div>
</div>
