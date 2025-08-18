<div class="wrap uniq-wrap" id="uniq-wrap" xmlns:v-on="http://www.w3.org/1999/xhtml">
    <?php echo erp_rec_opening_admin_progressbar( 'hiring_workflow' ); ?>
    <?php $postid = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;?>
    <div class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Hiring stage', 'erp-pro' ); ?></h3>
        <div class="inside" style="overflow-y: hidden;">
            <form id="hiring-workflow-form" action="<?php echo erp_rec_url( 'add-opening&action=edit&step=job_information' ); ?>" method="post" id="unique-form-customize">
                <div id="openingform_stage_handler" class="openingform_input_wrapper">
                    <p class="info-message">
                        <?php _e('Stages below reflect the steps in your hiring process. Coordinator of a stage typically schedules interviews, collects evaluation from interviewers and communicates with the candidate.', 'erp-pro');?>
                    </p>
                    <button style="margin-bottom: 10px;" class="button alignright" v-on:click.prevent="createStage">
                        <i class="fa fa-plus"></i>&nbsp;<?php _e('Add Stage','erp-pro');?>
                    </button>
                    <span class="spinner"></span>
                    <div id="stage-validation-message"></div>
                    <div id="openingform_sortit">
                        <?php
                            $get_stage = erp_rec_get_stages( $postid );
                            $db_stage  = get_post_meta( $postid, '_stage', true );
                        ?>
                        <?php if( is_array( $db_stage ) && count( $db_stage ) > 0 ):?>

                            <?php foreach(  $db_stage as $key => $value ):?>
                                <?php
                                    $stage_array = [ 'sid' => json_decode( $value )->sid, 'title' => json_decode( $value )->title, 'selected' => json_decode( $value )->selected ];
                                ?>
                                <div class="stage-list" id="<?php echo htmlspecialchars( json_encode( $stage_array ), ENT_QUOTES, 'UTF-8' );?>">
                                    <?php //if ( array_key_exists( 'stage_selected', $st ) && is_null( $st['stage_selected'] ) ) { ?>
                                    <?php if ( json_decode( $value )->selected == false ) { ?>
                                        <label><input type="checkbox" name="stage_name[]" value="<?php echo json_decode( $value )->sid;?>"><?php echo json_decode( $value )->title;?></label>
                                    <?php } else { ?>
                                        <label><input type="checkbox" name="stage_name[]" value="<?php echo json_decode( $value )->sid;?>" checked="checked"><?php echo json_decode( $value )->title;?></label>
                                    <?php  } ?>
                                </div>
                            <?php endforeach ?>
                        <?php else: ?>
                            <?php $i = 1; foreach ( $get_stage as $st ) : ?>
                                <?php
                                    $stage_array = [ 'sid' => $st['sid'], 'title' => $st['title'], 'selected' => $st['selected'] ];
                                ?>
                                <div class="stage-list" id="<?php echo htmlspecialchars( json_encode( $stage_array ), ENT_QUOTES, 'UTF-8' );?>">
                                    <?php //if ( array_key_exists( 'stage_selected', $st ) && is_null( $st['stage_selected'] ) ) { ?>
                                    <?php if ( $st['selected'] == false ) { ?>
                                        <label><input type="checkbox" name="stage_name[]" value="<?php echo $st['sid'];?>"><?php echo $st['title'];?></label>
                                    <?php } else { ?>
                                        <label><input type="checkbox" name="stage_name[]" value="<?php echo $st['sid'];?>" checked="checked"><?php echo $st['title'];?></label>
                                    <?php  } ?>
                                </div>
                            <?php $i++; endforeach;?>
                        <?php endif ?>
                    </div>
                </div>
                <input type="hidden" id="postid" name="postid" value="<?php echo $postid;?>">
                <input type="hidden" id="" name="hidden_hiring_workflow" value="hiring_workflow">
                <?php wp_nonce_field( 'hiring_workflow' ); ?>
                <br style="clear: both">
                <a href="<?php echo erp_rec_url( 'add-opening&postid='.$postid ); ?>" class="button button-hero"><?php _e('&larr; Back', 'erp-pro');?></a>
                <input type="submit" id="hiring_workflow" name="hiring_workflow" class="button-primary button button-hero alignright" value="<?php _e( 'Next &rarr;', 'erp-pro');?>">
            </form>
        </div>
    </div>
</div>
