<?php
namespace WeDevs\Recruitment;

class AdminMenu {

    public $post_type = 'erp_hr_recruitment';

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ], 11 );
        add_action( 'save_post', [ $this, 'save_recruitment_meta' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'front_end_scripts' ] );
        add_action( 'do_meta_boxes', [ $this, 'do_metaboxes' ] );
        add_action( 'admin_url', [ $this, 'change_url_for_add_new_post' ], 10, 3 );

        add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 2 );
        add_filter( 'the_content', [ $this, 'single_job_content' ] );
        //add_filter( 'post_row_actions', [ $this, 'remove_quick_edit' ], 10, 2 );
        //  add_filter( 'views_edit-' . $this->post_type, [ $this, 'change_create_links' ] );

        add_shortcode( 'erp-job-list', [ $this, 'job_list' ] );
    }

    /**
     * Single job content
     *
     *
     *
     * @return mixed
     */
    public function single_job_content( $content ) {
        global $post;

        if ( $post->post_type == $this->post_type ) {
            ob_start();
            $default_template = WPERP_REC_PATH . '/templates/single-template.php';
            include apply_filters( 'erp_modify_rec_single_template', $default_template );
            $content = ob_get_clean();
        }

        return $content;
    }

    /**
     * shortcode job list callback function
     *
     * @return array
     */
    public function job_list( $atts ) {

        ob_start();
        $default_template = WPERP_REC_PATH . '/templates/shortcode-job-list.php';
        include apply_filters( 'modify_rec_list_template', $default_template );
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Filter admin url for "Add Recruitment" button
     *
     * @return string
     */
    public function change_url_for_add_new_post( $url, $path, $blog_id ) {
        if ( 'post-new.php?post_type=' . $this->post_type === $path ) {
            $url  = admin_url( 'admin.php?page=erp-hr&section=recruitment' );
            $path = 'admin.php?page=erp-hr&section=recruitment';
        }

        return $url;
    }

    /**
     * Save Recruitment post meta
     *
     * @since  0.1
     * @since  1.0.5 Added meta fields for experience field and type information
     *
     * @param integer $post_id
     * @param object  $post
     *
     * @return mixed
     */
    function save_recruitment_meta( $post_id, $post ) {
        global $post;

        if ( ! isset( $_POST['hr_recruitment_meta_action_nonce'] ) ) {
            return $post_id;
        }

        if ( ! wp_verify_nonce( $_POST['hr_recruitment_meta_action_nonce'], 'hr_recruitment_meta_action' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $post_type = get_post_type_object( $post->post_type );

        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return $post_id;
        }

        if ( ! current_user_can( 'manage_recruitment' ) ) {
            return $post_id;
        }

        if ( $post->post_type != $this->post_type ) {
            return;
        }

        // $recruitment_hiring_lead        = ( isset( $_POST['hiring_lead'] ) ) ? $_POST['hiring_lead'] : '';
        $recruitment_department         = ( isset( $_POST['department'] ) ) ? $_POST['department'] : '';
        $recruitment_employment_type    = ( isset( $_POST['employment_type'] ) ) ? $_POST['employment_type'] : '';
        $remote_job                     = ( isset( $_POST['remote_job'] ) ) ? 1 : 0;
        $recruitment_minimum_experience = ( isset( $_POST['minimum_experience'] ) ) ? $_POST['minimum_experience'] : '';
        $experience_field               = ( isset( $_POST['experience_field'] ) ) ? $_POST['experience_field'] : '';
        $experience_type                = ( isset( $_POST['experience_type'] ) ) ? $_POST['experience_type'] : '';
        $location                       = ( isset( $_POST['location'] ) ) ? $_POST['location'] : '';
        $latlocation                    = ( isset( $_POST['latlocation'] ) ) ? $_POST['latlocation'] : '';
        $lnglocation                    = ( isset( $_POST['lnglocation'] ) ) ? $_POST['lnglocation'] : '';
        $state                          = ( isset( $_POST['state'] ) ) ? $_POST['state'] : '';
        $erp_state_text                 = ( isset( $_POST['erp_state_text'] ) ) ? $_POST['erp_state_text'] : '';
        $expire_date                    = ( isset( $_POST['expire_date'] ) ) ? $_POST['expire_date'] : '';
        $stage_name                     = ( isset( $_POST['stage_name'] ) ) ? $_POST['stage_name'] : [];
        $vacancy                        = ( isset( $_POST['vacancy'] ) ) ? $_POST['vacancy'] : 0;

        $street_address   = ( isset( $_POST['street_address'] ) ) ? $_POST['street_address'] : 0;
        $address_locality = ( isset( $_POST['address_locality'] ) ) ? $_POST['address_locality'] : 0;
        $postal_code      = ( isset( $_POST['postal_code'] ) ) ? $_POST['postal_code'] : 0;
        $address_country  = ( isset( $_POST['address_country'] ) ) ? $_POST['address_country'] : 0;
        $currency         = ( isset( $_POST['currency'] ) ) ? $_POST['currency'] : 0;
        $salary           = ( isset( $_POST['salary'] ) ) ? $_POST['salary'] : 0;
        $salary_type      = ( isset( $_POST['salary_type'] ) ) ? $_POST['salary_type'] : 0;

        if ( ! empty( $salary ) && ! erp_is_valid_currency_amount( $salary ) ) {
            $salary = 0;
        }

        $job_data['job_title'] = get_the_title( $post_id );
        // $job_data['hiring_lead']        = $recruitment_hiring_lead;
        $job_data['department']         = $recruitment_department;
        $job_data['employment_type']    = $recruitment_employment_type;
        $job_data['remote_job']         = $remote_job;
        $job_data['minimum_experience'] = $recruitment_minimum_experience;
        $job_data['experience_field']   = $experience_field;
        $job_data['experience_type']    = $experience_type;
        $job_data['vacancy']            = $vacancy;


        // if ( isset($recruitment_hiring_lead) && $recruitment_hiring_lead == '' ) {
        //     //$location = add_query_arg( array( 'post_error_message' => 1, 'action' => 'edit' ), admin_url( 'post.php' ) );
        //     wp_redirect( admin_url( 'post.php?post='.$post_id.'&post_error_message=1&action=edit' ) );
        //     //wp_redirect( $location );
        //     exit();
        // }

        // update_post_meta( $post_id, '_hiring_lead', $recruitment_hiring_lead );
        update_post_meta( $post_id, '_department', $recruitment_department );
        update_post_meta( $post_id, '_employment_type', $recruitment_employment_type );
        update_post_meta( $post_id, '_remote_job', $remote_job );
        update_post_meta( $post_id, '_minimum_experience', $recruitment_minimum_experience );
        update_post_meta( $post_id, '_experience_field', $experience_field );
        update_post_meta( $post_id, '_experience_type', $experience_type );
        update_post_meta( $post_id, '_location', $location );
        update_post_meta( $post_id, '_latlocation', $latlocation );
        update_post_meta( $post_id, '_lnglocation', $lnglocation );
        update_post_meta( $post_id, '_state', $state );
        update_post_meta( $post_id, '_state_text', $erp_state_text );
        update_post_meta( $post_id, '_expire_date', $expire_date );
        update_post_meta( $post_id, '_vacancy', $vacancy );


        update_post_meta( $post_id, '_street_address', $street_address );
        update_post_meta( $post_id, '_address_locality', $address_locality );
        update_post_meta( $post_id, '_postal_code', $postal_code );
        update_post_meta( $post_id, '_address_country', $address_country );
        update_post_meta( $post_id, '_currency', $currency );
        update_post_meta( $post_id, '_salary', $salary );
        update_post_meta( $post_id, '_salary_type', $salary_type );


        // update post meta for personal fields
        $efields            = isset( $_POST['efields'] ) ? $_POST['efields'] : [];
        $req                = isset( $_POST['req'] ) ? $_POST['req'] : [];
        $db_personal_fields = get_post_meta( $post->ID, '_personal_fields', true );
        $personal_fields    = [];
        if ( is_array( $db_personal_fields ) && count( $db_personal_fields ) > 0 ) {
            foreach ( $db_personal_fields as $key => $value ) {
                $pfield = json_decode( $value )->field;
                if ( is_array( $efields ) ) {
                    if ( in_array( $pfield, $efields, true ) ) {
                        if ( is_array( $req ) ) {
                            if ( in_array( $pfield, $req, true ) ) {
                                $personal_fields[] = json_encode( [ 'field' => json_decode( $value )->field, 'type' => json_decode( $value )->type, 'req' => true, 'showfr' => true ] );
                            } else {
                                $personal_fields[] = json_encode( [ 'field' => json_decode( $value )->field, 'type' => json_decode( $value )->type, 'req' => false, 'showfr' => true ] );
                            }
                        } else {
                            $personal_fields[] = json_encode( [ 'field' => json_decode( $value )->field, 'type' => json_decode( $value )->type, 'req' => false, 'showfr' => true ] );
                        }
                    } else {
                        $personal_fields[] = json_encode( [ 'field' => json_decode( $value )->field, 'type' => json_decode( $value )->type, 'req' => false, 'showfr' => false ] );
                    }
                }
            }
        }

        update_post_meta( $post_id, '_personal_fields', $personal_fields );
        // update stages
        erp_rec_update_stage( $stage_name, $post_id );
        // update questionnaire
        $questions = ( isset( $_POST['questions'] ) ? $_POST['questions'] : '' );
        update_post_meta( $post_id, '_erp_hr_questionnaire', $questions );

        do_action( 'erp_rec_opened_recruitment', $job_data );

        $get_stage = erp_rec_get_stages( $post_id );
        $get_stage = array_map( function ( $param ) {
            return json_encode( $param );
        }, $get_stage );

        $get_stage_meta      = get_post_meta( $post_id, '_stage', true );
        $get_stage_meta_diff = array_diff( $get_stage_meta, $get_stage );
        foreach ( $get_stage_meta_diff as $gsmd_key => $gsmd ) {
            if ( strpos( $gsmd, 'true' ) ) {
                $get_stage_meta[ $gsmd_key ] = str_replace( 'true', 'false', $gsmd );
            } else {
                $get_stage_meta[ $gsmd_key ] = str_replace( 'false', 'true', $gsmd );
            }
        }
        update_post_meta( $post_id, '_stage', $get_stage_meta );
    }

    /**
     * initialize meta boxes for recruitment post type
     *
     * @return void
     */
    public function do_metaboxes() {
        add_meta_box( 'erp-hr-recruitment-meta-box', __( 'Recruitment Settings', 'erp-pro' ),
            [ $this, 'meta_boxes_cb' ], $this->post_type, 'advanced', 'high' );

        add_meta_box( 'erp-hr-recruitment-experience-meta-box', __( 'Experience Settings', 'erp-pro' ),
            [ $this, 'meta_boxes_experience_cb' ], $this->post_type, 'advanced', 'high' );

        add_meta_box( 'erp-hr-recruitment-schema-meta-box', __( 'Recruitment Schema Settings <span style="font-size: 12px;">( Optional )</span>', 'erp-pro' ),
            [ $this, 'meta_boxes_schema_cb' ], $this->post_type, 'advanced', 'low' );

        add_meta_box( 'erp-hr-applicant-personal-fields', __( 'Applicant Personal Fields', 'erp-pro' ),
            [ $this, 'personal_fields' ], $this->post_type, 'advanced', 'low' );

        add_meta_box( 'erp-hr-applicantion-stage', __( 'Hiring workflow', 'erp-pro' ),
            [ $this, 'edit_stage' ], $this->post_type, 'advanced', 'low' );

        add_meta_box( 'erp-hr-applicant-questionnaire', __( 'Set Question For This Job', 'erp-pro' ),
            [ $this, 'applicant_questionnaire' ], $this->post_type, 'advanced', 'low' );
    }

    /**
     * recruitment metabox callback function
     *
     * @return
     */
    public function meta_boxes_cb( $post_id ) {
        global $post;

        $employees        = erp_hr_get_employees( [ 'no_object' => true ] );
        $departments      = erp_hr_get_departments_dropdown_raw();
        $employment_types = erp_hr_get_employee_types();
        // $get_hiring_lead        = get_post_meta( $post->ID, '_hiring_lead', true );
        $get_department      = get_post_meta( $post->ID, '_department', true );
        $get_employment_type = get_post_meta( $post->ID, '_employment_type', true );
        $get_remote_job      = get_post_meta( $post->ID, '_remote_job', true );
        $get_location        = get_post_meta( $post->ID, '_location', true );
        $get_state           = get_post_meta( $post->ID, '_state', true );
        $get_state_text      = get_post_meta( $post->ID, '_state_text', true );
        $get_expire_date     = get_post_meta( $post->ID, '_expire_date', true );
        $get_vacancy         = get_post_meta( $post->ID, '_vacancy', true );
        ?>
        <table class="form-table erp-hr-recruitment-meta-wrap-table" xmlns:v-on="http://www.w3.org/1999/xhtml">
        <tr>
            <td width="50%">
                <label><?php _e( 'Employment Type', 'erp-pro' ); ?></label> <select name="employment_type" class="full-regular-text">
                    <option></option><?php foreach ( $employment_types as $key => $value ) { ?>
                    <option value='<?php echo $key; ?>'<?php if ( $get_employment_type == $key ): ?> selected="selected"<?php endif; ?>>
                        <?php echo $value; ?>
                        </option><?php } ?>
                </select>
            </td>
            <td width="50%">
                <label>
                    <input type="checkbox" name="remote_job" <?php echo ( $get_remote_job == 1 ) ? 'checked' : ''; ?> />
                    <?php _e( 'Remote working is an option for this opening', 'erp-pro' ); ?>
                </label>
            </td>
        </tr>

        <tr>
            <td width="50%">
                <label><?php _e( 'Department', 'erp-pro' ); ?></label>

                <select name="department" class="full-regular-text">
                    <option></option>
                    <?php foreach ( $departments as $id => $dept ) : ?>
                        <option value="<?php echo esc_attr( $id ); ?>"
                            <?php echo ( $get_department == $id ) ? 'selected' : '' ?>>
                            <?php echo $dept; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td width="50%">
                <label><?php _e( 'Location', 'erp-pro' ); ?></label>
                <input class="full-regular-text" type="text" id="glocation" name="location" value="<?php echo $get_location; ?>" />
                <input type="hidden" id="latlocation" name="latlocation" value="" />
                <input type="hidden" id="lnglocation" name="lnglocation" value="" />
            </td>
        </tr>

        <tr>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label'       => __( 'Submission Deadline', 'erp-pro' ),
                        'name'        => 'expire_date',
                        'value'       => ( $get_expire_date == '' ) ? '' : $get_expire_date,
                        'type'        => 'text',
                        'class'       => 'erp-date-field-expire-date full-regular-text',
                        'custom_attr' => [
                            'autocomplete' => 'off'
                        ]

                    ]
                );
                ?>
            </td>
            <td width="50%">
                <label><?php _e( 'Vacancy', 'erp-pro' ); ?></label>
                <input class="full-regular-text" type="text" id="vacancy" name="vacancy" value="<?php _e( $get_vacancy, 'erp-pro' ); ?>" maxlength="2" />
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td width="50%"></td>
            <td width="50%"></td>
        </tr>
        <tr></tr>

        </table><?php wp_nonce_field( 'hr_recruitment_meta_action', 'hr_recruitment_meta_action_nonce' );
    }

    /**
     * Recruitment metabox experience callback
     *
     * @since 1.0.5
     *
     * @param int $post_id
     *
     * @return void
     */
    public function meta_boxes_experience_cb( $post_id ) {
        global $post;

        $minimum_experience     = erp_rec_get_recruitment_minimum_experience();
        $get_minimum_experience = get_post_meta( $post->ID, '_minimum_experience', true );
        $experience_field       = get_post_meta( $post->ID, '_experience_field', true );
        $experience_types_opt   = [ 'Preferred' => __( 'Preferred', 'erp-pro' ), 'Required' => __( 'Required', 'erp-pro' ) ];
        $experience_type        = get_post_meta( $post->ID, '_experience_type', true );
        ?>
        <table class="form-table erp-hr-recruitment-meta-wrap-table" xmlns:v-on="http://www.w3.org/1999/xhtml">
            <tr>
                <td width="50%">
                    <label><?php _e( 'Minimum Experience', 'erp-pro' ); ?></label>
                    <select name="minimum_experience" class="full-regular-text">
                        <option></option>
                        <?php foreach ( $minimum_experience as $key => $value ) : ?>
                            <option value='<?php echo $key; ?>'<?php if ( $get_minimum_experience == $key ): ?> selected="selected"<?php endif; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td width="50%">
                    <label><?php _e( 'Area of Experience', 'erp-pro' ); ?></label>
                    <input class="full-regular-text" type="text" id="experience_field" name="experience_field" value="<?php echo $experience_field; ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label><?php _e( 'Experience Type', 'erp-pro' ); ?></label>
                    <?php foreach ( $experience_types_opt as $et_key => $et_value ) : ?>
                        <?php $checked = $experience_type === $et_key ? 'checked' : ''; ?>
                        <span class="exp-type-list" style="margin: 0 10px 0 0;">
                            <input type="radio" class="exp-type-list" name="experience_type" id="experience_type_<?php echo esc_attr( strtolower( $et_key ) ); ?>" value="<?php echo esc_attr( strtolower( $et_key ) ); ?>" <?php echo esc_attr( $checked ); ?>>
                            <?php echo esc_html( $et_value ); ?>
                        </span>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * recruitment metabox schema callback function
     *
     * @return
     */
    public function meta_boxes_schema_cb( $post_id ) {
        global $post;


        $street_address   = get_post_meta( $post->ID, '_street_address', true );
        $address_locality = get_post_meta( $post->ID, '_address_locality', true );
        $postal_code      = get_post_meta( $post->ID, '_postal_code', true );
        $address_country  = get_post_meta( $post->ID, '_address_country', true );
        $currency         = get_post_meta( $post->ID, '_currency', true );
        $salary           = get_post_meta( $post->ID, '_salary', true );
        $salary_type      = get_post_meta( $post->ID, '_salary_type', true );


        ?>
        <table class="form-table erp-hr-recruitment-meta-wrap-table" xmlns:v-on="http://www.w3.org/1999/xhtml">

        <tr>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label' => __( 'Street Address', 'erp-pro' ),
                        'name'  => 'street_address',
                        'value' => ( $street_address == '' ) ? '' : $street_address,
                        'type'  => 'text',
                        'class' => 'full-regular-text'
                    ]
                );
                ?>
            </td>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label'       => __( 'Salary', 'erp-pro' ),
                        'name'        => 'salary',
                        'value'       => $salary,
                        'type'        => 'number',
                        'class'       => 'full-regular-text',
                        'custom_attr' => [
                            'min'  => '0.1',
                            'step' => '0.0001',
                        ],
                    ]
                );
                ?>
            </td>
        </tr>

        <tr>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label' => __( 'Locality', 'erp-pro' ),
                        'name'  => 'address_locality',
                        'value' => ( $address_locality == '' ) ? '' : $address_locality,
                        'type'  => 'text',
                        'class' => 'full-regular-text'
                    ]
                );
                ?><br>
                <span style="font-style: italic;"><?php _e( 'A particular area of a state or country', 'erp-pro' ); ?></span>
            </td>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label'   => __( 'Currency', 'erp-pro' ),
                        'name'    => 'currency',
                        'value'   => ( $currency == '' ) ? '' : $currency,
                        'type'    => 'select',
                        'class'   => 'full-regular-text',
                        'options' => erp_get_currency_list_with_symbol(),
                        'default' => 'USD'
                    ]
                );
                ?>
            </td>
        </tr>

        <tr>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label' => __( 'Postal Code', 'erp-pro' ),
                        'name'  => 'postal_code',
                        'value' => ( $postal_code == '' ) ? '' : $postal_code,
                        'type'  => 'text',
                        'class' => 'full-regular-text'
                    ]
                );
                ?>
            </td>
            <td width="50%">
                <?php erp_html_form_input(
                    [
                        'label'   => __( 'Salary Type', 'erp-pro' ),
                        'name'    => 'salary_type',
                        'value'   => $salary_type,
                        'type'    => 'select',
                        'class'   => 'full-regular-text',
                        'options' => erp_hr_get_pay_type(),
                    ]
                );
                ?>
            </td>
        </tr>

        <tr>
            <td width="50%">
                <?php $country = \WeDevs\ERP\Countries::instance(); ?>
                <?php erp_html_form_input(
                    [
                        'label'   => __( 'Country', 'erp-pro' ),
                        'name'    => 'address_country',
                        'value'   => ( $address_country == '' ) ? '' : $address_country,
                        'type'    => 'select',
                        'class'   => 'full-regular-text',
                        'options' => $country->get_countries( - 1 )
                    ]
                );
                ?>
            </td>
            <td width="50%">

            </td>
        </tr>


        <tr></tr>
        <tr></tr>
        <tr>
            <td width="50%"></td>
            <td width="50%"></td>
        </tr>
        <tr>
            <td width="100%" colspan="2"><?php echo _e( '<b style="font-style: italic">Note : These boxed information will only be used as <a target=\'_blank\' href="https://developers.google.com/search/docs/data-types/job-posting">meta information for search engines</a>. Users will never see these information. </b>', 'erp' ); ?></td>
        </tr>
        <tr></tr>

        </table><?php wp_nonce_field( 'hr_recruitment_meta_action', 'hr_recruitment_meta_action_nonce' );
    }

    /**
     * recruitment personal fields
     *
     * @return void
     */
    public function personal_fields( $post_id ) {
        global $post, $fArray;

        $fields             = erp_rec_get_personal_fields();
        $db_personal_fields = get_post_meta( $post->ID, '_personal_fields', true );
        // check has new extra field exist or not
        $extra_fields = get_option( 'erp-employee-fields' );
        $new_fields   = [];
        $count        = 0;
        if ( is_array( $extra_fields ) ) {
            foreach ( $extra_fields as $single ) {

                $new_fields[ $count ] = [
                    'label'       => $single['label'],
                    'name'        => $single['name'],
                    'section'     => $single['section'],
                    'icon'        => $single['icon'],
                    'required'    => $single['required'],
                    'type'        => $single['type'],
                    'placeholder' => $single['placeholder'],
                    'helptext'    => $single['helptext'],
                ];

                if ( is_array( $single['options'] ) && ! empty( $single['options'] ) ) {
                    foreach ( $single['options'] as $opt ) {
                        $new_fields[ $count ]['options'][ $opt['value'] ] = $opt['text'];
                    }
                }

                $count ++;
            }
        }

        if ( is_array( $db_personal_fields ) ) { // if user full filled candidate basic info during step filling
            if ( is_array( $new_fields ) ) { // check if new fields has or not
                $db_personal_fields_name = [];

                foreach ( $db_personal_fields as $dbf ) { // make an array to match new fields exist in personal fields or not
                    array_push( $db_personal_fields_name, json_decode( $dbf )->field );
                }

                foreach ( $new_fields as $single_field ) {
                    if ( ! in_array( $single_field['name'], $db_personal_fields_name ) ) {
                        $push_new_field = json_encode( [
                            'field'  => $single_field['name'],
                            'label'  => $single_field['label'],
                            'type'   => $single_field['type'],
                            'req'    => filter_var( $single_field['required'], FILTER_VALIDATE_BOOLEAN ),
                            'showfr' => true,
                        ] );

                        array_push( $db_personal_fields, $push_new_field );
                    }
                }
            }
            update_post_meta( $post->ID, '_personal_fields', $db_personal_fields );
        } else { // if user did not full fill candidate basic information step at the first time then fields will come from default array = $personal_fields
            $default_personal_fields = [];
            foreach ( $fields as $default_field ) { // making the json and push to a new array as personal fields
                $push_new_field = json_encode( [ 'field' => $default_field['name'], 'label' => $default_field['label'], 'type' => $default_field['type'], 'req' => $default_field['required'], 'showfr' => true ] );
                array_push( $default_personal_fields, $push_new_field );
            }
            // update_post_meta( $post->ID, '_personal_fields', $default_personal_fields );
            $db_personal_fields = $default_personal_fields;
        }

        ?>

        <div class="applicant_personal_mandatory_fields">
            <label>
                <?php _e( 'First Name', 'erp-pro' ); ?>
            </label>

            <div class="alignright">
                <label>
                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                </label>
            </div>
        </div>
        <div class="applicant_personal_mandatory_fields">
            <label>
                <?php _e( 'Last Name', 'erp-pro' ); ?>
            </label>

            <div class="alignright">
                <label>
                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                </label>
            </div>
        </div>
        <div class="applicant_personal_mandatory_fields">
            <label>
                <?php _e( 'Email', 'erp-pro' ); ?>
            </label>

            <div class="alignright">
                <label>
                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                </label>
            </div>
        </div>
        <div class="applicant_personal_mandatory_fields">
            <label>
                <?php _e( 'Upload CV', 'erp-pro' ); ?>
            </label>

            <div class="alignright">
                <label>
                    <?php _e( 'This field is required', 'erp-pro' ); ?>
                </label>
            </div>
        </div>
        <hr>

        <div id="label-wrapper">
            <label class="applicant_check_all"><input id="checkAll" type="checkbox"><?php _e( 'Check All', 'erp-pro' ); ?></label>
            <label class="applicant_check_all" style="float: right"><input id="checkAllReq" type="checkbox"><?php _e( 'Check All', 'erp-pro' ); ?></label>
        </div>

        <div id="sortit">
            <?php if ( count( $db_personal_fields ) > 0 && is_array( $db_personal_fields ) ) : ?>
                <?php foreach ( $db_personal_fields as $key => $value ) :
                    $fArray = [
                        'field'  => json_decode( $value )->field,
                        'label'  => isset( json_decode( $value )->label ) ? json_decode( $value )->label : '',
                        'type'   => json_decode( $value )->type,
                        'req'    => json_decode( $value )->req,
                        'showfr' => json_decode( $value )->showfr
                    ];
                    ?>
                    <div id="<?php echo htmlspecialchars( json_encode( $fArray ), ENT_QUOTES, 'UTF-8' ); ?>" class="applicant_personal_fields">
                        <label>
                            <?php if ( json_decode( $value )->showfr == true ) : ?>
                                <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo json_decode( $value )->field; ?>" checked="checked">
                            <?php else : ?>
                                <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo json_decode( $value )->field; ?>">
                            <?php endif; ?>

                            <?php echo ! empty( json_decode( $value )->label ) ? json_decode( $value )->label : ucwords( str_replace( '_', ' ', json_decode( $value )->field ) ); ?>
                        </label>

                        <div class="alignright">
                            <label>
                                <?php if ( json_decode( $value )->req == true ) : ?>
                                    <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo json_decode( $value )->field; ?>" checked="checked">
                                <?php else : ?>
                                    <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo json_decode( $value )->field; ?>">
                                <?php endif; ?>

                                <?php _e( 'This field is required', 'erp-pro' ); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php
    }

    /*
     * update stages
     * para
     * return
     */
    public function edit_stage() {
        ?>
        <div id="update_openingform_stage_handler" class="openingform_input_wrapper">
        <button style="margin-bottom: 10px;" class="button alignright" v-on:click.prevent="createStage">
            <i class="fa fa-plus"></i>&nbsp;<?php _e( 'Add Stage', 'erp-pro' ); ?>
        </button>
        <div id="stage-validation-message"></div>
        <div id="openingform_sortit_edit_mode">
            <?php
            $get_stage = erp_rec_get_stages( get_the_ID() );
            $db_stage  = get_post_meta( get_the_ID(), '_stage', true );
            ?>
            <?php if ( is_array( $db_stage ) && count( $db_stage ) > 0 ): ?>
                <?php foreach ( $db_stage as $key => $value ): ?>
                    <?php
                    $data     = (array) json_decode( $value );
                    $sid      = ! empty( $data['sid'] ) ? $data['sid'] : '0';
                    $title    = ! empty( $data['title'] ) ? $data['title'] : '';
                    $selected = ! empty( $data['selected'] ) ? $data['selected'] : false;

                    if ( $sid == 0 ) {
                        continue;
                    }

                    ?>
                    <?php $stage_array = [ 'sid' => $sid, 'title' => $title, 'selected' => $selected ]; ?>
                    <div class="stage-list" id="<?php echo htmlspecialchars( json_encode( $stage_array ), ENT_QUOTES, 'UTF-8' ); ?>">
                        <?php if ( $selected == false ) : ?>
                            <label>
                            <input type="checkbox" name="stage_name[]" value="<?php echo $sid ?>"><?php echo $title ?>

                            </label><?php else: ?><label>
                            <input type="checkbox" name="stage_name[]" value="<?php echo $sid; ?>" checked="checked"><?php echo $title; ?>

                            </label>
                        <?php endif; ?>
                    </div>
                <?php endforeach ?>
            <?php else: ?>
                <?php foreach ( $get_stage as $st ) : ?>
                    <?php $stage_array = [ 'sid' => $st['sid'], 'title' => $st['title'], 'selected' => $st['selected'] ] ?>
                    <div class="stage-list" id="<?php echo htmlspecialchars( json_encode( $stage_array ), ENT_QUOTES, 'UTF-8' ); ?>">
                        <?php if ( $st['selected'] == false ) : ?>
                            <label>
                            <input type="checkbox" name="stage_name[]" value="<?php echo $st['sid']; ?>"><?php echo $st['title']; ?>
                            <!-- <input type="hidden" class="candidate_number" value="<?php echo $st['candidate_number']; ?>"> -->
                            </label><?php else: ?><label>
                            <input type="checkbox" name="stage_name[]" value="<?php echo $st['sid']; ?>" checked="checked"><?php echo $st['title']; ?>
                            <!-- <input type="hidden" class="candidate_number" value="<?php echo isset( $st['candidate_number'] ) ? $st['candidate_number'] : 0; ?>"> -->
                            </label>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif ?>
            <input type="hidden" id="post_ID" value="<?php echo get_the_ID(); ?>">
        </div>
        <span class="spinner"></span>
        </div><?php
    }

    /**
     * recruitment applicant questionnaire
     *
     * @return void
     */
    public function applicant_questionnaire( $post_id ) {
        global $post;
        $localize_scripts = [ 'qset' => get_post_meta( $post->ID, '_erp_hr_questionnaire', true ) ];
        wp_localize_script( 'erp-recruitment-app-script', 'wpErpHrQuestionnaire', $localize_scripts );
        ?>
        <div id="meta_inner">
        <?php
        // get questionnaire post types and show in a drop down list
        $posts             = get_posts( [ 'post_type' => 'erp_hr_questionnaire', 'post_status' => 'publish', 'posts_per_page' => - 1 ] );
        $get_questionnaire = get_post_meta( $post->ID, '_erp_hr_questionnaire', true );
        ?>
        <div>
            <label><?php _e( 'Please Select Question set:', 'erp-pro' ); ?></label>
            <select id="qset">
                <?php foreach ( $posts as $p ) : ?><?php if ( count( get_post_meta( $p->ID, '_erp_hr_questionnaire', true ) ) > 0 ) : ?>
                    <option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option><?php endif; ?><?php endforeach; ?>
            </select>
            <span class="add page-title-action page-title-action-q"><?php _e( 'Add Question Set', 'erp-pro' ); ?></span>
        </div>
        <span id="here"></span>
        </div><?php
    }

    /**
     * Setting screen option.
     *
     * @since 1.0.0
     *
     * @param string $status , $option, $value
     *
     * @return string
     */
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    /**
     * Register the admin menu.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_menu() {

        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            $this->admin_menu_before_1_4_0();
        } else {
            $this->admin_menu_after_1_4_0();
        }
    }

    /**
     * Admin menu before version 1.4.0
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_menu_before_1_4_0() {

        $capability = 'manage_recruitment';

        add_menu_page( __( 'Recruitment', 'erp-pro' ),
            __( 'Recruitment', 'erp-pro' ),
            $capability,
            'erp-hr-recruitment', [
                $this,
                'job_opening_list_page'
            ], 'dashicons-businessman' );

        //        add_menu_page( __( 'Recruitment', 'erp-pro' ), __( 'Recruitment', 'erp-pro' ), $capability,
        //            'edit.php?post_type=$this->post_type', '', 'dashicons-businessman' );

        add_submenu_page( 'erp-hr-recruitment', __( 'Job Opening', 'erp-pro' ), __( 'Job Opening', 'erp-pro' ),
            $capability, 'erp-hr-recruitment', [ $this, 'job_opening_list_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Add Opening', 'erp-pro' ), __( 'Add Opening', 'erp-pro' ),
            $capability, 'add-opening', [ $this, 'job_description_step' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Candidates', 'erp-pro' ), __( 'Candidates', 'erp-pro' ),
            $capability, 'jobseeker_list', [ $this, 'candidate_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Calendar', 'erp-pro' ), __( 'Calendar', 'erp-pro' ),
            $capability, 'todo-calendar', [ $this, 'todo_calendar_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Question Sets', 'erp-pro' ), __( 'Question Sets', 'erp-pro' ),
            $capability, 'edit.php?post_type=erp_hr_questionnaire' );

        add_submenu_page( 'erp-hr-recruitment', __( 'Jobseeker List', 'erp-pro' ), __( 'Job Seeker List', 'erp-pro' ),
            $capability, 'jobseeker_list', [ $this, 'jobseeker_list' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Applicant Details', 'erp-pro' ), __( 'Applicant Details', 'erp-pro' ),
            $capability, 'applicant_detail', [ $this, 'applicant_detail' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Jobseeker List to email', 'erp-pro' ), __( 'Job Seeker List to email', 'erp-pro' ),
            $capability, 'jobseeker_list_email', [ $this, 'jobseeker_list_email_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Reports', 'erp-pro' ), __( 'Reports', 'erp-pro' ),
            $capability, 'opening_reports', [ $this, 'opening_reports_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Candidate Reports', 'erp-pro' ), __( 'Candidate Reports', 'erp-pro' ),
            $capability, 'candidate_reports', [ $this, 'candidate_reports_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'CSV Reports', 'erp-pro' ), __( 'CSV Reports', 'erp-pro' ),
            $capability, 'csv_reports', [ $this, 'csv_reports_page' ] );

        add_submenu_page( 'erp-hr-recruitment', __( 'Add candidate', 'erp-pro' ), __( 'Add candidate', 'erp-pro' ),
            $capability, 'add_candidate', [ $this, 'add_candidate' ] );

        add_submenu_page( 'options.php', __( 'Make Employee', 'erp-pro' ), __( 'Make Employee', 'erp-pro' ),
            $capability, 'make_employee', [ $this, 'make_employee' ] );
    }

    /**
     * Admin menu after version 1.4.0
     *
     * @since 1.4.1
     *
     * @return void
     */
    public function admin_menu_after_1_4_0() {

        $capability = 'manage_recruitment';

        erp_add_menu( 'hr', [
            'title'      => __( 'Recruitment', 'erp' ),
            'slug'       => 'recruitment',
            'capability' => $capability,
            'callback'   => [ $this, 'job_opening_list_page' ],
            'position'   => 35,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Job Opening', 'erp-pro' ),
            'slug'       => 'job-opening',
            'capability' => $capability,
            'callback'   => [ $this, 'job_opening_list_page' ],
            'position'   => 1,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add Opening', 'erp-pro' ),
            'slug'       => 'add-opening',
            'capability' => $capability,
            'callback'   => [ $this, 'job_description_step' ],
            'position'   => 5,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'       => __( 'Question Sets', 'erp-pro' ),
            'slug'        => '',
            'capability'  => $capability,
            'callback'    => '',
            'direct_link' => admin_url( 'edit.php?post_type=erp_hr_questionnaire' ),
            'position'    => 10,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Candidates', 'erp-pro' ),
            'slug'       => 'jobseeker_list',
            'capability' => $capability,
            'callback'   => [ $this, 'candidate_page' ],
            'position'   => 15,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Calendar', 'erp-pro' ),
            'slug'       => 'todo-calendar',
            'capability' => $capability,
            'callback'   => [ $this, 'todo_calendar_page' ],
            'position'   => 20,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Reports', 'erp-pro' ),
            'slug'       => 'reports',
            'capability' => $capability,
            'callback'   => [ $this, 'opening_reports_page' ],
            'position'   => 25,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add candidate', 'erp-pro' ),
            'slug'       => 'add_candidate',
            'capability' => $capability,
            'callback'   => [ $this, 'add_candidate' ],
            'position'   => 16,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( '', 'erp-pro' ),
            'slug'       => 'jobseeker_list_email',
            'capability' => $capability,
            'callback'   => [ $this, 'jobseeker_list_email_page' ],
            'position'   => 5000,
        ] );
    }

    /**
     * Job Opening list page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function job_opening_list_page() {
        $sub_section = ! empty( $_GET['sub-section'] ) ? $_GET['sub-section'] : '';
        switch ( $sub_section ) {
            case 'applicant_detail':
                require_once WPERP_REC_VIEWS . '/view-applicant-details.php';
                break;
            case 'make_employee':
                require_once WPERP_REC_VIEWS . '/view-make-employee.php';
                break;
            default:
                $view = isset( $_GET['view'] ) ? $_GET['view'] : '';
                if ( $view == 'detail_template' ) {
                    require_once WPERP_REC_VIEWS . '/view-job-detail.php';
                } else {
                    ?>
                    <div class="wrap job-opening-wrap">

                        <?php do_action( 'before_job_table' ); ?>

                        <h1 class="wp-heading-inline">
                            <?php _e( 'Job Openings', 'erp-pro' ); ?>
                            <a class="page-title-action" href="<?php echo erp_rec_url( 'add-opening' ); ?>">
                                <?php _e( 'Create Openings', 'erp-pro' ); ?>
                            </a>
                        </h1>
                        <form method="post">
                            <input type="hidden" name="page" value="erp-hr">
                            <input type="hidden" name="section" value="recruitment">
                            <?php
                            $job_opening_table = new Recruitment();
                            $job_opening_table->prepare_items();
                            $job_opening_table->search_box( __( 'Search', 'erp-pro' ), 'erp-recruitment-search' );
                            $job_opening_table->views();
                            $job_opening_table->display();
                            ?>
                        </form>
                    </div>
                    <?php
                }
                break;
        }
    }

    /**
     * Opening page
     *
     * para
     *
     * return void
     */
    public function job_description_step() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'new';
        $step   = isset( $_GET['step'] ) ? $_GET['step'] : 'job_description';

        if ( $action == 'new' ) {
            require_once WPERP_REC_VIEWS . '/step-job-description.php';
        } elseif ( $action == 'edit' ) {
            if ( $step == 'hiring_workflow' ) {
                require_once WPERP_REC_VIEWS . '/step-hiring-workflow.php';
            } elseif ( $step == 'job_information' ) {
                require_once WPERP_REC_VIEWS . '/step-job-information.php';
            } elseif ( $step == 'candidate_basic_information' ) {
                require_once WPERP_REC_VIEWS . '/step-candidate-basic-information.php';
            } elseif ( $step == 'questionnaire' ) {
                require_once WPERP_REC_VIEWS . '/step-questionnaire.php';
            }
        }
    }

    /*
     * Candidate list page
     * @since 1.0.0
     * @return void
     */
    public function candidate_page() {
        require_once WPERP_REC_VIEWS . '/jobseeker-list.php';
    }

    /*
     * Todos calendar page
     * @since 1.0.0
     * @return void
     */
    public function todo_calendar_page() {
        require_once WPERP_REC_VIEWS . '/todo-calendar.php';
    }

    /*
     * Opening reports page
     * @since 1.0.0
     * @return void
     */
    public function opening_reports_page() {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'opening_reports';

        switch ( $tab ) {
            case 'candidate_reports':
                require_once WPERP_REC_VIEWS . '/reports/candidate-reports.php';
                break;
            case 'csv_reports':
                require_once WPERP_REC_VIEWS . '/reports/csv-reports.php';
                break;
            case 'opening_reports':
                require_once WPERP_REC_VIEWS . '/reports/opening-reports.php';
                break;
        }
    }

    /*
     * Interviewer reports page
     * @since 1.0.0
     * @return void
     */
    public function candidate_reports_page() {
        require_once WPERP_REC_VIEWS . '/reports/candidate-reports.php';
    }

    /*
     * Opening reports page
     * @since 1.0.0
     * @return void
     */
    public function csv_reports_page() {
        require_once WPERP_REC_VIEWS . '/reports/csv-reports.php';
    }

    /*
     * Applicant list to email page
     * @since 1.0.0
     * @return void
     */
    public function jobseeker_list_email_page() {
        require_once WPERP_REC_VIEWS . '/jobseeker-list-email.php';
    }

    /*
     * Include make employee from applicant detail page
     * @since 1.0.0
     * @return void
     */
    public function jobseeker_list() {
        require_once WPERP_REC_VIEWS . '/jobseeker-list.php';
    }

    /*
     * Include make employee from applicant detail page
     * @since 1.0.0
     * @return void
     */
    public function make_employee() {
        require_once WPERP_REC_VIEWS . '/view-make-employee.php';
    }

    /*
     * Include applicant detail page
     * @since 1.0.0
     * @return void
     */
    public function applicant_detail() {
        require_once WPERP_REC_VIEWS . '/view-applicant-details.php';
    }

    /*
     * Include add candidate page
     * @since 1.0.0
     * @return void
     */
    public function add_candidate() {
        require_once WPERP_REC_VIEWS . '/add-candidate.php';
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @since 1.0.0
     * @since 1.1.0 Load scripts in specific pages
     *
     * @uses  wp_enqueue_script()
     * @uses  wp_localize_script()
     * @uses  wp_enqueue_style
     */
    public function enqueue_scripts( $hook ) {
        global $post;
        wp_enqueue_style( 'erp-recruitment-style-hide-submenu', WPERP_REC_ASSETS . '/css/hide-submenu.css' );

        $load_script       = false;
        $recruitment_pages = [
            'toplevel_page_erp-hr-recruitment',
            'recruitment_page_add-opening',
            'recruitment_page_jobseeker_list',
            'recruitment_page_add_candidate',
            'recruitment_page_todo-calendar',
            'recruitment_page_opening_reports',
            'recruitment_page_applicant_detail',
            'wp-erp_page_erp-hr'
        ];
        if ( in_array( $hook, $recruitment_pages ) ) {
            $load_script = true;
        }

        $post_types = [
            'erp_hr_recruitment',
            'erp_hr_questionnaire'
        ];

        if ( ! empty( $post->post_type ) && in_array( $post->post_type, $post_types ) ) {
            $load_script = true;
        }

        if ( ! $load_script ) {
            return;
        }

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'erp-recruitment-style', WPERP_REC_ASSETS . '/css/stylesheet.css' );
        wp_enqueue_style( 'erp-recruitment-barrating-star-style', WPERP_REC_ASSETS . '/css/fontawesome-stars.css' );
        wp_enqueue_style( 'erp-recruitment-extra-fields-style', WPERP_REC_ASSETS . '/css/extra-fields-style.css' );
        wp_enqueue_style( 'alertify-core-style', WPERP_REC_ASSETS . '/css/alertify.core.css' );
        wp_enqueue_style( 'alertify-default-style', WPERP_REC_ASSETS . '/css/alertify.default.css' );
        wp_enqueue_style( 'erp-timepicker' );
        wp_enqueue_style( 'erp-fullcalendar' );
        wp_enqueue_style( 'erp-sweetalert' );

        /**
         * All scripts goes here
         */
        //wp_enqueue_script('erp-recruitment-vuejs-script', WPERP_REC_ASSETS . '/js/vue.min.js', [], false, true);
        wp_enqueue_script( 'erp-vuejs' );
        wp_enqueue_script( 'erp-recruitment-barrating-script', WPERP_REC_ASSETS . '/js/jquery.barrating.js', [ 'jquery' ], false, true );
        wp_enqueue_script( 'erp-recruitment-app-script', WPERP_REC_ASSETS . '/js/app.js', [ 'jquery' ], false, true );
        wp_enqueue_script( 'erp-recruitment-script', WPERP_REC_ASSETS . '/js/recruitment_entry.js', [ 'jquery' ], false, true );
        wp_enqueue_script( 'erp-recruitment-dynamic-field-script', WPERP_REC_ASSETS . '/js/script.js', [ 'jquery' ], false, true );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'erp-google-map-script-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBkI1ZYg131g_O4YfbCc7eCmIen8omKFC4', [], false, true );
        wp_enqueue_script( 'erp-timepicker' );
        wp_enqueue_script( 'erp-fullcalendar' );
        wp_enqueue_script( 'multi-step-form-script', WPERP_REC_ASSETS . '/js/openingFormToWizard.js', [ 'jquery' ], false, true );
        wp_enqueue_script( 'alertify-lib', WPERP_REC_ASSETS . '/js/alertify.min.js', [ 'jquery' ], false, true );

        $localize_scripts = [
            'nonce'                          => wp_create_nonce( 'recruitment_form_builder_nonce' ),
            'qcollection'                    => [],
            'admin_url'                      => admin_url( 'admin.php' ),
            'todo_popup'                     => [
                'title'       => __( 'Create a new To-do', 'erp-pro' ),
                'del_confirm' => __( 'Are you sure you want to delete this to-do?', 'erp-pro' ),
                'submit'      => __( 'Create', 'erp-pro' )
            ],
            'todo_description_popup'         => [
                'title' => __( 'To-do Detail', 'erp-pro' ),
                'close' => __( 'Close', 'erp-pro' )
            ],
            'interview_popup'                => [
                'title'        => __( 'Create a new Interview', 'erp-pro' ),
                'update_title' => __( 'Update Interview', 'erp-pro' ),
                'del_confirm'  => __( 'Are you sure you want to delete this interview?', 'erp-pro' ),
                'submit'       => __( 'Create', 'erp-pro' ),
                'update'       => __( 'Update', 'erp-pro' )
            ],
            'stage_del_confirm'              => __( 'Are you sure you want to delete this stage?', 'erp-pro' ),
            'add_candidate_popup'            => [
                'title'  => __( 'Add Candidate', 'erp-pro' ),
                'submit' => __( 'Create', 'erp-pro' )
            ],
            'candidate_submission'           => [
                'success_message'    => __( 'Candidate added successfully', 'erp-pro' ),
                'candidate_list_url' => erp_rec_url( 'jobseeker_list' ),
            ],
            'stage_message'                  => [
                'duplicate_error_message'        => __( 'Given stage name already exist!', 'erp-pro' ),
                'candidate_number_error_message' => __( 'You cannot uncheck it because this stage has candidate!', 'erp-pro' ),
                'prompt_message'                 => __( 'Please enter stage title', 'erp-pro' ),
                'title_message'                  => __( 'Please select at least one stage (A stage has been auto-selected)', 'erp-pro' )
            ],
            'information_validation_message' => [
                // 'hiring_validation_message'      => __( 'Hiring lead cannot be empty. Please select a hiring lead.', 'erp-pro' ),
                'department_validation_message'  => __( 'Department cannot be empty. Please select a department name.', 'erp-pro' ),
                'employment_validation_message'  => __( 'Employment type cannot be empty. Please select an employment type.', 'erp-pro' ),
                'minimum_exp_validation_message' => __( 'Minimum experience cannot be empty. Please select minimum experience.', 'erp-pro' ),
                'expire_date_validation_message' => __( 'Expire date cannot be empty. Please select expire date.', 'erp-pro' ),
                'location_validation_message'    => __( 'Location cannot be empty. Please enter location.', 'erp-pro' ),
                'vacancy_validation_message'     => __( 'Vacancy cannot be empty. Please enter vacancy number.', 'erp-pro' )
            ]
        ];

        wp_localize_script( 'erp-recruitment-app-script', 'wpErpRec', $localize_scripts );
    }

    /**
     * Register & Enqueue settings page scripts
     *
     * @since 1.4.2
     *
     * @return void
     */
    public function enqueue_settings_scripts() {
        if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'erp-settings' ) {
            wp_register_script( 'erp-rec-settings', WPERP_REC_ASSETS . '/js/settings.js', [ 'erp-settings' ], false, true );
            wp_enqueue_script( 'erp-rec-settings' );
        }
    }

    /**
     * Enqueue front-end scripts
     *
     * @return void
     */
    public function front_end_scripts() {
        wp_register_style( 'erp-recruitment-front-end-style', WPERP_REC_ASSETS . '/css/frontend.css' );
        // wp_register_style( 'multi-step-form-style', WPERP_REC_ASSETS . '/css/multi-form-style.css' );
        wp_enqueue_style( 'erp-sweetalert' );
        wp_enqueue_script( 'erp-sweetalert' );
        wp_enqueue_style( 'alertify-core-style', WPERP_REC_ASSETS . '/css/alertify.core.css' );
        wp_enqueue_style( 'alertify-default-style', WPERP_REC_ASSETS . '/css/alertify.default.css' );
        wp_enqueue_script( 'alertify-lib', WPERP_REC_ASSETS . '/js/alertify.min.js', [ 'jquery' ], false, true );
        wp_register_script( 'erp-recruitment-frontend-script', WPERP_REC_ASSETS . '/js/recruitment_frontend.js', [ 'jquery' ], false, true );
        wp_register_script( 'multi-step-form-script', WPERP_REC_ASSETS . '/js/formToWizard.js', [ 'jquery' ], false, true );
        wp_enqueue_style( 'erp-recruitment-front-end-style' );
        wp_enqueue_style( 'multi-step-form-style' );
        wp_enqueue_script( 'erp-recruitment-frontend-script' );
        wp_enqueue_script( 'multi-step-form-script' );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css' );

        $localize_script = apply_filters( 'erp_rec_localize_script', [
            'nonce'    => wp_create_nonce( 'wp-erp-rec-nonce' ),
            'popup'    => [
                'jobseeker_title'  => __( 'New JobSeeker', 'erp-pro' ),
                'jobseeker_submit' => __( 'Submit', 'erp-pro' )
            ],
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'confirm'  => __( 'Are you sure?', 'erp-pro' ),
            'fileSize' => __( 'File size is greater than 2MB', 'erp-pro' )
        ] );

        wp_localize_script( 'erp-recruitment-frontend-script', 'wpErpHr', $localize_script );

        $country = \WeDevs\ERP\Countries::instance();
        wp_localize_script( 'erp-recruitment-frontend-script', 'wpErpCountries', $country->load_country_states() );

    }

}
