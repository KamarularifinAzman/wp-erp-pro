<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get helpscout access token
 *
 * @return string
 */
function get_helpscout_access_token() {
    $app_id             = helpscout_get_option( 'helpscout_app_id' );
    $app_secret         = helpscout_get_option( 'helpscout_app_secret' );
    $access_token       = helpscout_get_option( 'helpscout_access_token' );
    $token_expire_time  = helpscout_get_option( 'token_expire_time' );
    $time_diff          = time() - (int) $token_expire_time;

    if ( ! $access_token ) {
        $access_token = generate_helpscout_access_token( $app_id, $app_secret );
    } else if( $time_diff >= 7200 ) {
        $access_token = generate_helpscout_access_token( $app_id, $app_secret );
    }
    $access_token = generate_helpscout_access_token( $app_id, $app_secret );
    return $access_token;
}

/**
 * Generate access token
 *
 * @param  string $app_id     Help Scout Application ID
 * @param  string $app_secret Help Scout Application Secret
 * @return string            Help Scout Access token
 */
function generate_helpscout_access_token( $app_id, $app_secret ) {
    if ( ! $app_id && ! $app_secret ) {
        return;
    }
    $options = get_option( 'erp_integration_settings_helpscout' );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, 'https://api.helpscout.net/v2/oauth2/token' );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id={$app_id}&client_secret={$app_secret}" );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );

    $response = curl_exec( $ch );
    $response = json_decode( $response );
    $token = isset( $response->access_token ) ? $response->access_token : '';
    if ( empty( $token ) ) {
        return;
    }
    $options['helpscout_access_token'] = $token;
    $options['token_expire_time'] = time();
    curl_close ($ch);
    update_option( 'erp_integration_settings_helpscout', $options );
    return $token;
}

/**
 * Get customer email
 *
 * @param  string $email_link Helpscout customer email link
 *
 * @return string  customer email
 */
function get_helpscout_customer_email( $email_link ) {
    if ( ! $email_link ) {
        return;
    }
    $access_token = helpscout_get_option( 'helpscout_access_token' );
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token
        )
    );

    $request = wp_remote_get( $email_link, $args );
    $body = wp_remote_retrieve_body( $request );
    $response = json_decode( $body );
    $email = '';

    if ( ! empty( $response->_embedded->emails[0] ) ) {
        $email = $response->_embedded->emails[0]->value;
    }
    return $email;
}

/**
 * Get helpscout users
 *
 * @return array helpscout
 */
function get_helpscout_users() {
    $access_token = get_helpscout_access_token();
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token
        )
    );

    $request    = wp_remote_get( 'https://api.helpscout.net/v2/users', $args );
    $body       = wp_remote_retrieve_body( $request );
    $response   = json_decode( $body );
    $users      = array();
    if ( ! empty( $response->_embedded->users ) ) {
        $users = $response->_embedded->users;
    }

    return $users;
}

/**
 * Get helpscout mailboxes
 *
 * @return array helpscout mailbox
 */
function get_helpscout_mailbox() {
    $access_token = get_helpscout_access_token();

    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token
        )
    );

    $request    = wp_remote_get( 'https://api.helpscout.net/v2/mailboxes', $args );
    $body       = wp_remote_retrieve_body( $request );
    $response   = json_decode( $body );
    $mailboxes  = array();

    if ( ! empty( $response->_embedded->mailboxes ) ) {
        $mailboxes =  $response->_embedded->mailboxes;
    }
    return $mailboxes;
}
/**
 * Get helpscout options
 *
 * @param  string $option Helpscout option
 * @return string         helpscout option
 */
function helpscout_get_option( $option ) {
    if ( ! $option ) {
        return;
    }
    $options = get_option( 'erp_integration_settings_helpscout' );
    $option = ! empty( $options[ $option ] ) ? $options[ $option ] : '';
    return $option;
}

/**
 *  Check erp-crm-contact
 *
 * @return boolean crm-contact-page
 */
function is_erp_crm_contact() {
    $page = isset( $_GET['page'] ) ? $_GET['page'] : '';
    $section = isset( $_GET['section'] ) ? $_GET['section'] : '';

    if ( $page === 'erp-crm' && $section == 'contacts' ) {
        return  true;
    }

    return false;
}

/**
 * Check configuration on helpscout
 *
 * @return boolean
 */
function is_connected_helpscout() {
    $app_id             = helpscout_get_option( 'helpscout_app_id' );
    $app_secret         = helpscout_get_option( 'helpscout_app_secret' );

    if ( ! empty( $app_id ) && ! empty( $app_secret ) ) {
        return true;
    }
    return false;
}

/**
 *  Get customer life stage
 *
 * @return string
 */
function get_customer_life_stage() {
    $life_stage = helpscout_get_option( 'helpscout_customer_lifestage' );

    if ( $life_stage ) {
        return $life_stage;
    }
    return false;
}

/**
 * Helpscout webhook
 *
 * @return [type] [description]
 */
function create_helpscout_webhook() {
    $secrete        = md5( time() );
    $access_token   = get_helpscout_access_token();
    $data   =  json_encode( array(
        'url'   => site_url( 'wp-json/erp/helpscout/v1/customer' ),
        'events'    => array( 'customer.created' ),
        'secret'    => $secrete,
    ), JSON_UNESCAPED_SLASHES );
    $args = array(
        'body'      =>  $data,
        'headers'   =>  array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-type' => 'application/json; charset=UTF-8'
        )
    );
    $response = wp_remote_post( 'https://api.helpscout.net/v2/webhooks',  $args );
    return $response;
}

function get_helscout_user_by_mailbox( $mailbox_id ) {
    $access_token = get_helpscout_access_token();
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token
        )
    );

    $request    = wp_remote_get( 'https://api.helpscout.net/v2/users?mailbox='.$mailbox_id, $args );
    $body       = wp_remote_retrieve_body( $request );
    $response   = json_decode( $body );
    $users      = array();
    if ( ! empty( $response->_embedded->users ) ) {
        $users = $response->_embedded->users;
    }

    return $users;
}

/**
 * Add recent helpscout tickets on contact page
 *
 * @since 1.0.0
 */
function erp_helpscout_customer_activity_on_hs(){
  ?>
    <div class="postbox erp-helpscout-activity-hs loading">
        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="erp-hndle"><span><?php _e( 'Recent tickets on HelpScout', 'erp' ); ?></span></h3>
        <div class="inside">
        </div>
    </div><!-- .postbox -->
<?php
}
add_action('erp_crm_contact_left_widgets', 'erp_helpscout_customer_activity_on_hs');
// add_action( 'admin_init', 'get_helscout_access_token' );
