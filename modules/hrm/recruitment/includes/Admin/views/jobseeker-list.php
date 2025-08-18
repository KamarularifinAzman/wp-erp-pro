<?php

use WeDevs\Recruitment\JobSeekerListTable;

$jobid = ( isset( $_GET['jobid'] ) ? $_GET['jobid'] : 0 );
//$total_applicants   = erp_rec_applicant_counter($jobid);
$total_applicants   = erp_rec_get_applicant_counter($jobid);
$all_candidate_link = ( $jobid == 0 ) ? erp_rec_url( 'jobseeker_list' ) : erp_rec_url( 'jobseeker_list&jobid='.$jobid );
?>

<div class="wrap erp-candidate-detail">
    <h1>
        <span class="candidate-title"><?php _e('Candidates', 'erp-pro'); ?></span>
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <span class="job-title"><?php echo get_the_title( $jobid ); ?></span>
        <a id="add_candidate" class="page-title-action" href="<?php echo erp_rec_url( 'add_candidate' );?>">
            <?php _e( 'Add Candidate', 'erp-pro' );?>
        </a>
    </h1>

    <form method="post">
        <div id="dashboard-widgets-wrap">
            <div class="row">
                <div class="col-6">
                    <div class="postbox">
                        <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;">
                            <div id="left-fixed-menu">
                                <ul>
                                    <li>
                                        <?php
                                            $protocol       = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                                            $actual_link    = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                                            $selected_class = ($actual_link == $all_candidate_link) ? 'left-menu-current-item' : '' ;
                                        ?>
                                        <span id="menu-overview" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link;?>"><?php _e('Overview', 'erp-pro');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'hired') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-hired" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=hired';?>"><?php _e('Hired', 'erp-pro');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'rejected') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-rejected" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=rejected';?>"><?php _e('Rejected', 'erp-pro');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'withdrawn') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-withdrawn" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=withdrawn';?>"><?php _e('Withdrawn', 'erp-pro');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'decline_offer') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-decline_offer" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=decline_offer';?>"><?php _e('Declined Offer', 'erp-pro');?></a></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="single-information-container">
                                <div id="candidate-overview-zone">

                                    <div class="filter-box-wrapper">
                                        <a class="filter-box" href="<?php echo $all_candidate_link;?>">
                                            <span class="top-zone-number"><?php echo $total_applicants;?></span>
                                            <span class="footer-zone-text"><?php _e(' Candidates', 'erp-pro'); ?></span>
                                        </a>
                                    </div>

                                    <?php if ( $jobid == 0 ) : ?>
                                        <?php $stages = erp_rec_get_all_stages();
                                        foreach ( $stages as $stage ) : ?>
                                            <div class="filter-box-wrapper">
                                                <a class="filter-box" href="<?php echo erp_rec_url( 'jobseeker_list&filter_stage='.$stage['id'] );?>">
                                                    <span class="top-zone-number">
                                                        <?php echo erp_rec_get_candidate_number_in_this_stages($jobid, $stage['id']);?>
                                                    </span>
                                                    <span class="footer-zone-text"><?php echo $stage['title'];?></span>
                                                </a>
                                            </div>
                                        <?php endforeach;?>
                                    <?php else : ?>
                                    <?php

                                        $stages = erp_rec_get_this_job_stages($jobid);
                                        $stgs   = get_post_meta( $jobid, '_stage', true ) ;

                                        if( ! empty( $stgs ) ) {

                                            if ( ! is_array( $stgs ) ) {
                                                $stgs = unserialize( $stgs );
                                            } else {
                                                $stgs = array_map( function ( $param ) {
                                                    return ( array )json_decode( $param );
                                                }, $stgs);
                                            }

                                            $stgs_blank = [];

                                            foreach( $stgs as $st ) {
                                                if( isset( $st['sid'] ) && isset( $st['selected'] ) && $st['selected'] == true ) {
                                                    $stgs_blank_cur = [
                                                        'stageid'   => $st['sid'],
                                                        'title'     => $st['title'],
                                                    ];
                                                    $stgs_blank[] = $stgs_blank_cur;
                                                }
                                            }
                                            $stages = $stgs_blank;
                                        }

                                        foreach ( $stages as $stage ) : ?>
                                            <div class="filter-box-wrapper">
                                                <a class="filter-box" href="<?php echo erp_rec_url( 'jobseeker_list&jobid='.$jobid.'&filter_stage='.$stage['stageid'] );?>">
                                                    <span class="top-zone-number">
                                                        <?php echo erp_rec_get_candidate_number_in_this_stages($jobid, $stage['stageid']);?>
                                                    </span>
                                                    <span class="footer-zone-text"><?php echo $stage['title'];?></span>
                                                </a>
                                            </div>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                </div>
                                <input type="hidden" name="page" value="jobseeker_list">
                                <?php
                                    $customer_table = new JobSeekerListTable();
                                    $customer_table->prepare_items();
                                    $customer_table->search_box(__('Search', 'erp-pro'), 'erp-recruitment-search');
                                    $customer_table->views();
                                    $customer_table->display();
                                ?>
                            </div>
                        </div><!-- inside -->
                    </div><!-- postbox -->
                </div><!-- col-6 -->
            </div><!-- row -->
        </div><!-- erp-grid-container -->
    </form>
</div>
