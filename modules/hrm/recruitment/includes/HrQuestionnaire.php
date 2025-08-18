<?php
namespace WeDevs\Recruitment;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 *  Recruitment class HR
 *
 *  Recruitment for employees
 *
 * @since 1.0.0
 *
 * @author weDevs <info@wedevs.com>
 */
class HrQuestionnaire {

    use Hooker;

    private $post_type = 'erp_hr_questionnaire';
    private $assign_type = [];

    /**
     * Load autometically all actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    function __construct() {

        $this->assign_type = array(
            ''                  => __('-- Select --', 'erp-pro'),
            'all_employee'      => __('All Employees', 'erp-pro'),
            'selected_employee' => __('Selected Employee', 'erp-pro')
        );

        $this->action('init', 'post_types');
        $this->action('do_meta_boxes', 'do_metaboxes');
        $this->action('save_post', 'save_questionnaire_meta', 10, 2);
        $this->action('manage_erp_hr_questionnaire_posts_custom_column', 'questionnaire_table_content', 10, 2);

        $this->filter('manage_erp_hr_questionnaire_posts_columns', 'questionnaire_table_head');

        $this->action( 'admin_head', 'filter_admin_sidebar_menu_items' );
    }

    /**
     * Register Questionnaire post type
     *
     * @since 1.0.0
     *
     * @return void
     */
    function post_types() {
        $capability = 'manage_recruitment';

        register_post_type( $this->post_type, array(
            'label'              => __( 'HR Questionnaire', 'erp-pro' ),
            'description'        => '',
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'capability_type'    => 'post',
            'publicly_queryable' => false,
            'hierarchical'       => false,
            'query_var'          => false,
            'supports'           => array('title'),
            'capabilities'       => array(
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
            'labels'             => array(
                'name'               => __( 'HR Questionnaire', 'erp-pro' ),
                'singular_name'      => __( 'HR Questionnaire', 'erp-pro' ),
                'menu_name'          => __( 'HR Recruitment', 'erp-pro' ),
                'add_new'            => __( 'Add HR Questionnaire', 'erp-pro' ),
                'add_new_item'       => __( 'Add New HR Questionnaire', 'erp-pro' ),
                'edit'               => __( 'Edit', 'erp-pro' ),
                'edit_item'          => __( 'Edit HR Questionnaire', 'erp-pro' ),
                'new_item'           => __( 'New HR Questionnaire', 'erp-pro' ),
                'view'               => __( 'View HR Questionnaire', 'erp-pro' ),
                'view_item'          => __( 'View HR Questionnaire', 'erp-pro' ),
                'search_items'       => __( 'Search HR Questionnaire', 'erp-pro' ),
                'not_found'          => __( 'No HR Questionnaire Found', 'erp-pro' ),
                'not_found_in_trash' => __( 'No HR Questionnaire found in trash', 'erp-pro' ),
                'parent'             => __( 'Parent HR Questionnaire', 'erp-pro' )
            )
        ) );
    }

    /**
     * Initialize meta boxes for Questionnaire post type
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function do_metaboxes() {
        add_meta_box(
            'erp-hr-questionnaire-meta-box',
            __( 'Questionnaire Settings', 'erp-pro' ),
            array( $this, 'meta_boxes_cb' ),
            $this->post_type,
            'advanced',
            'high'
        );
    }

    /**
     * Questionnaire metabox callback function
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function meta_boxes_cb( $post_id ) {
        global $post;

        $localize_scripts = [
            'nonce'            => wp_create_nonce( 'recruitment_form_builder_nonce' ),
            'qcollection'      => get_post_meta( $post->ID, '_erp_hr_questionnaire', true ),
            'custom_post_type' => get_post_type( $post->ID ),
            'post_title'       => str_replace( ' ', '_', strtolower( get_the_title( $post->ID ) ) )
        ];

        wp_localize_script( 'erp-recruitment-app-script', 'wpErpRec', $localize_scripts );
        ?>

        <div id="questions-field-main" xmlns:v-bind="http://www.w3.org/1999/xhtml">
            <questions-field
                v-for="(count,singlemodel) in qcollection"
                track-by="$index" v-bind:model="singlemodel"
                :count="count"
                data-index="{{ $index }}"
            >
            </questions-field>

            <div id="add-new-field">
                <button class="button-primary" @click.prevent="addNewField"><?php _e('Add New Question', 'erp-pro'); ?></button>
            </div>
        </div>

        <input type="hidden" id="hr_questions" name="hr_questions" value="" />
        <?php wp_nonce_field( 'hr_recruitment_meta_action', 'hr_recruitment_meta_action_nonce' );?>

        <script type="text/template" id="single-field-template">

            <div class="single-field">

                <div draggable="true" class="main-people-single" v-show="!edit" @click="edit=true" @mouseover="hover = true" @mouseout="hover = false" transition="main-single">
                <div class="extended-button-holder" v-show="hover">
                    <button class="btn button-delete extended-button" @click.stop="deleteModel(model)"><span class="dashicons dashicons-trash"></span></button>
                </div>

                <div class="main-inside">
                    <div class="main-label"> &nbsp;
                        <span>{{ model.label }}</span>
                    </div>

                    <div class="main-field">
                        <span v-if="'text' == model.type"><input type="text" v-on:click.stop placeholder="{{model.placeholder}}"></span>
                        <span v-if="'textarea' == model.type">
                            <textarea v-on:click.stop></textarea>
                        </span>
                        <span v-if="'select' == model.type">
                            <select v-on:click.stop>
                                <option v-for="option in model.options">{{option.value}}</option>
                            </select>
                        </span>
                        <span v-if="'radio' == model.type">
                            <span class="main-child-options" v-for="option in model.options">
                                <input type="radio" v-on:click.stop>{{option.value}}
                            </span>
                        </span>
                        <span v-if="'checkbox' == model.type">
                            <span class="main-child-options" v-for="option in model.options">
                                <input type="checkbox" v-on:click.stop>{{option.value}}
                            </span>
                        </span>
                        <span v-if="'number' == model.type"><input type="number" v-on:click.stop placeholder="{{model.placeholder}}"></span>
                        <span v-if="'url' == model.type"><input type="url" v-on:click.stop placeholder="{{model.placeholder}}"></span>
                        <span v-if="'email' == model.type"><input type="email" v-on:click.stop placeholder="{{model.placeholder}}"></span>
                        <span v-if="'date' == model.type"><input type="date" v-on:click.stop></span>
                        <span class="main-helptext">{{ model.helptext }}</span>
                    </div>
                </div>
            </div>

            <div class="extended-people-single" v-show="edit" transition="extended-single">
                <div class="extended-button-holder">
                    <h4 class="erp-left"><?php _e( 'Question No', 'erp-pro' ); ?> #{{ count+1 }}</h4>
                    <button class="erp-right btn button-delete extended-button" @click.prevent="deleteModel(model)"><span class="dashicons dashicons-trash"></span></button>
                    <div class="clearfix"></div>
                </div>

                <div class="extended-inside">
                    <div class="extended-inside-single">
                        <div class="extended-label">
                            <span><?php _e('Question', 'erp-pro');?></span>
                        </div>
                        <textarea class="widefat regular-text input-question-text" v-model="model.label" placeholder="<?php _e( 'Question', 'erp-pro' ); ?>"></textarea>
                    </div>

                    <div class="extended-inside-single">
                        <div class="extended-label"></div>
                        <div class="extended-label">
                            <label>
                                <input type="checkbox" class="widefat input-question-text" v-model="model.req">
                                <span><?php _e('This is a required field', 'erp-pro');?></span>
                            </label>
                        </div>
                    </div>

                    <div class="extended-inside-single">
                        <div class="extended-label">
                            <span><?php _e('Field Type','erp-pro');?></span>
                        </div>
                        <select v-model="model.type">
                            <option v-for="field in fields" v-bind:value="field.value">{{ field.text }}</option>
                        </select>
                    </div>

                    <div class="extended-inside-single" v-show="hasChildOptions">
                        <div class="extended-child-options">
                            <div class="single-input-option" v-for="option in model.options" track-by="$index">
                                <input type="hidden" placeholder="text" v-bind:value="option.text" v-model="option.text">
                                <input type="text" placeholder="value" v-bind:value="option.value" v-model="option.value">
                                <span class="button remove-option" style="cursor: pointer" v-on:click="removeOption(option)"><?php _e('Remove','erp-pro');?></span>
                            </div>
                        </div>
                    </div>

                    <div class="extended-inside-single" v-show="hasChildOptions">
                        <div class="extended-child-options add-option" v-show="hasChildOptions">
                            <button class="button" @click.prevent="addNewOption(model)"><?php _e('Add Option','erp-pro');?></button>
                        </div>
                    </div>

                    <div class="extended-inside-single">
                        <div class="extended-label">
                            <span><?php _e('Help Text','erp-pro');?></span>
                        </div>
                        <textarea v-model="model.helptext" class="widefat regular-text" placeholder="<?php _e( 'Enter your helper text', 'erp-pro' ); ?>"></textarea>
                    </div>

                </div>
            </div>
            </div>

        </script>

    <?php
    }

    /**
     * Save questionnaire post meta
     *
     * @since  1.0.0
     *
     * @param  integer $post_id
     * @param  object $post
     *
     * @return void
     */
    public function save_questionnaire_meta( $post_id, $post ) {
        if ( $post->post_type != 'erp_hr_questionnaire' ) {
            return;
        }

        if ( !isset($_POST['hr_recruitment_meta_action_nonce']) ) {
            return $post_id;
        }

        if ( !wp_verify_nonce($_POST['hr_recruitment_meta_action_nonce'], 'hr_recruitment_meta_action') ) {
            return $post_id;
        }

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $post_type = get_post_type_object($post->post_type);

        if ( ! current_user_can($post_type->cap->edit_post, $post_id) ) {
            return $post_id;
        }

        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            return $post_id;
        }

        update_post_meta( $post_id, '_erp_hr_questionnaire', json_decode(stripslashes($_POST['hr_questions']), true));

        erp_log()->add( [
            'component'     => 'HRM',
            'sub_component' => 'Recruitment Questionnaire',
            'changetype'    => 'edit',
            'message'       => __( 'A questionnaire has been added', 'erp-pro' ),
            'created_by'    => get_current_user_id(),
            'new_value'     => '',
            'old_value'     => '',
        ] );
    }

    /**
     * Added custom column in questionnaire list table
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function questionnaire_table_head( $defaults ) {
        $defaults['questions'] = 'Total Question';
        $defaults['created']   = 'Created On';
        $defaults['modified']  = 'Modified';

        return $defaults;
    }

    /**
     * Get and set content of recruitment list table
     *
     * @since  1.0.0
     *
     * @param  string    $column_name
     * @param  integer    $post_id
     *
     * @return void
     */
    public function questionnaire_table_content( $column_name, $post_id ) {
        if ( $column_name == 'questions' ) {
            $get_q_data = get_post_meta(get_the_ID(), '_erp_hr_questionnaire', true);
            echo is_array($get_q_data) ? count($get_q_data) : 0;
        }

        if ( $column_name == 'created' ) {
            echo get_the_date( erp_get_date_format() );
        }

        if ( $column_name == 'modified' ) {
            echo the_modified_date( erp_get_date_format() );
        }
    }

    /**
     * Filter admin sidebar menu items
     *
     * Remove HR Recruitment items from sidebar generated by register_post_type function.
     * Highlight Parent menu as active item when we are in questionnaire menu page and sub pages.
     *
     * @since 1.0.2
     *
     * @return void
     */
    public function filter_admin_sidebar_menu_items() {
        global $menu, $submenu_file, $typenow;

        $hr_menu = array_filter( $menu, function ( $item ) {
            return __( 'HR Recruitment', 'erp-pro' ) === $item[0];
        } );

        $recruitment_pages = [
            'post-new.php?post_type=erp_hr_questionnaire',
            'edit.php?post_type=erp_hr_questionnaire'
        ];

        if ( in_array( $submenu_file , $recruitment_pages ) ) {
            $submenu_file = 'edit.php?post_type=erp_hr_questionnaire';
            $typenow = null;
            $_SERVER['PHP_SELF'] = 'edit.php?post_type=erp_hr_recruitment';

            add_filter( 'parent_file', function () {
                return 'edit.php?post_type=erp_hr_questionnaire';
            } );
        }

        $hr_menu_position = key( $hr_menu );

        unset( $menu[ $hr_menu_position ] );
    }
}

