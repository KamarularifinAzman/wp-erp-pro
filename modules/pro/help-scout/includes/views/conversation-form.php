<style>
#helpscout-nav-content {
    padding: 10px 15px
}
.mailbox {
    width: 35% !important;
    float: left !important;;
}
.mailbox select {
    width: 85% !important;
    line-height: 35px !important
}
.users {
    width: 60% !important;;
    float: right !important;;
}
.users select {
    width: 100% !important;
    line-height: 35px !important;
}
.subject input, .message textarea {
    width: 100% !important;
}
.subject input {
    padding:8px 5px;
}
#helpscout-nav-content #response_div span {
    font-size: 14px;
    color: #8a6d3b;
    background-color: #fcf8e3;
    border-color: #faebcc;
    padding: 10px;
    text-align: center;
    margin: 10px 0;
    border: 1px solid transparent;
    border-radius: 4px;
    display: block;
    width:100%;
</style>
<div id="helpscout-nav-content" v-show="tabShow === 'helpscout'">
    <?php if ( is_connected_helpscout() ) :?>
    <form action="" method="POST" id="conversation-form">
        <div id="response_div"></div>

        <div class="helpscout-form-group mailbox">
            <p>
                <label for="mailbox"><?php _e( 'Mail Box', 'erp-pro' ) ?></label>
            </p>
            <p>
                <select id="mailbox" >
                    <option value="">Select Helscout Mailbox</option>
                    <?php if ( $helpscout_mailboxes ): ?>
                        <?php foreach( $helpscout_mailboxes as $mailbox ):?>
                            <option value="<?php echo $mailbox->id ?>"><?php echo $mailbox->name;?></option>
                        <?php endforeach?>
                    <?php endif ?>
                </select>
                <span class="user-loader" style="display: none">
                    <img src="<?php echo ERP_HELPSCOUT_ASSETS ?>/images/spinner-2x.gif" alt="" width="18px">
                </span>
            </p>
        </div>
        <div class="helpscout-form-group users">
            <p>
                <label for="users"><?php _e( 'Users', 'erp-pro' ) ?></label>
            </p>
            <p>
                <select id="users" >
                    <option value="">Select Helscout User</option>
                </select>

            </p>
        </div>
        <div class="helpscout-form-group subject">
            <label for="helpscout_subject"><?php _e( 'Subject', 'erp-pro' ) ?></label>
            <input type="text" name="helpscout_subject" id="helpscout_subject" >
        </div>
        <div class="helpscout-form-group message">
           <p>
                <label for="helpscout-message"><?php _e( 'Message', 'erp-pro' ) ?></label>
            </p>

            <p>
                <textarea rows="5" id="helpscout-message" ></textarea>
            </p>
        </div>
        <div class="helpscout-form-group">
            <input type="submit" value="Send Message" class="button button-primary updating-message" name="submit-helpscout-message" id="helpscout-send-message">
            <span class="sync-loader" style="display: none">
                <img src="<?php echo ERP_HELPSCOUT_ASSETS ?>/images/spinner-2x.gif" alt="" width="24px">
            </span>
        </div>
        <input type="hidden" id="customer-email" value="<?php  echo $contact->email ?>">
    </form>
    <?php else:
        $configure_link = admin_url( 'admin.php?page=erp-settings#/erp-integration' );
        _e( '<p>WP ERP HelpScout plugin is not configured correctly. Please configure the plugin from
            the following <b><a href="'.$configure_link.'">link</a></b></p>', 'erp-pro' );
    endif;
    ?>
</div>
