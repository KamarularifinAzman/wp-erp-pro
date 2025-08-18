<div class="assset-return-form">
    <div class="row">
        <ul>
            <li>
                <?php
                erp_html_form_input( [
                    'label'    => __( 'Return Date', 'erp-pro' ),
                    'name'     => 'return_date',
                    'value'    => current_time( 'Y-m-d' ),
                    'class'    => 'assets-date-field',
                    'help'     => __( 'Asset return date', 'erp-pro' ),
                    'required' => true
                ] );
                ?>
            </li>
            <li>
                <?php
                erp_html_form_input( [
                    'label'    => __( 'Return Note', 'erp-pro' ),
                    'name'     => 'return_note',
                    'type'     => 'textarea',
                    'help'     => __( 'Add note if item has slightly or fully damaged, changed, patched etc.', 'erp-pro' ),
                ] );
                ?>
            </li>
            <li>
                <?php
                erp_html_form_input( [
                    'label'    => __( 'Lost/Damaged', 'erp-pro' ),
                    'name'     => 'is_dissmissed',
                    'type'     => 'checkbox',
                    'help'     => __( '<br>Check if item has been lost, damaged or in irreparable condition etc.', 'erp-pro' ),
                ] );
                ?>
            </li>
        </ul>
    </div>
</div>

<input type="hidden" name="allott_id">
<input type="hidden" name="item_id">
<input type="hidden" name="action" value="erp-asset-item-return">
<?php wp_nonce_field( 'erp_asset_return') ?>
