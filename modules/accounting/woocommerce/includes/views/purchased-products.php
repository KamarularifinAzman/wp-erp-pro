<?php
if ( $customer->email ) {

    $args = [
        'meta_key' => '_billing_email',
        'customer' => $customer->email
    ];

} else if ( $customer->phone ) {

    $args = [
        'meta_key' => '_billing_phone',
        'customer' => $customer->phone
    ];

} else if ( $customer->user_id ) {

    $args = [
        'meta_key' => '_customer_user',
        'customer' => $customer->user_id
    ];

} else {
    return;
}

$products_data = erp_wc_get_customer_purchased_products( $args );
$products      = [];

foreach ( $products_data as $product_id => $value ) {
    if ( $product_id != 0 && $product_id !== null ) {
        $products [ $product_id ] =  $value ;
    }
}
?>
<div class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span><?php _e( 'Purchased Products', 'erp-pro' ) ?></span></h3>
    <div class="inside customer-latest-purchased-product-content">
        <?php if ( ! empty( $products ) ): ?>
            <ul>
                <?php
                foreach ( $products as $product_id => $value ) {
                    $product = wc_get_product( $product_id );

                    if ( ( false !== $product ) && ( null !== $product ) ) {

                        $product_title = $product->get_title();
                        $product_id    = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
                        $item_meta     = new WC_Order_Item_Product( $value['item'], $product );

                        $variations    = $item_meta->get_formatted_meta_data( '_', true );
                        ?>
                        <li>
                            <div class="erp-left">
                                <?php echo sprintf( '<a href="%s">%s</a> &times; %d', get_edit_post_link( $product_id ), $product_title, $value['qty'] );
                                foreach ( $variations as $variation ) {
                                    echo '<div class="item-variation"></div><div class="item-variation">'. $variation->display_key. ': '.$variation->value.'</div>';
                                }
                                ?>
                            </div>
                            <div class="erp-right"><?php echo wc_price( $value['line_total'] ); ?></div>
                            <div class="clearfix"></div>
                        </li>
                    <?php } ?>

                <?php } ?>
            </ul>
        <?php else: ?>
            <p class="not-found"><?php _e( 'No products found', 'erp-pro' ); ?></p>
        <?php endif ?>
    </div>
 </div><!-- .postbox -->
