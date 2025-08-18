<ul class="asset-request-reply">
    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Category', 'erp-pro' ),
                'name'     => 'category_id',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'asset-category',
                'options'  => erp_hr_assets_get_categories_dropdown(),
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Item Name', 'erp-pro' ),
                'name'     => 'item_group',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'item-name',
                'options'  => [ '-1' => __( '&mdash;Select Group&mdash;', 'erp-pro' ) ],
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Item', 'erp-pro' ),
                'name'     => 'item',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'item',
                'options'  => [ '-1' => __( '&mdash;Select Item&mdash;', 'erp-pro' ) ],
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Given Date', 'erp-pro' ),
                'name'     => 'given_date',
                'value'    => '',
                'class'    => 'assets-date-field',
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label' => __( 'Returnable?'),
                'name'  => 'is_returnable',
                'value' => '',
                'type'  => 'checkbox',
                'class' => 'is_returnable',
                'help'  => __( 'Add return date', 'erp-pro' ),
            ] );
            ?>
        </li>
    </div>

    <div class="row" id="return-date" style="display: none">
        <li>
            <?php
            erp_html_form_input( [
                'label' => __( 'Return Date', 'erp-pro' ),
                'name'  => 'return_date',
                'value' => '',
                'class' => 'assets-date-field',
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Instructions', 'erp-pro' ),
                'name'     => 'reply_msg',
                'value'    => '',
                'type'     => 'textarea',
            ] );
            ?>
        </li>
    </div>
</ul>

<input type="hidden" class="item-id" name="item_id" value="">
<input type="hidden" class="row-id" name="row_id" value="">
<input type="hidden" name="action" value="erp-asset-request-approve">
<?php wp_nonce_field( 'erp-asset-request-approve' ) ?>
