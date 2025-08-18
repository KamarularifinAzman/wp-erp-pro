<?php

/**
 * Erp Hubspot JavaScript enqueue.
 *
 * @since  1.0
 *
 * @return void
 */
function erp_hubspot_enqueue_js() {
 ?>
    <script type="text/javascript" >
    jQuery( document ).ready( function($) {

        responseDiv = $( "div#response_div" );
        $("form#erp_hubspot_sync_form").on( 'submit', function(e) {
            e.preventDefault();

            var form = $(this),
                submit = form.find('input[type=submit]'),
                loader = form.find('.sync-loader');
            submit.attr('disabled', 'disabled');
            loader.show();

            var data = {
                'action': 'erp_hubspot_sync',
                'sync_type': form.find( "input[name=sync_type]:checked" ).val(),
                'group_id': form.find( "select[name=group_id]" ).val(),
                'hubspot_list': form.find( "select[name=hubspot_list]" ).val(),
                'contact_owner': form.find( "select[name=contact_owner]" ).val(),
                'life_stage': form.find( "select[name=life_stage]" ).val(),
                '_wpnonce': '<?php echo wp_create_nonce( "erp-hubspot-sync-nonce" ); ?>'
            };

            $.post( ajaxurl, data, function(response) {
                if ( response.success ) {
                    responseDiv.html( '<span>' + response.data.message + '</span>' );

                    if ( response.data.has_more ) {
                        form.submit();
                        return;
                    } else {
                        submit.removeAttr('disabled');
                        loader.hide();
                        responseDiv.html('<span><?php _e( 'Successfully synced all contacts.', 'erp-pro' ); ?></span>');
                    }
                }
            });
        });

        $("form#erp_hubspot_sync_form").on( 'click', 'a#refresh_lists', function(e) {
            e.preventDefault();

            $( 'a#refresh_lists' ).find( 'i.fa' ).addClass( 'fa-spin' );

            var data = {
                'action': 'erp_hubspot_refresh_email_lists',
                '_wpnonce': '<?php echo wp_create_nonce( "erp-hubspot-refresh-lists-nonce" ); ?>'
            };

            $.get( ajaxurl, data, function(response) {
                if ( response.success ) {
                    $( 'a#refresh_lists' ).find( 'i.fa' ).removeClass( 'fa-spin' );

                    var html = '';

                    response.data.lists.forEach( function( item ) {
                        html += '<option value="' + item.id + '">' + item.name + '</option>';
                    });

                    $("form#erp_hubspot_sync_form").find( "select[name=hubspot_list]" ).html(html);
                }
            });
        });

        $("form#erp_hubspot_sync_form").on( 'change', 'input[name=sync_type]', function(e) {
            e.preventDefault();
            if ( $(this).val() == 'hubspot_to_contacts' ) {
                $('tbody#hubspot_to_contacts_show').show();
            } else {
                $('tbody#hubspot_to_contacts_show').hide();
            }
        });
    });
    </script> <?php
}

/**
 * Get the option.
 *
 * @return mixed
 */
function erp_hubspot_get_option( $option ) {
    $integration = get_option( 'erp_integration_settings_hubspot-integration', [] );

    if ( isset( $integration[$option] ) ) {
        return $integration[$option];
    }

    return null;
}

/**
 * Get the API Key.
 *
 * @return string
 */
function erp_hubspot_get_api_key() {
    return erp_hubspot_get_option( 'api_key' );
}

/**
 * Get Hubspot lists from options.
 *
 * @return array
 */
function erp_hubspot_get_email_lists() {
    return erp_hubspot_get_option( 'email_lists' );
}

/**
 * Get Hubspot lists from server.
 *
 * @param  string $api_key (optional)
 *
 * @return array
 */
function erp_hubspot_refresh_email_lists( $api_key = null ) {
    if( ! isset( $api_key ) ) {
        $api_key = erp_hubspot_get_api_key();
    }

    $hubspot   = new \WeDevs\ERP\Hubspot\Hubspot( $api_key );
    $lists_array = $hubspot->get_lists();
    if ( is_array( $lists_array ) ) {
        $lists = [];
        $x = 0;
        if ( isset( $lists_array['lists'] ) ) {
            foreach ( $lists_array['lists'] as $list ) {
                $lists[$x]['id']   = $list['listId'];
                $lists[$x]['name'] = $list['name'];

                $x++;
            }
        }

        return $lists;
    }

    return [];
}

/**
 * Create a contact.
 *
 * @param  $data
 *
 * @return int
 */
function erp_hubspot_create_contact( $data ) {
    $contact_id = erp_insert_people( $data );

    if ( ! is_wp_error( $contact_id ) ) {
        erp_crm_update_contact_hash( $contact_id, $data['contact_owner'] );
        erp_crm_update_life_stage( $contact_id, $data['life_stage'] );
    }

    return $contact_id;
}
