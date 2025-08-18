<?php
namespace WeDevs\ERP\WooCommerce;

/**
 * WooCommerce Tax handler class
 *
 * @since 1.4.0
 */
class Tax {

    /**
     * Class constructor
     *
     * @since 1.4.0
     */
    private function __construct() {

        // Action hook when woocommerce tax rate is updated
        add_action( 'woocommerce_tax_rate_updated', [ $this, 'update_tax_rate' ], 10, 2 );
    }

    /**
     * Initializes the Order class
     *
     * Checks for an existing Order instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Runs synchronization of tax when
     * tax rate is updated in WooCommerce
     *
     * @since 1.4.0
     *
     * @param int|string $tax_rate_id
     * @param array $tax_data
     *
     * @return void
     */
    public function update_tax_rate( $tax_rate_id, $tax_data ) {
        global $wpdb;

        $system_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT system_id
                FROM {$wpdb->prefix}erp_acct_synced_taxes
                WHERE sync_type = 'tax-rate'
                AND sync_source = 'woocommerce'
                AND sync_id = %d",
                [ $tax_rate_id ]
            ),
            ARRAY_A
        );

        if ( empty( $system_data ) || is_wp_error( $system_data ) ) {
            return;
        }

        foreach ( $system_data as $data ) {
            $wpdb->update(
                $wpdb->prefix . 'erp_acct_tax_cat_agency',
                [
                    'tax_rate'   => $tax_data['tax_rate'],
                    'updated_at' => gmdate( 'Y-m-d' ),
                    'updated_by' => get_current_user_id()
                ],
                [
                    'id'         => $data['system_id']
                ]
            );
        }
    }
}
