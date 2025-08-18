<div class='wrap erp-calendar-detail'>
    <h1><?php _e( 'Add candidate', 'erp-pro' ); ?><span class="spinner"></span></h1>
    <div id="dashboard-widgets-wrap" class="erp-grid-container">
        <div class="postbox">
            <div class="inside" style="overflow-y:hidden;">
                <div id="primary" class="content-area">
                    <main id="main" class="site-main" role="main">
                        <h4><?php _e( 'Select Job', 'erp-pro' ); ?></h4>
                        <?php
                        $all_jobs = erp_rec_get_all_jobs();
                        $jobid    = isset( $_GET['jobid'] ) ? $_GET['jobid'] : null;
                        ?>
                        <select name="job_id" tabindex="3" id="cjob_id">
                            <option value="">-- select --</option>
                            <?php foreach ( $all_jobs as $key => $alljobs ) : ?>
                                <option value="<?php echo $alljobs['jobid']; ?>" <?php selected( $alljobs['jobid'], $jobid ) ?>>
                                    <?php echo $alljobs['jobtitle']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        $protocol    = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';
                        $actual_link = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        ?>
                        <input type="hidden" id="hidden-base-url" value="<?php echo erp_rec_url( 'add_candidate' ); ?>">
                        <a id="job-link" class="button button-default" href="<?php echo $actual_link; ?>">
                            <?php _e( 'Show form', 'erp-pro' ); ?>
                        </a>
                        <hr />

                        <?php if ( ! empty( $jobid ) ) : ?>
                            <div id="job_seeker_apply_form_wrapper">
                                <?php
                                global $wpdb;
                                $default_fields             = erp_rec_get_default_fields();
                                $all_personal_fields        = erp_rec_get_personal_fields();
                                $db_choosen_personal_fields = get_post_meta( $jobid, '_personal_fields', true );
                                $postid                     = $jobid;
                                $meta_key                   = '_personal_fields';

                                $personal_field_data = $wpdb->get_var(
                                    $wpdb->prepare( "SELECT meta_value
                                    FROM {$wpdb->prefix}postmeta
                                    WHERE meta_key = %s AND post_id = %d", $meta_key, $postid ) );
                                $personal_field_data = maybe_unserialize( $personal_field_data );

                                // convert object to array
                                $db_choosen_fields_array = [];
                                if ( is_array( $db_choosen_personal_fields ) ) {
                                    foreach ( $db_choosen_personal_fields as $dbf ) {
                                        $db_choosen_fields_array[] = (array) $dbf;
                                    }
                                }

                                ?>
                                <div id="job_seeker_table_wrapper">
                                    <form id="jobseeker_form" method="post" enctype="multipart/form-data">
                                        <h4 class="title"><?php _e( 'Basic information', 'erp-pro' ); ?></h4>
                                        <?php foreach ( $default_fields as $key => $value ) : ?>
                                            <?php if ( $value['type'] == 'text' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $value['label']; ?>
                                                        <?php if ( isset( $value['required'] ) && $value['required'] == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input type="text" class="inputclass <?php echo $value['name']; ?> <?php echo ( $value['required'] == true ) ? 'reqc' : ''; ?>" name="<?php echo $value['name']; ?>" value="" maxlength="50" />
                                                </div>
                                            <?php elseif ( $value['type'] == 'name' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $value['label']; ?>
                                                        <?php if ( isset( $value['required'] ) && $value['required'] == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <span class="rec-clearfix">
                                                        <span class="name-col first-name">
                                                            <input type="text" class="inputclass reqc" name="first_name" value="" placeholder="<?php echo esc_attr( __( 'First Name', 'erp-pro' ) ); ?>" maxlength="50" required />
                                                        </span>
                                                        <span class="name-col last-name">
                                                            <input type="text" class="inputclass reqc" name="last_name" value="" placeholder="<?php echo esc_attr( __( 'Last Name', 'erp-pro' ) ); ?>" maxlength="50" required />
                                                        </span>
                                                    </span>
                                                </div>
                                            <?php elseif ( $value['type'] == 'email' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $value['label']; ?>
                                                        <?php if ( isset( $value['required'] ) && $value['required'] == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input type="email" class="inputclass <?php echo ( $value['required'] == true ) ? 'reqc' : ''; ?>" name="<?php echo $value['name']; ?>" value="" />
                                                </div>
                                            <?php elseif ( $value['type'] == 'file' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $value['label']; ?>
                                                        <?php if ( isset( $value['required'] ) && $value['required'] == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input type="button" class="file_upload button button-default inputclass <?php echo true === $value['required'] ? 'reqc' : ''; ?>" name="<?php echo $value['name'] . '[]'; ?>" value="Select Files" required="required" multiple="multiple" />
                                                </div>
                                            <?php elseif ( $value['type'] == 'date' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $value['label']; ?>
                                                        <?php if ( isset( $value['required'] ) && $value['required'] == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input type="text" class="erp-date-field inputclass <?php echo ( $value['required'] == true ) ? 'reqc' : ''; ?>" name="<?php echo $value['name']; ?>" />
                                                </div>
                                            <?php elseif ( $value['type'] == 'textarea' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $value['label']; ?>
                                                        <?php if ( isset( $value['required'] ) && $value['required'] == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <textarea type="textarea" class="inputclass <?php echo ( $value['required'] == true ) ? 'reqc' : ''; ?>" name="<?php echo $value['name']; ?>"></textarea>
                                                </div>
                                            <?php elseif ( $value['type'] == 'select' ) : ?>
                                                <div class="row">
                                                    <label class="title">
                                                        <?php echo $all_personal_fields[ $personal_data_field ]['label']; ?>
                                                        <?php if ( $personal_data_req == true ) : ?>
                                                            <span class="required">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <?php erp_html_form_input( [
                                                        'label'    => '',
                                                        'name'     => $value['name'],
                                                        'required' => ( ( isset( $value['required'] ) && $value['required'] == true ) ? 'required' : '' ),
                                                        'value'    => '',
                                                        'class'    => 'erp-hrm-select2' . ( $value['required'] == true ) ? 'reqc' : '',
                                                        'type'     => 'select',
                                                        'options'  => [ '' => __( '- Select -', 'erp-pro' ) ] + ( isset( $value['options'] ) ? $value['options'] : [] )
                                                    ] ); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <hr>

                                        <?php if ( is_array( $personal_field_data ) ) : ?>
                                            <?php
                                            $flag_for_additional_title = false;
                                            foreach ( $personal_field_data as $key => $value ) {
                                                if ( json_decode( $value )->showfr == true ) {
                                                    $flag_for_additional_title = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ( $flag_for_additional_title ) : ?>
                                                <h4 class="title"><?php _e( 'Addtional information', 'erp-pro' ); ?></h4>
                                            <?php endif; ?>
                                            <?php foreach ( $personal_field_data as $key => $value ) : ?>
                                                <?php
                                                $personal_data_field      = json_decode( $value )->field;
                                                $personal_data_showfr     = json_decode( $value )->showfr;
                                                $personal_data_req        = json_decode( $value )->req;
                                                $personal_data_field_type = json_decode( $value )->type;
                                                ?>
                                                <?php if ( $personal_data_field_type == 'text' && $personal_data_showfr == true ) : ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo $all_personal_fields[ $personal_data_field ]['label']; ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <input type="text" class="inputclass <?php echo ( $personal_data_req == true ) ? 'reqc' : ''; ?>" name="<?php echo $all_personal_fields[ $personal_data_field ]['name']; ?>" value="" maxlength="50" />
                                                    </div>
                                                <?php elseif ( $personal_data_field_type == 'email' && $personal_data_showfr == true ) : ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo $all_personal_fields[ $personal_data_field ]['label']; ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <input type="email" class="inputclass <?php echo ( $personal_data_req == true ) ? 'reqc' : ''; ?>" name="<?php echo $all_personal_fields[ $personal_data_field ]['name']; ?>" value="" />
                                                    </div>
                                                <?php elseif ( $personal_data_field_type == 'file' && $personal_data_showfr == true ) : ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo $all_personal_fields[ $personal_data_field ]['label']; ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <input type="file" class="inputclass <?php echo ( $personal_data_req == true ) ? 'reqc' : ''; ?>" name="<?php echo $all_personal_fields[ $personal_data_field ]['name']; ?>" />
                                                    </div>
                                                <?php elseif ( $personal_data_field_type == 'date' && $personal_data_showfr == true ) : ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo $all_personal_fields[ $personal_data_field ]['label']; ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <input type="text" class="erp-date-field inputclass <?php echo ( $personal_data_req == true ) ? 'reqc' : ''; ?>" name="<?php echo $all_personal_fields[ $personal_data_field ]['name']; ?>" />
                                                    </div>
                                                <?php elseif ( $personal_data_field_type == 'textarea' && $personal_data_showfr == true ) : ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo $all_personal_fields[ $personal_data_field ]['label']; ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <textarea type="textarea" class="inputclass <?php echo ( $personal_data_req == true ) ? 'reqc' : ''; ?>" name="<?php echo $all_personal_fields[ $personal_data_field ]['name']; ?>"></textarea>
                                                    </div>
                                                <?php elseif ( $personal_data_field_type == 'select' && $personal_data_showfr == true ) : ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo ucfirst( str_replace( '_', ' ', $personal_data_field ) ); ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <?php erp_html_form_input( [
                                                            'label'    => '',
                                                            'name'     => $all_personal_fields[ $personal_data_field ]['name'],//$value['name'],
                                                            'required' => ( ( $personal_data_req == true ) ? 'required' : '' ),
                                                            'value'    => '',
                                                            'class'    => 'erp-hrm-select2' . ( $personal_data_req == true ) ? 'reqc' : '',
                                                            'type'     => 'select',
                                                            'options'  => [ '' => __( '- Select -', 'erp-pro' ) ] + ( isset( $all_personal_fields[ $personal_data_field ]['options'] ) ? $all_personal_fields[ $personal_data_field ]['options'] : [] )
                                                        ] ); ?>
                                                    </div>
                                                <?php elseif ( $personal_data_field_type == 'checkbox' && $personal_data_showfr == true ) : ?>
                                                    <?php $checkbox_name = $personal_data_field; ?>
                                                    <div class="row">
                                                        <label class="title">
                                                            <?php echo ucfirst( str_replace( '_', ' ', $personal_data_field ) ); ?>
                                                            <?php if ( $personal_data_req == true ) : ?>
                                                                <span class="required">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <?php erp_html_form_input( [
                                                            'label'    => '',
                                                            'name'     => $checkbox_name . '[]',
                                                            'required' => '',
                                                            'value'    => '',
                                                            'class'    => '',
                                                            'type'     => 'multicheckbox',
                                                            'options'  => isset( $all_personal_fields[ $personal_data_field ]['options'] ) ? $all_personal_fields[ $personal_data_field ]['options'] : []
                                                        ] ); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php if ( $flag_for_additional_title ) : ?>
                                                <hr>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php wp_nonce_field( 'wp-erp-rec-job-seeker-nonce' ); ?>
                                        <input type="hidden" name="job_id" value="<?php echo $jobid; ?>">
                                        <input type="hidden" name="action" value="wp-erp-rec-job-seeker" />
                                        <input type="submit" class="sub button button-primary" name="submit_app" id="submit_app" value="Submit" />
                                        <span class="spinner"></span>
                                    </form>
                                </div>
                                <div id="jobseeker_insertion_message"></div>
                            </div>

                        <?php endif; ?>
                    </main><!-- .site-main -->
                </div><!-- .content-area -->
            </div><!-- inside -->
        </div><!-- postbox -->
    </div><!-- erp-grid-container -->
</div>
