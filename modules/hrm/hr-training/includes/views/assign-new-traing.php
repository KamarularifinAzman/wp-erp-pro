<?php
    $posts      = get_posts( array(
        'post_type' => 'erp_hr_training',
        'posts_per_page'    =>  -1
    ) );
    $profile_id = !empty( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ): get_current_user_id();

    $options = array( 'Select' );
    if ( $posts ) {
        foreach ( $posts as $post ) {
            $options[ $post->ID ] = $post->post_title;
        }
    }
?>
<div class="employee-training-assign-form">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Training', 'erp-pro' ),
            'name'      => 'training-id',
            'required'  => true,
            'type'      => 'select',
            'class'     => 'erp-hrm-select2-add-more erp-hr-desi-drop-down erp-hr-leave-period',
            'options'   => $options
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Completed Date', 'erp-pro' ),
            'name'      => 'training-completed-date',
            'class'     =>  'erp-date-field',
            'required'  => true,
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Trainer\'s name', 'erp-pro' ),
            'name'      => 'training-trainer',
            'required'  => true,
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Trainer\' Phone No.', 'erp-pro' ),
            'name'      => 'trainer-phone',
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Cost', 'erp-pro' ),
            'name'      => 'training-cost',
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Credit', 'erp-pro' ),
            'name'      => 'training-credit',
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Hours', 'erp-pro' ),
            'name'      => 'training-hours',
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Notes', 'erp-pro' ),
            'name'      => 'training-notes',
            'type'      => 'textarea'
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'label'         => __( 'Rating', 'erp-pro' ),
            'name'          => 'training-rate',
            'type'          => 'number',
            'custom_attr'   => array(
                'min'         => 1,
                'max'         => 10,
                'style'       => 'width: 170px;',
                'placeholder' => __( 'Rate between 1 to 10', 'erp-pro' )
            ),
            'required'  => true
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'name'      => 'action',
            'value'     => 'erp_assign_new_training',
            'type'      => 'hidden',
        ) );?>
    </div>
    <div class="row">
        <?php erp_html_form_input( array(
            'name'      => 'erp_training_user_id',
            'value'     => $profile_id,
            'type'      => 'hidden',
        ) );?>
    </div>
</div>
