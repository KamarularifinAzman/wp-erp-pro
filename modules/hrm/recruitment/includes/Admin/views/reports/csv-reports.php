<div class="wrap erp-candidate-detail">
    <h1><?php _e( 'Reports', 'erp-pro' ); ?></h1><?php $jobid = ( isset( $_GET['jobid'] ) ? $_GET['jobid'] : 0 ); ?>
    <?php $total_applicants = erp_rec_applicant_counter( $jobid ); ?>
    <div id="dashboard-widgets-wrap" class="erp-grid-container">
        <div class="row">
            <div class="col-6">
                <div class="postbox">
                    <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;min-height: 500px;">
                        <div id="left-fixed-menu">
                            <?php include 'left-fixed-menu.php';?>
                        </div>

                        <div class="single-information-container">
                            <div id="candidate-overview-zone">
                                <h1 style="border-bottom:1px solid #e1e1e1;padding-bottom:15px;margin-bottom:15px;">
                                    <i class="fa fa-file-excel-o">&nbsp;</i><?php _e( 'CSV Report', 'erp-pro' ); ?>
                                </h1>
                            </div>

                            <div class="candidate-job-list">
                                <?php
                                    $url = version_compare( WPERP_VERSION, '1.4.0', '<' ) ? 'admin.php?page=opening_reports' : 'admin.php?page=erp-hr&section=recruitment&sub-section=reports'
                                ?>
                                <form method="post" action="<?php echo admin_url( $url.'&tab=csv_reports' );?>">
                                    <select id="job-title" name="report_type">
                                        <option value="opening_report"><?php _e('Opening report','erp-pro'); ?></option>
                                        <option value="candidate_report"><?php _e('Candidate report','erp-pro'); ?></option>
                                    </select>
                                    <?php erp_html_form_input( array(
                                        'label'       => __( '', 'erp-pro' ),
                                        'name'        => 'from_date',
                                        'id'          => 'from_date',
                                        'placeholder' => 'From',
                                        'value'       => '',
                                        'class'       => 'erp-date-field'
                                    ) ); ?>
                                    <?php erp_html_form_input( array(
                                        'label'       => __( '', 'erp-pro' ),
                                        'name'        => 'to_date',
                                        'id'          => 'to_date',
                                        'placeholder' => 'To',
                                        'value'       => '',
                                        'class'       => 'erp-date-field'
                                    ) ); ?>
                                    <input type="hidden" name="func" value="send-email-with-csv-report">
                                    <input type="submit" class="button button-default" value="<?php _e( 'Generate', 'erp-pro' ); ?>">
                                </form>
                                <div id="email-notification-status">
                                    <?php if( isset($_REQUEST['csv_create']) && $_REQUEST['csv_create'] == '1' ) : ?>
                                        <p class="info-message" style="margin-top: 15px;">
                                            <?php
                                                $author_obj = get_user_by('ID', get_current_user_id());
                                                _e('It will be sent to <strong>'.$author_obj->user_email.'</strong> shortly.<br>','erp-pro');
                                                _e('Please Check your email in some time.','erp-pro');
                                            ?>
                                        </p>
                                    <?php elseif ( isset($_REQUEST['csv_create']) && $_REQUEST['csv_create'] == '0' ) : ?>
                                        <p class="info-message" style="margin-top: 15px;">
                                            <?php _e('Please Check your folder permission, CSV report not created.','erp-pro');?>
                                        </p>
                                    <?php endif;?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- inside -->
                </div>
                <!-- postbox -->
            </div>
            <!-- col-6 -->
        </div>
        <!-- row -->
    </div>
    <!-- erp-grid-container -->
</div>
