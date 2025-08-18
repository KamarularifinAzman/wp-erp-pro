<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$customer = new \WeDevs\ERP\CRM\Contact( $customer_id );

?>
<div id="sms">
    <p>
        <select name="sms_phone_number" v-model="feedData.sms_number" multiple="multiple" id="erp-crm-customer-sms-phone-number-tags" style="width:100%;">
            <option selected value="<?php echo $customer->mobile; ?>"><?php echo $customer->mobile; ?></option>
        </select>
    </p>

    <p>
        <textarea name="sms-text-editor" id="sms-text-editor" v-model="feedData.message" rows="5" style="width:100%; font-weight:normal;" placeholder="<?php _e( 'Type your SMS body...', 'erp-pro' ); ?>"></textarea>
    </p>

    <div class="submit-action clearfix">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer_id; ?>" >
        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo get_current_user_id(); ?>" >
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="sms">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="save_sms_notes" value="<?php _e( 'Send SMS', 'erp-pro' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" value="<?php _e( 'Discard', 'erp-pro' ); ?>">

        <span class="alignright" style="line-height: 2;">
            {{ feedData.message.length }} / {{ nextCharLimit }}
            <span v-if="messageCount > 1">(<?php printf( __( '%s messages', 'erp-pro' ), '{{ messageCount }}' ); ?>)</span>
        </span>
    </div>
</div>
