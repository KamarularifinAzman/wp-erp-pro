<div class="wrap">
    <h2><?php _e( 'Integrations', 'erp-pro' ); ?></h2>
    <?php
    $contacts_groups = erp_crm_get_contact_groups( [ 'number' => '-1' ] );
    $hubspot_lists = erp_hubspot_get_email_lists();

    $crm_users   = erp_crm_get_crm_user();
    $life_stages = erp_crm_get_life_stages_dropdown_raw();

    delete_option( 'erp_hubspot_sync_attempt' );
    delete_option( 'erp_hubspot_vid_offset' );
    ?>

    <?php do_action( 'erp_crm_integration_menu', 'hubspot' ); ?>

    <form action="" method="post" id="erp_hubspot_sync_form">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Sync Type', 'erp-pro' ); ?></th>
                    <td>
                        <div class="sync_type-selector">
                            <input id="contacts_to_hubspot" type="radio" name="sync_type" value="contacts_to_hubspot" checked/>
                            <label class="sync_type contacts_to_hubspot" for="contacts_to_hubspot"></label>
                            <input id="hubspot_to_contacts" type="radio" name="sync_type" value="hubspot_to_contacts" />
                            <label class="sync_type hubspot_to_contacts" for="hubspot_to_contacts"></label>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Contacts Group', 'erp-pro' ); ?></th>
                    <td>
                        <select name="group_id">
                            <option value="" selected="selected"><?php _e( '&mdash; Select Group &mdash;', 'erp-pro' ); ?></option>
                            <?php
                            foreach ( $contacts_groups as $group ) {
                            ?>
                            <option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
                            <?php
                            }
                            ?>
                        </select>

                        <p class="help"><?php _e( 'Select a specific contacts group to synchronize.', 'erp-pro' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Hubspot List', 'erp-pro' ); ?></th>
                    <td>
                        <select name="hubspot_list">
                            <?php
                            foreach ($hubspot_lists as $list) {
                                ?>
                                <option value="<?php echo $list['id']; ?>"><?php echo $list['name']; ?></option>
                                <?php
                            }
                            ?>
                        </select> <a id="refresh_lists" title="Refresh the lists" href="#"><i class="fa fa-refresh" aria-hidden="true"></i></a>


                        <p class="help"><?php _e( 'Select a specific list to synchronize.', 'erp-pro' ); ?></p>
                    </td>
                </tr>
            </tbody>
            <tbody id="hubspot_to_contacts_show" style="display: none;">
                <tr valign="top">
                    <th scope="row">
                        <label for="contact_owner"><?php _e( 'Contact Owner', 'erp-pro' ); ?></label>
                    </th>
                    <td>
                        <select name="contact_owner" id="contact_owner">
                            <option value="" selected="selected"><?php _e( '&mdash; Select Owner &mdash;', 'erp-pro' ); ?></option>
                            <?php
                            foreach ( $crm_users as $user ) {
                            ?>
                                <option value="<?php echo $user->ID; ?>"><?php echo $user->display_name; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e( 'Contact owner for the contact.', 'erp-pro' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="life_stage"><?php _e( 'Life Stage', 'erp-pro' ); ?></label>
                    </th>
                    <td>
                        <select name="life_stage" id="life_stage">
                            <?php
                            foreach ( $life_stages as $key => $value ) {
                            ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e( 'Life stage for the contact.', 'erp-pro' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <div id="response_div"></div>

        <?php wp_nonce_field( 'erp-hubspot-sync' ); ?>
        <input type="submit" name="submit_erp_hubspot_sync" class="button button-primary" value="<?php esc_attr_e( 'Synchronize', 'erp-pro' ); ?>">
        <span class="sync-loader" style="display: none;"></span>
    </form>
</div>
