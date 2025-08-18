<div class="wrap uniq-wrap" id="uniq-wrap">
    <?php echo erp_rec_opening_admin_progressbar( 'candidate_basic_information' ); ?>
    <?php $postid = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;?>
    <div class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Candidate basic information', 'erp-pro' ); ?></h3>
        <div class="inside" style="overflow-y: hidden;">
            <form action="<?php echo erp_rec_url( 'add-opening&action=edit&step=candidate_basic_information' );?>" method="post" id="unique-form-pages">
                <div class="openingform_input_wrapper">
                    <div id="openingform_candidate_basic_information">
                        <p class="info-message"><?php _e('The first 3 fields (Name, Email and Upload CV) are mandatory and will be displayed in candidate application form.', 'erp-pro');?></p>

                        <div class="applicant_require_personal_fields">
                            <label>
                                <?php _e('Name', 'erp-pro');?>
                            </label>
                            <div class="alignright">
                                <label>
                                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                                </label>
                            </div>
                        </div>
                        <div class="applicant_require_personal_fields">
                            <label>
                                <?php _e('Email', 'erp-pro');?>
                            </label>
                            <div class="alignright">
                                <label>
                                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                                </label>
                            </div>
                        </div>
                        <div class="applicant_require_personal_fields">
                            <label>
                                <?php _e('Upload CV', 'erp-pro');?>
                            </label>
                            <div class="alignright">
                                <label>
                                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                                </label>
                            </div>
                        </div>

                        <hr>
                        <?php
                            $db_personal_fields = get_post_meta( $postid, '_personal_fields', true );
                            $fields = erp_rec_get_personal_fields();
                        ?>
                        <div id="label-wrapper">
                            <label class="applicant_check_all"><input id="checkAll" type="checkbox"><?php _e( 'Check All', 'erp-pro' ); ?></label>
                            <label class="applicant_check_all" style="float: right"><input id="checkAllReq" type="checkbox"><?php _e( 'Check All', 'erp-pro' ); ?></label>
                        </div>

                        <div id="sortit">
                            <?php if ( is_array( $db_personal_fields ) && count( $db_personal_fields ) > 0 ) : ?>
                                <?php foreach ( $db_personal_fields as $key => $value ) : ?>
                                    <?php $fArray = [ "field" => json_decode( $value )->field, "type" => json_decode( $value )->type, "req" => json_decode( $value )->req, "showfr" => json_decode( $value )->showfr ]; ?>
                                    <div id="<?php echo htmlspecialchars( json_encode( $fArray ), ENT_QUOTES, 'UTF-8' ); ?>"
                                        class="applicant_personal_fields">
                                        <label>
                                            <?php if ( json_decode( $value )->showfr == true ) : ?>
                                                <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo json_decode( $value )->field;?>" checked="checked">
                                            <?php else : ?>
                                                <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo json_decode( $value )->field;?>">
                                            <?php endif; ?>
                                            <?php echo ucwords( str_replace( '_', ' ', json_decode( $value )->field ) ); ?>
                                        </label>

                                        <div class="alignright">
                                            <label>
                                                <?php if ( json_decode( $value )->req == true ) : ?>
                                                    <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo json_decode( $value )->field;?>" checked="checked">
                                                <?php else : ?>
                                                    <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo json_decode( $value )->field;?>">
                                                <?php endif; ?>
                                                <?php _e( 'This field is required', 'erp-pro' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <?php foreach ( $fields as $key => $field ) : ?>
                                    <?php $fArray = [ "field" => $key, "type" => $field['type'], "req" => false, "showfr" => false ]; ?>
                                    <div id="<?php echo htmlspecialchars( json_encode( $fArray ), ENT_QUOTES, 'UTF-8' );?>" class="applicant_personal_fields">
                                        <label>
                                            <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo $key;?>">
                                            <?php echo ucwords( str_replace( '_', ' ', $key ) ); ?>
                                        </label>

                                        <div class="alignright">
                                            <label>
                                                <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo $key;?>">
                                                <?php _e( 'This field is required', 'erp-pro' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="post_ID" name="postid" value="<?php echo $postid;?>">
                <?php wp_nonce_field( 'candidate_basic_information' ); ?>
                <br style="clear: both">
                <a href="<?php echo erp_rec_url( 'add-opening&action=edit&step=job_information&postid='.$postid ); ?>" class="button button-hero"><?php _e('&larr; Back', 'erp-pro');?></a>
                <input type="submit" name="candidate_basic_information" class="button-primary button button-hero alignright" value="<?php _e( 'Next &rarr;', 'erp-pro');?>">
            </form>
        </div>
    </div>
</div>
