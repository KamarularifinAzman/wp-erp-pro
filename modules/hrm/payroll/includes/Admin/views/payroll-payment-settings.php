<div id="payment-settings-wrapper">
    <?php $payment_method = get_option('erp_payroll_payment_method_settings', 'cash');?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="gen_financial_month"><?php _e( 'Select a method', 'erp-pro' ); ?></label></th>
            <td>
                <div class="row">
                    <?php if ( isset($payment_method) && $payment_method == 1 ) : ?>
                        <label><input class="tiny-radio" type="radio" name="payment_method" group="pm" value="1" checked="checked"/><?php _e( 'Cash', 'erp-pro');?></label>
                    <?php else : ?>
                        <label><input class="tiny-radio" type="radio" name="payment_method" group="pm" value="1"/><?php _e( 'Cash', 'erp-pro');?></label>
                    <?php endif;?>
                    <?php if ( isset($payment_method) && $payment_method == 2 ) : ?>
                        <label><input class="tiny-radio" type="radio" name="payment_method" group="pm" value="2" checked="checked"/><?php _e( 'Cheque', 'erp-pro');?></label>
                    <?php else : ?>
                        <label><input class="tiny-radio" type="radio" name="payment_method" group="pm" value="2"/><?php _e( 'Cheque', 'erp-pro');?></label>
                    <?php endif;?>
                    <?php if ( isset($payment_method) && $payment_method == 3 ) : ?>
                        <label><input class="tiny-radio" type="radio" name="payment_method" group="pm" value="3" checked="checked"/><?php _e( 'Bank', 'erp-pro');?></label>
                    <?php else : ?>
                        <label><input class="tiny-radio" type="radio" name="payment_method" group="pm" value="3"/><?php _e( 'Bank', 'erp-pro');?></label>
                    <?php endif;?>
                </div>
            </td>
        </tr>
    </table>
</div>
