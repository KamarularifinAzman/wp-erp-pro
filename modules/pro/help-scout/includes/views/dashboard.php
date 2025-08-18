<div class="wrap">
    <h2><?php _e( 'Integrations', 'erp-pro' ); ?></h2>
    <?php
    $contacts_groups = erp_crm_get_contact_groups( [ 'number' => '-1' ] );
    $crm_users   = erp_crm_get_crm_user();
    $life_stages = erp_crm_get_life_stages_dropdown_raw();
    $mailboxes = get_helpscout_mailbox();
    ?>

    <?php do_action( 'erp_crm_integration_menu', 'help_scout' ); ?>

    <form action="" method="post" id="erp_helpscout_sync_form">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Mail Box', 'erp-pro' ); ?></th>
                    <td>
                        <?php
                            foreach ( $mailboxes as $mailbox ) {
                                $mailbox_ids = helpscout_get_option('helpscout_mailbox');
                                $mailbox_ids = ! empty( $mailbox_ids ) ? $mailbox_ids : array();
                                $checked = in_array( $mailbox->id, $mailbox_ids ) ? 'checked' : '';
                            ?>
                                <label for="<?php echo $mailbox->id ?>">

                                    <input type="checkbox"  <?php echo $checked ?> id="<?php echo $mailbox->id ?>" name="mailbox" value="<?php echo $mailbox->id?>"> <?php echo $mailbox->name ?><br/>
                                </label>

                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Contacts Group', 'erp-pro' ); ?></th>
                    <td>
                         <?php
                            foreach ( $contacts_groups as $group ) {
                                $group_ids = helpscout_get_option( 'contact_group' );
                                $group_ids = ! empty( $group_ids )  ? $group_ids : array();
                                $checked = in_array( $group->id, $group_ids ) ? 'checked' : '';
                            ?>
                                <label for="group-<?php echo $group->id ?>">
                                    <input type="checkbox" id="group-<?php echo $group->id ?>" <?php echo $checked ?> name="group_ids" value="<?php echo $group->id ?>"> <?php echo $group->name; ?> <br>
                                </label>
                            <?php
                            }
                        ?>
                        <p class="help"><?php _e( 'Select contact group to synchronize.', 'erp-pro' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Customer Life Stage', 'erp-pro' ); ?></th>
                    <td>
                        <select name="helpscout_life_stage">
                            <?php

                            foreach ($life_stages as $key => $stage) {
                                $selected = helpscout_get_option('helpscout_customer_lifestage') === $key ? 'selected' : '';
                                ?>
                                <option value="<?php echo $key; ?>" <?php echo $selected ?>><?php echo $stage; ?></option>
                                <?php
                            }
                            ?>
                        <p class="help"><?php _e( 'Select customer life stage.', 'erp-pro' ); ?></p>
                    </td>
                </tr>
            </tbody>
            <tbody id="helpscout_to_contacts_show" style="display: none;">
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

        <?php wp_nonce_field( 'erp-helpscout-sync' ); ?>
        <input type="submit" name="submit_erp_helpscout_sync" class="button button-primary" value="<?php esc_attr_e( 'Synchronize', 'erp-pro' ); ?>">
        <span class="sync-loader" style="display: none;">
            <img src="<?php echo ERP_HELPSCOUT_ASSETS ?>/images/spinner-2x.gif" alt="" width="24px">
        </span>

    </form>
</div>
