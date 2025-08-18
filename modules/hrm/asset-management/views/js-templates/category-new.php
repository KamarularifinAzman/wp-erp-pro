<ul class="category-new-field">
    <div class="row">
    <?php
        erp_html_form_input( array(
            'label'    => __( 'Category Name', 'erp-asset'),
            'name'     => 'cat_name',
            'value'    => '',
            'tag'      => 'li',
            'required' => true
        ) );
    ?>
    </div>
</ul>

<input type="hidden" name="action" id="erp-assets-action" value="erp-hr-assets-new-category">
<?php wp_nonce_field( 'erp-hr-asset-new-category' ) ?>