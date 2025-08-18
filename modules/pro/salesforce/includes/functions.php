<?php

/**
 * ERP Salesforce JavaScript enqueue.
 *
 * @since  1.0
 *
 * @return void
 */
function erp_salesforce_enqueue_js() { ?>
    <script type="text/javascript" >
    jQuery( document ).ready( function($) {

        var responseDiv = $( "div#response_div" );
        $("form#erp_salesforce_sync_form").on( 'submit', function(e) {
            e.preventDefault();

            var form = $(this),
                submit = form.find('input[type=submit]'),
                loader = form.find('.sync-loader');
            submit.attr('disabled', 'disabled');
            loader.show();

            var data = {
                'action': 'erp_salesforce_sync',
                'sync_type': form.find( "input[name=sync_type]:checked" ).val(),
                'group_id': form.find( "select[name=group_id]" ).val(),
                'salesforce_list': form.find( "select[name=salesforce_list]" ).val(),
                'contact_owner': form.find( "select[name=contact_owner]" ).val(),
                'life_stage': form.find( "select[name=life_stage]" ).val(),
                '_wpnonce': '<?php echo wp_create_nonce( "erp-salesforce-sync-nonce" ); ?>'
            };

            $.post( ajaxurl, data, function(response) {
                if ( response.success ) {
                    responseDiv.html( '<span>' + response.data.message + '</span>' );
                    if ( response.data.left > 0 ) {
                        form.submit();
                        return;
                    } else {
                        submit.removeAttr('disabled');
                        loader.hide();
                        responseDiv.html('<span><?php _e( 'Successfully synced all contacts.', 'erp-pro' ); ?></span>');
                    }
                } else {
                    submit.removeAttr('disabled');
                    loader.hide();
                    responseDiv.html( '<span>' + response.data.message + '</span>' );
                }
            });
        });

        $("form#erp_salesforce_sync_form").on( 'click', 'a#refresh_lists', function(e) {
            e.preventDefault();

            $( 'a#refresh_lists' ).find( 'i.fa' ).addClass( 'fa-spin' );

            var data = {
                'action': 'erp_salesforce_refresh_contact_lists',
                '_wpnonce': '<?php echo wp_create_nonce( "erp-salesforce-refresh-lists-nonce" ); ?>'
            };

            $.get( ajaxurl, data, function(response) {
                if ( response.success ) {
                    $( 'a#refresh_lists' ).find( 'i.fa' ).removeClass( 'fa-spin' );

                    var html = '';

                    response.data.lists.forEach( function( item ) {
                        html += '<option value="' + item.id + '">' + item.name + '</option>';
                    });

                    $("form#erp_salesforce_sync_form").find( "select[name=salesforce_list]" ).html(html);
                }
            });
        });

        $("form#erp_salesforce_sync_form").on( 'change', 'input[name=sync_type]', function(e) {
            e.preventDefault();
            if ( $(this).val() == 'salesforce_to_contacts' ) {
                $('tbody#salesforce_to_contacts_show').show();
            } else {
                $('tbody#salesforce_to_contacts_show').hide();
            }
        });

        <?php
        if ( isset( $_GET['section'] ) && $_GET['section'] == 'salesforce' ) {
        ?>
            $(".erp-settings p.submit").remove();
        <?php
        }
        ?>
    });
    </script> <?php
}

/**
 * Get the option.
 *
 * @return mixed
 */
function erp_salesforce_get_option( $option ) {
    $integration = get_option( 'erp_integration_settings_salesforce-integration', [] );

    if ( isset( $integration[$option] ) ) {
        return $integration[$option];
    }

    return null;
}

/**
 * Update the options.
 *
 * @param  array $options
 *
 * @return boolean
 */
function erp_salesforce_update_options( $options ) {
    $saved_options = get_option( 'erp_integration_settings_salesforce-integration', [] );
    $options       = array_merge( $saved_options, $options );

    return update_option( 'erp_integration_settings_salesforce-integration', $options );
}

/**
 * Get the Instance URL.
 *
 * @return string
 */
function erp_salesforce_get_instance_url() {
    return erp_salesforce_get_option( 'instance_url' );
}

/**
 * Get the Access Token.
 *
 * @return string
 */
function erp_salesforce_get_access_token() {
    return erp_salesforce_get_option( 'access_token' );
}

/**
 * Get the Refresh Token.
 *
 * @return string
 */
function erp_salesforce_get_refresh_token() {
    return erp_salesforce_get_option( 'refresh_token' );
}

/**
 * Get Salesforce lists from options.
 *
 * @return array
 */
function erp_salesforce_get_contact_lists() {
    return erp_salesforce_get_option( 'contact_lists' );
}

/**
 * Get Salesforce lists from server.
 *
 * @return array
 */
function erp_salesforce_refresh_contact_lists() {
    $instance_url  = erp_salesforce_get_instance_url();
    $access_token  = erp_salesforce_get_access_token();
    $refresh_token = erp_salesforce_get_refresh_token();

    $salesforce = new \WeDevs\ERP\Salesforce\Salesforce( $instance_url, $access_token,  $refresh_token );
    $lists_array = $salesforce->get_lists();

    $lists = [];

    if ( is_array( $lists_array ) && isset( $lists_array['listviews'] ) ) {
        foreach ( $lists_array['listviews'] as $list ) {
            $lists[] = [
                'id' => $list['id'],
                'name' => $list['label'],
            ];
        }
    }

    return $lists;
}

/**
 * Create a contact.
 *
 * @param  $data
 *
 * @return int
 */
function erp_salesforce_create_contact( $data ) {
    $contact_id = erp_insert_people( $data );

    if ( ! is_wp_error( $contact_id ) ) {
	    erp_crm_update_contact_hash( $contact_id, $data['contact_owner'] );
	    erp_crm_update_life_stage( $contact_id, $data['life_stage'] );
    }

    return $contact_id;
}
