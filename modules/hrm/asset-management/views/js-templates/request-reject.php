<ul class="asset-request-reject">
    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label' => __( 'Reject Reason', 'erp-pro' ),
                'name'  => 'reject_reason',
                'value' => '',
                'type'  => 'textarea',
                'help'  => __( 'You can briefly add why the asset request was rejected (optional)', 'erp-pro' )
            ] );
            ?>
        </li>
    </div>
</ul>
<# console.log(data);#>
<input type="hidden" class="row-id" name="row_id" value="{{data.row_id}}">
<input type="hidden" name="action" value="erp-asset-request-reject">
<?php wp_nonce_field( 'erp-asset-request-reject' ) ?>
