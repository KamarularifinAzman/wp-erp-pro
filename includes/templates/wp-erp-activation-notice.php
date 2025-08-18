<div class="updated" id="wp-erp-pro-installer-notice" style="padding: 1em; position: relative;">
    <h2><?php _e( 'Your ERP Pro is almost ready!', 'erp-pro' ); ?></h2>

    <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php _e( 'Dismiss this notice', 'erp-pro' ); ?>"></a>

    <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'erp-pro' ) ): ?>
        <p><?php echo sprintf( __( 'You just need to activate the <strong>%s</strong> to make it functional.', 'erp-pro' ), 'WP ERP – Complete WordPress Business Manager with HR, CRM & Accounting Systems for Small Businesses' ); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php _e( 'Activate this plugin', 'erp-pro' ); ?>"><?php _e( 'Activate', 'erp-pro' ); ?></a>
        </p>
    <?php else: ?>
        <p><?php echo sprintf( __( "You just need to install the %sCore Plugin%s to make it functional.", "erp-pro" ), '<a target="_blank" href="https://wordpress.org/plugins/erp/">', '</a>' ); ?></p>

        <p>
            <button id="wp-erp-pro-installer" class="button"><?php _e( 'Install Now', 'erp-pro' ); ?></button>
        </p>
    <?php endif ?>
</div>

<script type="text/javascript">
    ( function ( $ ) {
        $( '#wpbody' ).on( 'click', '#wp-erp-pro-installer', function ( e ) {
            e.preventDefault();
            $( this ).addClass( 'install-now updating-message' );
            $( this ).text( '<?php echo esc_js( 'Installing...', 'wp-erp' ); ?>' );

            var data = {
                action: 'wp_erp_pro_install_erp',
                _wpnonce: '<?php echo wp_create_nonce( 'wp-erp-pro-installer-nonce' ); ?>'
            };

            $.post( ajaxurl, data, function ( response ) {
                if ( response.success ) {
                    $( '#wp-erp-pro-installer-notice #wp-erp-pro-installer' ).attr( 'disabled', 'disabled' );
                    $( '#wp-erp-pro-installer-notice #wp-erp-pro-installer' ).removeClass( 'install-now updating-message' );
                    $( '#wp-erp-pro-installer-notice #wp-erp-pro-installer' ).text( '<?php echo esc_js( 'Installed', 'erp-pro' ); ?>' );
                    window.location.reload();
                }
            } );
        } );
    } )( jQuery );
</script>
