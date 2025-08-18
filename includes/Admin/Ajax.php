<?php
namespace WeDevs\ERP_PRO\Admin;

// don't call the file directly
use WeDevs\ERP\ErpErrors;
use WeDevs\ERP\Framework\Traits\Hooker;

if ( ! defined('ABSPATH') ) {
    exit;
}

class Ajax {

    use \WeDevs\ERP\Framework\Traits\Ajax;
    use Hooker;

    /**
     * Load automatically all actions
     */
    public function __construct() {
        add_action( 'wp_ajax_erp-pro-toggle-extension', array( $this, 'toggle_extension' ), 10 );
        add_action( 'wp_ajax_erp-pro-toggle-module', array( $this, 'toggle_module' ), 10 );
    }

    /**
     * Toggles modules
     *
     * @return mixed
     */
    public function toggle_module() {
        $this->verify_nonce( 'wp-erp-pro-toggle-module' );

        // Check permission
        if ( current_user_can( 'manage_options' ) === false ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( isset( $_POST['module_id'] ) && ! empty( $_POST['module_id'] ) ) {
            if ( is_array( $_POST['module_id'] ) ) {
                $module_ids   = array_map( 'sanitize_text_field', wp_unslash( $_POST['module_id'] ) );
            } else {
                $module_ids[] = isset( $_POST['module_id'] ) ? sanitize_text_field( wp_unslash( $_POST['module_id'] ) ) : '';
            }
        }

        // check for valid module
        if ( true !== ( $ret = wperp()->modules->is_valid_module( $module_ids ) ) ) {
            $this->send_error( $ret->get_error_message() );
        }

        $toggle = isset( $_POST['toggle'] ) ? sanitize_text_field( wp_unslash( $_POST['toggle'] ) ) : '';

        if ( ! empty( $toggle ) && $toggle != -1 && ! empty( $module_ids ) ) {

            if ( 'activate' === $toggle ) {
                // activate module
                wperp()->modules->activate_modules( $module_ids );
            }
            elseif ( 'deactivate' === $toggle ) {
                // activate module
                wperp()->modules->deactivate_modules( $module_ids );

                // deactivate all related extensions
                $available_modules = wp_erp_pro()->module->get_available_modules( true );
                $module_id = $module_ids[0];
                $modules_to_deactivate = [];

                foreach ( $available_modules as $module ) {
                    $module     = (object) $module;
                    $deactivate = false;

                    switch ( $module_id ) {
                        case 'hrm':
                            if ( $module->is_hrm ) {
                                $modules_to_deactivate[] = $module->id;
                            }
                            break;

                        case 'crm':
                            if ( $module->is_crm ) {
                                $modules_to_deactivate[] = $module->id;
                            }
                            break;

                        case 'accounting':
                            if ( $module->is_acc ) {
                                $modules_to_deactivate[] = $module->id;
                            }
                            break;
                    }
                }

                if ( ! empty( $modules_to_deactivate ) ) {
                    wp_erp_pro()->module->deactivate_modules( $modules_to_deactivate );
                }
            }

            $this->send_success( esc_html__( 'Redirecting...', 'erp-pro' ) );

        } else {
            $this->send_error( __( 'Invalid input.', 'erp-pro') );
        }
    }

    /**
     * Toggles extensions
     *
     * @return mixed
     */
    public function toggle_extension() {
        // verify nonce
        $this->verify_nonce( 'wp-erp-pro-toggle-extension' );

        // Check permission
        if ( current_user_can( 'manage_options' ) === false ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( isset( $_POST['module_id'] ) && ! empty( $_POST['module_id'] ) ) {
            if ( is_array( $_POST['module_id'] ) ) {
                $module_ids   = array_map( 'sanitize_text_field', wp_unslash( $_POST['module_id'] ) );
            } else {
                $module_ids[] = isset( $_POST['module_id'] ) ? sanitize_text_field( wp_unslash( $_POST['module_id'] ) ) : '';
            }
        }

        $toggle = isset( $_POST['toggle'] ) ? sanitize_text_field( wp_unslash( $_POST['toggle'] ) ) : '';

        $this->toggle_extension_helper( $module_ids, $toggle );
    }

    /**
     * Helper method for toggling extension
     *
     * @param string $module_ids
     * @param string $toggle
     *
     * @return mixed
     */
    public function toggle_extension_helper( $module_ids, $toggle ) {
        // check for valid license
        if ( ! wp_erp_pro()->update->is_valid_license() ) {
            $error = __( 'Please activate your purchased license.', 'erp-pro' );
            $this->send_error( $error );
        }

        // check user limit
        if ( wp_erp_pro()->update->get_licensed_user() < wp_erp_pro()->update->count_users() ) {
            $license_id     = intval( wp_erp_pro()->update->get_license_id() );
            $purchase_url   = trailingslashit( wp_erp_pro()->update->get_base_url() ) . 'pricing?utm_source=wp-admin&utm_medium=link&utm_campaign=erp-pro-extension-page';

            if ( ! empty( $license_id ) ) {
                $purchase_url .= "&license_id=$license_id&action=upgrade" ;
            }

            $error = sprintf( __( 'Current <strong>WP ERP PRO</strong> user limit has been exceeded. Purchased Users: %d, Current Site Users: %d Please <a target="_blank" href="%s">upgrade</a> the number of users as per your business needs or <strong>delete</strong> existing users to match the user limit.', 'erp-pro' ), wp_erp_pro()->update->get_licensed_user(), wp_erp_pro()->update->count_users(), $purchase_url );
            $this->send_error( $error );
        }

        if ( ! empty( $toggle ) && $toggle != -1 && ! empty( $module_ids ) ) {
            $old_active_modules = wp_erp_pro()->module->get_active_modules();

            if ( 'activate' === $toggle ) {
                $active_modules = wp_erp_pro()->module->activate_modules( $module_ids );
            } elseif ( 'deactivate' === $toggle ) {
                $active_modules = wp_erp_pro()->module->deactivate_modules( $module_ids );
            }

            if ( $old_active_modules === $active_modules ) {
                // get activation error
                $error_obj = new ErpErrors( 'erp_pro_extension_error' );
                if ( $error_obj->has_error() ) {
                    $errors = $error_obj->get_errors();
                    $errors = implode( '</p><p>', $errors );
                    $errors = '<p>' . $errors . '</p>';
                    $this->send_error( $errors );
                }
            }

            $this->send_success( esc_html__( 'Redirecting...', 'erp-pro' ) );
        }
    }

}
