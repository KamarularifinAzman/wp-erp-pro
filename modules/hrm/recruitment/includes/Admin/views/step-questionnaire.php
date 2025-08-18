<div class="wrap uniq-wrap" id="uniq-wrap">
    <?php echo erp_rec_opening_admin_progressbar( 'questionnaire_selection' ); ?>
    <?php $postid = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;?>
    <div class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Questionnaire selection', 'erp-pro' ); ?></h3>
        <div class="inside" style="overflow-y: hidden;">
            <p class="info-message">
                <?php _e('You can create question sets for your candidates. During filling the application form, candidates will have to answer your selected question sets.<br><b>Please be noted that</b> creating a question set will take you another page. Don\'t worry, this job has been saved already on the available jobs, you can update that job and add questions sets when you are done creating the question sets.', 'erp-pro');?>
            </p>
            <form action="<?php echo admin_url(); ?>" method="post" id="unique-form-customize">
                <label id="label-show-questionnaire"><input type="checkbox" id="show-questionnaire"><?php _e('This job requires question set(s)', 'erp-pro');?></label>
                <div id="step-questionnaire" class="openingform_input_wrapper">
                    <div id="meta-inner-question-left-side">
                        <?php
                        // get questionnaire post types and show in a drop down list
                        $posts = get_posts(array(
                                'post_type'      => 'erp_hr_questionnaire',
                                'post_status'    => 'publish',
                                'posts_per_page' => -1
                            )
                        );
                        if ( isset($posts) ) : ?>
                            <div>
                                <?php if ( is_array($posts) && count($posts) > 0 ) : ?>
                                    <label><?php _e('Please Select Question set:', 'erp-pro');?></label>
                                    <select id="qset">
                                        <?php foreach ( $posts as $p ) : ?>
                                            <?php if ( count(get_post_meta($p->ID, '_erp_hr_questionnaire', true)) > 0 ) : ?>
                                                <option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="add page-title-action page-title-action-q"><?php _e('Add Question Set', 'erp-pro'); ?></span>
                                <?php else : ?>
                                    <label id="no-question-caption"><?php _e('No question set found. Please create a question set first', 'erp-pro'); ?></label>
                                    <a id="create-question-btn" class="button button-hero button-primary alignright" href="<?php echo admin_url('edit.php?post_type=erp_hr_questionnaire');?>"><?php _e('Create question set', 'erp-pro');?></a>
                                <?php endif;?>
                            </div>
                        <?php endif; ?>
                        <span id="here"></span>
                    </div>
                    <?php if ( is_array($posts) && count($posts) > 0 ) : ?>
                        <div id="meta-inner-question-right-side">
                            <a class="button button-hero button-primary alignright" href="<?php echo admin_url('edit.php?post_type=erp_hr_questionnaire');?>"><?php _e('Create question set', 'erp-pro');?></a>
                        </div>
                    <?php endif;?>
                </div>
                <input type="hidden" name="postid" value="<?php echo $postid; ?>">
                <?php wp_nonce_field( 'questionnaire' ); ?>
                <div id="question-next-prev-buttons">
                    <a href="<?php echo erp_rec_url( 'add-opening&action=edit&step=candidate_basic_information&postid='.$postid ); ?>" class="button button-hero"><?php _e('&larr; Back', 'erp-pro');?></a>
                    <input type="submit" name="questionnaire" class="button-primary button button-hero alignright" value="<?php _e( 'Finish', 'erp-pro');?>">
                </div>
            </form>
        </div>
    </div>
</div>
