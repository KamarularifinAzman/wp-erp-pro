<div class="wrap erp">
    <div class="payitem-template-container">
        <div class="row">
            <label><?php _e( 'Pay item (required)', 'erp-pro' ); ?></label>
            <input name="pi_name" id="pi_name" value="" tabindex="1" type="text">
            <input type="hidden" id="pi_id" name="pi_id" value="">
        </div>
        <div class="row">
            <label><?php _e( 'Select Pay item category (required)', 'erp-pro' ); ?></label>
            <?php $pi_cat = []; // erp_payroll_get_pay_item_category_name();?>
            <select name="pi_category" id="pi_category">
                <?php foreach ( $pi_cat as $pay_item_category ) : ?>
                    <option value="<?php echo $pay_item_category['id'];?>">
                        <?php echo $pay_item_category['payitem_category'];?>
                    </option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="row">
            <label><?php _e( 'Description', 'erp-pro' ); ?></label>
            <textarea name="pi_description" id="pi_description" tabindex="3"></textarea>
        </div>
    </div>
</div>
