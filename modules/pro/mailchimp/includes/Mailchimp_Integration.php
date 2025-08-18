<?php
namespace WeDevs\ERP\Mailchimp;

use WeDevs\ERP\Integration;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Mailchimp Integration
 */
class Mailchimp_Integration extends Integration {

    use Hooker;

    /**
     * Class constructor.
     */
    function __construct() {
        $this->id          = 'mailchimp-integration';
        $this->title       = __( 'Mailchimp', 'wp-erp' );
        $this->description = __( 'Mailchimp Add-on for WP-ERP.', 'wp-erp' );

        $this->init_settings();
        $this->filter( $this->get_option_id() . '_filter', 'update_option_filter' );

        parent::__construct();
    }

    /**
     * Get the title of this setting.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Get the description of this setting.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Get the fields of this setting.
     *
     * @return array
     */
    public function init_settings() {
        $desc_text = '<a target="_blank" href="https://admin.mailchimp.com/account/api">' . __( 'Get your API key here.', 'erp-pro' ) . '</a>';

        if ( erp_mailchimp_get_api_key() ) {
            $url       = admin_url( 'admin.php?page=erp-crm&section=integration&sub-section=mailchimp&action=disconnect' );
            $desc_text = '<a class="wperp-btn btn--secondary" href="' . $url . '">' . __( 'Disconnect', 'erp-pro' ) . '</a>';
        }

        $this->form_fields = [
            [
                'title'             => __( 'API Key', 'erp-pro' ),
                'id'                => 'api_key',
                'type'              => 'text',
                //'custom_attributes' => ['placeholder' => __( 'Your MailChimp API key', 'erp-pro' ) ], //todo: getting frontend error for placeholder
                'desc'              => $desc_text,
            ],
        ];

        return $this->form_fields;
    }

    /**
     * Add filter of this setting.
     *
     * @since 1.2.0 added sync data in the saved option
     *
     * @param  array $update_options
     *
     * @return array|\Wp_Error
     */
    public function update_option_filter( $update_options ) {
        if ( ! isset ( $update_options['api_key'] ) ) {
            return $update_options;
        }

        $mailchimp = new Mailchimp( $update_options['api_key'] );

        if ( ! $mailchimp->is_connected() ) {
            return new \WP_Error( 'invalid-api-key', __( 'Invalid API key. Enter correct one!', 'erp-pro' ) );
        }

        $update_options['email_lists'] = erp_mailchimp_refresh_email_lists( $update_options['api_key'] );

        if ( ! isset( $_POST['sync_data'] ) ) {
            return new \WP_Error( 'invalid-form-data', __( 'Sync settings data missing.', 'erp-pro' ) );
        }

        $sync_settings = wp_unslash( $_POST['sync_data'] );

        if ( ! isset( $sync_settings['group_to_email_lists'] ) ) {
            return new \WP_Error( 'invalid-form-data', __( 'Group to Mailchimp email list mapping data missing.', 'erp-pro' ) );
        }

        //sanitize sync data: contact group to mailchimp mapping
        foreach ( $sync_settings['group_to_email_lists'] as $group_id => $settings ) {
            $sanitized_settings = [];

            if ( isset( $settings['auto_sync'] ) ) {
                $sanitized_settings['auto_sync'] = sanitize_text_field( $settings['auto_sync'] );
            }

            if ( ! isset( $settings['email_lists'] ) || ! is_array( $settings['email_lists'] ) ) {
                $sync_settings['group_to_email_lists'][ $group_id ] = $sanitized_settings;
                continue;
            }

            $sanitized_settings['email_lists'] = [];

            foreach ( $settings['email_lists'] as $index => $data ) {
                $sanitized_data = [];

                if ( isset( $data['id'] ) ) {
                    $sanitized_data['id'] = sanitize_text_field( $data['id'] );
                }

                if ( isset( $data['name'] ) ) {
                    $sanitized_data['name'] = sanitize_text_field( $data['name'] );
                }

                if ( isset( $data['stats']['member_count'] ) ) {
                    $sanitized_data['stats'] = [ 'member_count' => sanitize_text_field( $data['stats']['member_count'] ) ];
                }

                $sanitized_settings['email_lists'][ $index ] = $sanitized_data;
            }

            $sync_settings['group_to_email_lists'][ $group_id ] = $sanitized_settings;
        }

        if ( ! isset( $sync_settings['email_list_to_groups'] ) ) {
            return new \WP_Error( 'invalid-form-data', __( 'Mailchimp email list to Group mapping data missing.', 'erp-pro' ) );
        }

        //sanitize sync data: mailchimp list to contact group mapping
        foreach ( $sync_settings['email_list_to_groups'] as $email_list_id => $settings ) {
            $sanitized_settings = [];

            if ( isset( $settings['auto_sync'] ) ) {
                $sanitized_settings['auto_sync'] = sanitize_text_field( $settings['auto_sync'] );
            }

            if ( isset( $settings['groups'] ) && is_array( $settings['groups'] ) ) {
                foreach ( $settings['groups'] as $index => $data ) {
                    if ( isset( $data['id'] ) ) {
                        $data['id'] = sanitize_text_field( $data['id'] );
                    }

                    if ( isset( $data['name'] ) ) {
                        $data['name'] = sanitize_text_field( $data['name'] );
                    }

                    $settings['groups'][ $index ] = $data;
                }

                $sanitized_settings['groups'] = $settings['groups'];
            }

            $data_keys = ['contact_owner', 'life_stage'];

            foreach ( $data_keys as $data_key ) {
                if ( ! isset( $settings[ $data_key ] ) || ! is_array( $settings[ $data_key ] ) ) {
                    continue;
                }

                $fields = ['id', 'name'];

                foreach ( $fields as $field ) {
                    if ( ! isset( $settings[ $data_key ][ $field ] ) ) {
                        continue;
                    }

                    if ( 'life_stage' === $data_key ) {
                        $settings[ $data_key ][ $field ] = sanitize_title_with_dashes( $settings[ $data_key ][ $field ] );
                    } else {
                        $settings[ $data_key ][ $field ] = sanitize_text_field( $settings[ $data_key ][ $field ] );
                    }
                }

                $sanitized_settings[ $data_key ] = $settings[ $data_key ];
            }

            $sync_settings['email_list_to_groups'][ $email_list_id ] = $sanitized_settings;
        }

        if ( ! isset( $_POST['email_lists'] ) || empty( $sync_settings ) ) {
            return new \WP_Error( 'invalid-form-data', __( 'Mailchimp email list is missing.', 'erp-pro' ) );
        }

        $email_lists_posted = wp_unslash( $_POST['email_lists'] );

        foreach ( $email_lists_posted as $index => $email_list ) {
            if ( isset( $email_list['id'] ) ) {
                $email_lists_posted[ $index ]['id'] = sanitize_text_field( $email_list['id'] );
            }

            if ( isset( $email_list['name'] ) ) {
                $email_lists_posted[ $index ]['name'] = sanitize_text_field( $email_list['name'] );
            }

            if ( isset( $email_list['stats'] ) && isset( $email_list['stats']['member_count'] ) ) {
                $email_lists_posted[ $index ]['stats'] = [ 'member_count' => sanitize_text_field( $email_list['stats']['member_count'] ) ];
            }
        }

        $mailchimp_email_lists = [];

        foreach ( $email_lists_posted as $email_list ) {
            $email_list_exists = $this->find_email_list( $update_options['email_lists'], $email_list['id'] );

            if ( ! $email_list_exists ) {
                unset( $sync_settings['email_list_to_groups'][ $email_list['id'] ] );
                continue;
            }

            $mailchimp_email_lists[] = $email_list;

            if ( isset( $email_list['id'] ) && isset( $sync_settings['email_list_to_groups'] ) && isset( $sync_settings['email_list_to_groups'][ $email_list['id'] ] ) ) {
                $error_message = $this->validate_mailchimp_to_erp_data( $sync_settings['email_list_to_groups'][ $email_list['id'] ] );
            }

            if ( empty( $error_message ) ) {
                continue;
            }

            // translators: %1$s is field names and %2$s is email list name
            $error_message = sprintf( __( '%1$s are can\'t be empty for Email List: %2$s', 'erp-pro' ), implode( "\n", $error_message ), $mailchimp_email_lists['name'] );

            return new \WP_Error( 'invalid-form-data', $error_message );
        }

        $error_message = $this->webhook_manage( $mailchimp_email_lists, $sync_settings['email_list_to_groups'], $update_options['api_key'] );

        if ( ! empty( $error_message ) ) {
            // translators: %1$s is field names and %2$s is email list name
            $error_message = implode( "\n", $error_message );

            return new \WP_Error( 'invalid-form-data', $error_message );
        }

        $update_options['sync_data'] = $sync_settings;

        return $update_options;
    }

    /**
     * Validate the incoming data
     *
     * @since 1.2.0
     *
     * @param $sync_setting
     *
     * @return array
     */
    private function validate_mailchimp_to_erp_data( $sync_setting ) {
        $error_message = [];

        if ( ! $sync_setting['auto_sync'] ) {
            return $error_message;
        }

        if ( empty( $sync_setting['groups'] ) ) {
            $error_message[] = __( 'Contact Group', 'erp-pro' );
        }

        if ( empty( $sync_setting['contact_owner'] ) ) {
            $error_message[] = __( 'Contact Owner', 'erp-pro' );
        }

        if ( empty( $sync_setting['life_stage'] ) ) {
            $error_message[] = __( 'Life Stage', 'erp-pro' );
        }

        return $error_message;
    }

    /**
     * Create or delete webhooks as necessary based on settings
     *
     * @since 1.2.0
     *
     * @param array $mailchimp_email_lists
     * @param array $sync_settings
     * @param string $api_key
     *
     * @return array
     */
    private function webhook_manage( $mailchimp_email_lists, $sync_settings, $api_key ) {
        $messages = [];

        foreach ( $sync_settings as $email_list_id => $sync_setting ) {
            $email_list = $this->find_email_list( $mailchimp_email_lists, $email_list_id );

            $webhook_manager = new Webhook_Controller( $email_list_id, $api_key );

            if ( empty( $sync_setting['auto_sync'] ) || 'false' === $sync_setting['auto_sync'] ) {
                if ( false === $webhook_manager->delete_webhook() ) {
                    $messages[] = __( 'Can\'t stop sync for email list: ', 'erp-pro' ) . ( is_array( $email_list ) ? $email_list['name'] : '' );
                }
            } else {
                if ( false === $webhook_manager->create_webhook() ) {
                    $messages[] = __( 'Can\'t enable sync for email list: ', 'erp-pro' ) . ( is_array( $email_list ) ? $email_list['name'] : '' );
                }
            }
        }

        return $messages;
    }

    /**
     * Find email list by id
     *
     * @since 1.2.0
     *
     * @param $mailchimp_email_lists
     * @param $email_list_id
     *
     * @return bool|array
     */
    private function find_email_list($mailchimp_email_lists, $email_list_id ) {
        foreach ($mailchimp_email_lists as $existing_list ) {
            if ( $email_list_id === $existing_list['id'] ) {
                return $existing_list;
            }
        }

        return false;
    }
}
