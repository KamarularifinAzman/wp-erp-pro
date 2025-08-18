<?php
namespace WeDevs\ERP_PRO\Admin;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP_PRO\Admin\Menu\Extensions;

class Admin {

    use Hooker;

    private $update;

    private $message;

    public function __construct() {
        // get instance of update class
        $this->update = isset( wp_erp_pro()->update )
            ? wp_erp_pro()->update
            : \WeDevs\ERP_PRO\Admin\Update::init();

        $this->message = '';

        $this->action( 'admin_menu', 'admin_menu', 99 );
        $this->action( 'admin_menu', 'remove_addons_menu', 999 );
        $this->filter( 'pre_erp_hr_employee_args', 'add_new_employee', 10, 1 );
    }

    public function add_new_employee( $data ) {

        if ( ! $this->update->is_valid_license() ) {
            // if there is no valid license, don't bother user
            return $data;
        }

        // check existing user count
        if ( $this->update->count_users() < $this->update->get_licensed_user() ) {
            // user is in limit, so don't bother user
            return $data;
        }

        $message = __( 'Current WP ERP PRO user limit has been exceeded. Please upgrade the number of users in order to add new Employee.', 'erp-pro' );

        return new \WP_Error( 'user-limit-exceeded', $message );
    }

    public function remove_addons_menu() {
        // remove addons menu
        remove_submenu_page('erp', 'erp-addons');

        // remove modules menu
        remove_submenu_page('erp', 'erp-modules');

    }

    public function admin_menu() {
        $extension_slug = add_submenu_page( 'erp', __( 'Modules', 'erp' ), __( 'Modules', 'erp' ), 'manage_options', 'erp-extensions', [ $this, 'extension_menu' ] );
        add_action( 'load-' . $extension_slug , array( $this,'load_extensions' ) );

        if (
            wp_erp_pro()->module->is_active( 'hubspot' ) ||
            wp_erp_pro()->module->is_active( 'mailchimp' ) ||
            wp_erp_pro()->module->is_active( 'salesforce' ) ||
            wp_erp_pro()->module->is_active( 'help_scout' ) ||
            wp_erp_pro()->module->is_active( 'awesome_support' )
        ) {
            erp_add_menu( 'crm', array(
                'title'         =>  __( 'Integrations', 'erp-pro' ),
                'slug'          =>  'integration',
                'capability'    =>  erp_crm_get_manager_role(),
                'callback'      =>  [ $this, 'integration_page' ],
                'position'      =>  40
            ) );
        }
    }

    public function integration_page() {
        $integrations = [ 'hubspot', 'mailchimp', 'salesforce', 'help_scout', 'awesome_support' ];

        foreach ( $integrations as $integration ) {
            if ( wp_erp_pro()->module->is_active( $integration ) ) {
                $default = $integration;
                break;
            }
        }

        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : $default;

        if ( wp_erp_pro()->module->is_active( $sub_section ) ) {
            $action = 'erp_crm_' . $sub_section . '_page';
            do_action( $action );
        }
    }

    public function extension_menu() {
        $obj = Extensions::init();
        $obj->entry();
    }

    public function load_extensions() {
        $obj = Extensions::init();
        $obj->on_load_page();
    }
}
