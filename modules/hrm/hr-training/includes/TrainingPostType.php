<?php
namespace WeDevs\HrTraining;

/**
 * WP ERP Post Type class
 */
class TrainingPostType {
    /**
     * Constructor function
     */
    function __construct() {
        add_action( 'init', array( $this, 'erp_training_register_post_types' ) );

        add_action( 'add_meta_boxes', array( $this, 'do_metaboxes' ) );

        add_action( 'save_post', array( $this, 'save_training' ), 10, 2 );

        add_action( 'manage_erp_hr_training_posts_columns', array( $this, 'set_training_column' ) );

        add_action( 'manage_erp_hr_training_posts_custom_column', array( $this, 'training_custom_column' ), 10, 2 );

        // add_action( 'quick_edit_custom_box', array( $this, 'display_quick_edit_training' ), 10, 2 );

        add_action( 'edit_form_advanced', array( $this, 'force_post_title' ) );
    }

    /**
     * Register ERP Training post type
     *
     * @since 0.1
     *
     * @return void
     */
    public function erp_training_register_post_types() {
        $post_type  = 'erp_hr_training';
        $capability = 'erp_hr_manager';

        register_post_type( $post_type,
            array(
                'label'           => __( 'Training', 'erp-pro' ),
                'description'     => '',
                'public'          => true,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'capability_type' => 'post',
                'hierarchical'    => false,
                'query_var'       => false,
                'supports'        => array(
                    'title'
                ),
                'menu_icon'       => 'dashicons-businessman',
                'capabilities'    => array(
                    'edit_post'          => $capability,
                    'read_post'          => $capability,
                    'delete_posts'       => $capability,
                    'edit_posts'         => $capability,
                    'edit_others_posts'  => $capability,
                    'publish_posts'      => $capability,
                    'read_private_posts' => $capability,
                    'create_posts'       => $capability,
                    'delete_post'        => $capability
                ),
                'labels'          => array(
                    'name'               => __( 'Training', 'erp-pro' ),
                    'singular_name'      => __( 'Training', 'erp-pro' ),
                    'menu_name'          => __( 'Training', 'erp-pro' ),
                    'add_new'            => __( 'Create Training', 'erp-pro' ),
                    'add_new_item'       => __( 'Add New Training', 'erp-pro' ),
                    'edit'               => __( 'Edit', 'erp-pro' ),
                    'edit_item'          => __( 'Edit Training', 'erp-pro' ),
                    'new_item'           => __( 'New Training', 'erp-pro' ),
                    'view'               => __( 'View Training', 'erp-pro' ),
                    'view_item'          => __( 'View Training', 'erp-pro' ),
                    'search_items'       => __( 'Search Training', 'erp-pro' ),
                    'not_found'          => __( 'No Training Found', 'erp-pro' ),
                    'not_found_in_trash' => __( 'No Training found in trash', 'erp-pro' ),
                    'parent'             => __( 'Parent Training', 'erp-pro' )
                )
            )
        );
    }

    /**
     * Initialize meta boxes for HR Trainig post type
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function do_metaboxes() {
        add_meta_box(
            'erp-hr-training-meta-box',
            __( 'HR Training Options', 'erp-pro' ),
            array( $this, 'meta_boxes_cb' ),
            'erp_hr_training',
            'advanced',
            'high'
        );
    }

    /**
     * ERP HR Trainig metaboxes
     *
     * @return  void
     */
    public function meta_boxes_cb( $post_id ) {
        if ( ! $post_id ) {
            return;
        }
        global $post;
    $employees    = erp_hr_get_employees( [ 'number' => -1, 'no_object' => true ] );
    $departments  = erp_hr_get_departments( [ 'number' => -1, 'no_object' => true ] );
    $designations = erp_hr_get_designations( [ 'number' => -1, 'no_object' => true ] );

    $training_type            = get_post_meta( $post->ID, 'training_type', true );
    $training_users           = get_post_meta( $post->ID, 'erp_training_incompleted_employee', true );
    $training_departments     = get_post_meta( $post->ID, 'erp_training_department', true );
    $training_designations    = get_post_meta( $post->ID, 'erp_training_designation', true );

    $training_employee    = ( $training_users ) ? $training_users : array();
    $training_department  = ( $training_departments )  ? $training_departments  : array();
    $training_designation = ( $training_designations ) ? $training_designations : array();
        $assign_type = array(
            ''                  => __( '-- Select --', 'erp' ),
            'all_employee'      => __( 'All Employees', 'erp' ),
            'selected_employee' => __( 'Selected Employee', 'erp' ),
            'by_department'     => __( 'By Department', 'erp' ),
            'by_designation'    => __( 'By Designation', 'erp' )
        );
        $training_users           = get_post_meta( get_the_ID(), 'erp_training_incompleted_employee', true );
        $training_departments     = get_post_meta( get_the_ID(), 'erp_training_department', true );
        $training_designations    = get_post_meta( get_the_ID(), 'erp_training_designation', true );
        ?>
        <div class="wp-erp-training-meta-data">
            <p class="form-field">
                <label><?php _e( "Training Subject (Skill)", 'erp-pro' ); ?>: </label>
                <input type="text" id="traning-subject" name="training_subject" value="<?php echo get_post_meta( get_the_ID(), 'training_subject', true ); ?>">
            </p>
            <div class="erp-hr-training-meta-wrap ">
            <p class="form-field">
                <label><?php _e( 'Assign To', 'erp' ); ?></label>
                <select name="training_type" id="training_type" style="width:60%">
                    <?php foreach ( $assign_type as $key => $type ): ?>
                        <option value="<?php echo $key; ?>" <?php selected( $training_type, $key ); ?> ><?php echo $type; ?></option>
                    <?php endforeach ?>
                </select>
            </p>

            <p class="selected_employee_field">
                <label><?php _e( 'Select Employees', 'erp' ); ?></label>
                <select name="employees[]" data-placeholder= '<?php echo __( 'Select Employees...', 'erp' ); ?>' id="employees" class="erp-select2" multiple="multiple">
                    <?php
                    foreach ( $employees as $user ) {
                        if ( $user->user_id == get_current_user_id() ) {
                            continue;
                        }

                        ?>
                            <option <?php echo in_array( $user->user_id, $training_employee ) ? 'selected="selected"' : ''; ?> value='<?php echo $user->user_id  ?>'><?php echo $user->display_name; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>

            <p class="by_department_field">
                <label><?php _e( 'Select Departments', 'erp' ); ?></label>
                <select name="departments[]" data-placeholder= '<?php echo __( 'Select Departments...', 'erp' ); ?>' id="departments" class="erp-select2" multiple="multiple">
                    <?php
                    foreach ( $departments as $department ) {
                        ?>
                        <option <?php echo in_array( $department->id, $training_department ) ? 'selected="selected"' : ''; ?> value='<?php echo $department->id; ?>'><?php echo $department->title; ?></option>
                        <?php
                    }
                    ?>
                </select>

            </p>

            <p class="by_designation_field">
                <label><?php _e( 'Select Designations', 'erp' ); ?></label>
                <select name="designations[]" data-placeholder= '<?php echo __( 'Select Designations...', 'erp' ); ?>' id="designations" class="erp-select2" multiple="multiple">
                    <?php
                    foreach ( $designations as $designation ) {
                        ?>
                        <option <?php echo in_array( $designation->id, $training_designation ) ? 'selected="selected"' : ''; ?> value='<?php echo $designation->id; ?>'><?php echo $designation->title; ?></option>
                        <?php
                    }
                    ?>
                </select>

            </p>
        </div>

        <?php do_action( 'hr_training_table_last', $post ); ?>
            <?php wp_nonce_field( 'hr_training_meta_action', 'hr_training_meta_action_nonce' ); ?>
            <script>
                (function( $ ){
                    $( document ).ready( function() {

                        // Remove selected value other than the currently active one.
                        switch ( $('select#training_type').val() ) {
                            case 'selected_employee':
                                $( '.by_department_field select' ).val(null).trigger('change');
                                $( '.by_designation_field  select' ).val(null).trigger('change');
                                break;

                            case 'by_department':
                                $( '.selected_employee_field  select' ).val(null).trigger('change');
                                $( '.by_designation_field  select' ).val(null).trigger('change');
                                break;

                            case 'by_designation':
                                $( '.selected_employee_field  select' ).val(null).trigger('change');
                                $( '.by_department_field  select' ).val(null).trigger('change');
                        }

                        $('.erp-hr-training-meta-wrap').on( 'change', 'select#training_type', function() {
                            var self = $( this );

                            switch ( self.val() ) {
                                case 'all_employee':
                                    $( '.selected_employee_field' ).hide();
                                    $( '.by_department_field' ).hide();
                                    $( '.by_designation_field' ).hide();
                                    break;

                                case 'selected_employee':
                                    $( '.by_department_field' ).hide();
                                    $( '.by_designation_field' ).hide();
                                    $( '.selected_employee_field' ).show();
                                    break;

                                case 'by_department':
                                    $( '.selected_employee_field' ).hide();
                                    $( '.by_department_field' ).show();
                                    $( '.by_designation_field' ).hide();
                                    break;

                                case 'by_designation':
                                    $( '.selected_employee_field' ).hide();
                                    $( '.by_department_field' ).hide();
                                    $( '.by_designation_field' ).show();
                            }
                        });

                        $( 'select#training_type' ).trigger( 'change' )
                    });
                })( jQuery );
            </script>
            <style>
                /*#hr_training_assign_employee,
                #hr_training_assign_department,
                #hr_training_assign_designation {
                    width: 315px;
                }*/

                .selected_employee_field,
                .by_department_field,
                .by_designation_field,
                .break {
                    display: none;
                }

                .erp-hr-training-meta-wrap .select2 {
                    width: 315px !important;
                }
            </style>
            <p class="form-field">
                <label for="erp-training-frequency"><?php _e( 'Duration', 'erp-pro' );?></label>
                <input type="text" id="erp-training-frequency" name="training_frequency" value="<?php echo get_post_meta( get_the_ID(), 'training_frequency', true ); ?>">
            </p>
            <p class="">
                <label><?php _e( 'Auto assigned for new employee', 'erp-pro' ); ?>
                    <input type="checkbox" name="auto_assigned" value="yes" <?php checked( 'yes', get_post_meta( get_the_ID(), 'auto_assigned', true ) ) ?>>
                </label>
            </p>
            <p class="form-field">
                <label><?php _e( 'Description', 'erp-pro' ); ?>: </label>
                <textarea id="description" name="description"><?php echo get_post_meta( get_the_ID(), 'description', true ); ?></textarea>
            </p>
        </div>
        <?php
        wp_nonce_field( 'hr_training_meta_action', 'hr_training_meta_action_nonce' );
    }

    /**
     * Save training custom field
     *
     * @param  integer $post_id
     * @return void
     */
    public function save_training( $post_id, $post ) {
        if ( !current_user_can( 'erp_hr_manager' ) ) {
            return;
        }

        if ( ! isset( $_POST['hr_training_meta_action_nonce'] ) ) {
            return $post_id;
        }

        if ( ! wp_verify_nonce( $_POST['hr_training_meta_action_nonce'], 'hr_training_meta_action' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $data = array();
        $data['training_subject']    =   ( isset( $_POST['training_subject'] ) ) ? $_POST['training_subject'] : '';
        $data['training_frequency']  =   ( isset( $_POST['training_frequency'] ) ) ? $_POST['training_frequency'] : '';
        $data['description']         =   ( isset( $_POST['description'] ) ) ? $_POST['description'] : '';
        $data['auto_assigned']       =   ( isset( $_POST['auto_assigned'] ) ) ? $_POST['auto_assigned'] : '';

        // Save each custom field
        foreach ( $data as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }

        // Employee
        $type                     = ! empty( $_POST['training_type'] ) ? $_POST['training_type'] : '';
        $departments              = ! empty( $_POST['departments'] )  ?  $_POST['departments']  : array();
        $designations             = ! empty( $_POST['designations'] )  ? $_POST['designations'] : array() ;
        $employees                = ! empty( $_POST['employees'] ) ? $_POST['employees'] : array();

        if ( is_string( $employees ) ) {
            $employees  =   explode( ',', $employees );
        }
        if ( $type == 'by_department' ) {
            $select = $departments;
        } else if( $type == 'by_designation' ) {
            $select = $designations;
        } else {
            $select = $employees;
        }


        $this->assign_training_to_employees( $post_id, $type, $select );

        wp_redirect( admin_url( 'edit.php?post_type=erp_hr_training' ) );
        exit;
    }
    /**
     * Assign training to employee
     *
     * @param  integer $post_id
     * @param  string $type
     * @param  array  $Selected
     * @return void
     */
    public function assign_training_to_employees( $post_id, $type, $selected = [] ) {
        $post      = get_post( $post_id );
        $post_type = $post->post_status;

        $data      = [];

        if ( $type == 'by_department' ) {
            update_post_meta( $post_id, 'erp_training_department', $selected );

            foreach ( $selected as $department ) {
                $data[] = erp_hr_get_employees( array(
                     'no_object'  => true,
                     'department' => $department
                ) );
            }

            $selected = format_data_as_employee( $data );
        }

        if ( $type == 'by_designation' ) {
            update_post_meta( $post_id, 'erp_training_designation', $selected );

            foreach ( $selected as $designation ) {
                $data[] = erp_hr_get_employees( array(
                     'no_object'  => true,
                     'designation' => $designation
                 ) );
            }

            $selected = format_data_as_employee( $data );
        }

        if ( $type == 'all_employee' ) {
            $empls = erp_hr_get_employees( array( 'no_object' => true ) );

            if ( $empls ) {
                foreach ( $empls as $user ) {
                    $selected[] = (int) $user->user_id;
                }
            }
        }
        $selected   =   array_unique( $selected );
        $completed  =   get_post_meta( $post_id, 'erp_training_completed_employee', true);

        if ( is_array( $completed ) ) {
            foreach( $selected as $key => $value  ) {
                if ( in_array( $value, $completed ) ) {
                    unset( $selected[ $key ] );
                }
            }
        }

        update_post_meta( $post_id, 'training_type', $type );
        update_post_meta( $post_id, 'erp_training_incompleted_employee', $selected );

        $training_details   = get_post( $post_id );
        $training_title     = $training_details->post_title;
        $training_post_date = $training_details->post_date;

        $emailer            = wperp()->emailer->get_email( 'AfterAssignTraining' );

        if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {

            foreach ( $selected as $sl ) {
                $user = get_userdata( $sl );
                $data = [
                    'recipient'     => $user->user_email,
                    'training_name' => $training_title,
                    'date'          => $training_post_date,
                    'employee_name' => $user->display_name
                ];
                $emailer->trigger( $data );
            }
        }
    }

    /**
     * Set training column
     *
     * @param array
     */
    public function set_training_column( $column ) {
        $column['training_subject']     = __( 'Training Subject', 'erp-pro' );
        $column['description']          = __( 'Description', 'erp-pro' );
        $column['duration']             = __( 'Duration', 'erp-pro' );
        $column['participant']          = __( 'Participants', 'erp-pro' );
        /*$column['employee']             = '';
        $column['auto_assigned']        = '';
        $column['training_type']        = '';*/
        unset( $column['date'] );
        return $column;
    }

    /**
     * Training custom column
     *
     * @param  array $column
     * @param  integer $post_id
     * @return void
     */
    public function training_custom_column( $column, $post_id ) {

        switch ( $column ) {
            case 'training_subject':
                $subject    = get_post_meta( get_the_ID(), 'training_subject', true );

                if ( $subject ) {
                    echo $subject;
                }

                break;
            case 'description':
                $description = get_post_meta( get_the_ID(), 'description', true );
                if ( $description ) {
                    echo $description;
                }
                break;
            case 'duration':
                $training_frequency = get_post_meta( get_the_ID(), 'training_frequency', true );

                if ( $training_frequency ) {
                    echo $training_frequency;
                }
                break;
            case 'auto_assigned':
                $auto_assigned = get_post_meta( get_the_ID(), 'auto_assigned', true );

                if ( $auto_assigned ) {
                    ?>
                        <input type="hidden" checked="checked" value="yes">
                    <?php
                }
                break;
            case 'employee':
                $employee = get_post_meta( get_the_ID(), 'erp_training_incompleted_employee', true );


                break;
            case 'training_type':
                $training_type  =   get_post_meta( get_the_ID(), 'training_type', true );
                if ( $training_type ) {
                    ?>
                        <input type="hidden" id="get_training_type" value="<?php echo $training_type ?>">
                    <?php
                }
            break;
            case 'participant':

                $employee_training_status_all   = [];
                $incomplete_training_data       = get_post_meta( get_the_ID(), 'erp_training_incompleted_employee', true );
                $completed_training_data        = get_post_meta( get_the_ID(), 'erp_training_completed_employee', true );
                if ( $completed_training_data ) {
                    foreach ($completed_training_data as $ctd) {

                        $training_details = get_user_meta( $ctd, 'erp_employee_training', true )[get_the_ID()];

                        if ( isset( $training_details['erp_training_rate'] ) ) {
                            $score = $training_details['erp_training_rate'] . '/10';
                        } else {
                            $score = '';
                        }

                        $employee_training_status['name']   = erp_hr_get_employee_name($ctd);
                        $employee_training_status['status'] = __('Completed', 'erp-pro');
                        $employee_training_status['score']  = $score;
                        $employee_training_status_all[]     = $employee_training_status;
                    }
                }

                if ( $incomplete_training_data ) {
                    foreach ($incomplete_training_data as $ictd) {
                        $employee_training_status['name']   = erp_hr_get_employee_name($ictd);
                        $employee_training_status['status'] = __('Incomplete', 'erp-pro');
                        $employee_training_status['score']  = '';
                        $employee_training_status_all[]     = $employee_training_status;
                    }
                }
                ?>
                <a href="#" class="training_participant" id="tpview_<?php echo get_the_ID() ;?>">View participant</a>
                <script type="text/html" id="training_participant_<?php echo get_the_ID() ;?>">
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <th>Training Participant</th>
                                <th>Status</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ( count( $employee_training_status_all ) > 0 ) :
                            foreach ( $employee_training_status_all as $etsa ) :  ?>
                                <tr>
                                    <td><?php echo $etsa['name'] ;?></td>
                                    <td><?php echo $etsa['status'] ;?></td>
                                    <td><?php echo $etsa['score'] ;?></td>
                                </tr>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="3"> There is no participant right now. </td>
                            </tr>
                            <?php
                        endif; ?>
                        </tbody>
                    </table>
                </script>
                <?php
                break;
        }
    }

    /**
     * Display quick edit custom fields
     *
     * @return void
     */
    public function display_quick_edit_training( $column_name, $post_type ) {
        if ( 'erp_hr_training' != $post_type ) {
            return;
        }
        if ( $column_name == 'employee' ) {
            return;
        }
       ?>
        <fieldset class="inline-edit-col-left">
          <div class="inline-edit-col column-<?php echo $column_name; ?>">
            <label class="inline-edit-group">
            <?php
                switch ( $column_name ) {
                    case 'training_subject':
                     ?><span class="title">Subject</span><span class="input-text-wrap"><input type="text" name="training_subject" class="ptitle"></span><?php
                    break;

                    case 'description':
                     ?><span class="title">Description</span> <span class="input-text-wrap"><textarea name="description" id=""></textarea></span><?php
                    break;

                    case 'duration':
                     ?><span class="title">Duration</span> <span class="input-text-wrap"><input name="training_frequency" type="text" /></span><?php
                    break;

                    case 'employee':
                        ?> <span class="input-text-wrap"><input type="hidden" name="employees"></span> <?php
                        break;
                    case 'auto_assigned':
                        ?><label class="alignleft inline-edit-private">
                            <input type="checkbox" name="auto_assigned" value="yes">
                            <span class="checkbox-title">Auto assign when employee created.</span>
                        </label><?php
                    break;
                    case 'training_type':
                        ?>
                            <input type="hidden" name="training_type">
                        <?php
                    break;

                }
            ?>
            </label>
          </div>
        </fieldset>
       <?php
    }

    /**
     * Required post title
     *
     * @param  string $post
     * @return void
     */
    public function force_post_title( $post )  {

        if ( $post->post_type !== 'erp_hr_training' ) {
            return;
        }

        ?>
            <script type='text/javascript'>
                ( function ( $ ) {

                    $( document ).ready( function () {
                        $( 'body' ).on( 'submit.edit-post', '#post', function () {
                            if ( $( "#title" ).val().replace( / /g, '' ).length === 0 ) {
                                // Show the alert
                                if ( !$( "#title-required-msj" ).length ) {
                                    $( "#titlewrap" )
                                    .append( '<div id="title-required-msj"><em>Title is required.</em></div>' )
                                    .css({
                                        "padding": "5px 10px",
                                        "margin": "5px 0",
                                    });
                                }
                                // Hide the spinner
                                $( '#major-publishing-actions .spinner' ).hide();
                                // The buttons get "disabled" added to them on submit. Remove that class.
                                $( '#major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );
                                // Focus on the title field.
                                $( "#title" ).focus();
                                return false;
                            }
                        });
                    });
                }( jQuery ) );
            </script>
        <?php
    }
}
