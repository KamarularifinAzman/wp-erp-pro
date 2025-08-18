<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Manage WooCommerce Order for ERP
*
* @since 1.3.2
*
* @package WPERP|WooCommerce
*/
class Products {

    use \WeDevs\ERP\Framework\Traits\Hooker;
    use \WeDevs\ERP\Framework\Traits\Ajax;

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
     * Load class constructor
     *
     * @since 1.3.2
     * @return void
     */
    private function __construct() {
        $this->action( 'woocommerce_update_product', 'add_product' );
        $this->action( 'wp_ajax_erp_wc_sync_product_data', 'sync_product_data' );
    }

    /**
     * This method will sync a single woocommerce product after a product is created or updated.
     *
     * @since 1.3.2
     * @param $post_id int
     */
    public function add_product( $post_id ) {
        // check if product sync is active or not
        if ( ! erp_wc_is_product_sync_active() ) {
            return;
        }

        $pf = new \WC_Product_Factory();
        $product = $pf->get_product( $post_id );

        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        // skip if product type is not external or simple
        if ( ! in_array( $product->get_type(), [ 'simple', 'external' ] )  ) {
            return;
        }

        // skip if product type is not publish
        if ( $product->get_status() !== 'publish' ) {
            return;
        }

        $default_product_type   = erp_get_option( 'erp_woocommerce_default_product_type', false, 1 );
        $default_product_owner  = erp_get_option( 'erp_woocommerce_default_product_owner', false, 'self' );
        $default_product_cat    = erp_get_option( 'erp_woocommerce_default_product_cat' );
        $default_tax_cat        = erp_get_option( 'erp_woocommerce_default_tax_cat' );
        $replace_original       = erp_get_option( 'erp_woocommerce_replace_original' );

        $this->sync_product( $product, $default_product_type, $default_product_owner, $default_product_cat, $default_tax_cat, $replace_original );
    }

    /**
     * Sync all existing WooCommerce products data
     *
     * @since 1.3.2
     * @return void
     */
    public function sync_product_data() {

        $this->verify_nonce( 'erp-wc-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( array(
                'message' => __( 'Error: You are not authorized to use this action.', 'erp-pro' )
            ) );
        }

        if ( ! erp_wc_is_product_sync_active() ) {
            $this->send_error( array(
                'message' => __( 'Please enable product synchronization from WP ERP -> Settings -> WooCommerce --> Accounting.', 'erp-pro' )
            ) );
        }

        @ini_set( 'max_execution_time', '0' );

        // get required post data
        $default_product_type   = isset( $_POST['default_product_type'] )   ? absint( wp_unslash( $_POST['default_product_type'] ) ) : 1;
        $default_product_owner  = isset( $_POST['default_product_owner'] )  ? sanitize_text_field( wp_unslash( $_POST['default_product_owner'] ) ) : 'self';
        $default_product_cat    = isset( $_POST['default_product_cat'] )    ? absint( wp_unslash( $_POST['default_product_cat'] ) ) : '';
        $default_tax_cat        = isset( $_POST['default_tax_cat'] )        ? absint( wp_unslash( $_POST['default_tax_cat'] ) ) : '';
        $replace_original       = isset( $_POST['replace_original'] )       ? sanitize_text_field( wp_unslash( $_POST['replace_original'] ) ) : '';
        $total_count            = isset( $_POST['total_count'] )            ? absint( wp_unslash( $_POST['total_count'] ) ) : 0;
        $page                   = isset( $_POST['page'] )                   ? absint( wp_unslash( $_POST['page'] ) ) : 1;
        $done                   = isset( $_POST['done'] )                   ? absint( wp_unslash( $_POST['done'] ) ) : 0;

        if ( $total_count === 0 ) {
            // count no of products
            $query = new \WC_Product_Query( array(
                'status'    => array( 'publish' ),
                'type'      => array( 'simple', 'external' ),
                'limit'     => -1,
                'return'    => 'ids',
            ) );

            $product_ids = $query->get_products();

            $total_count = count( $product_ids );
        }

        // check progress
        if ( $total_count === 0 ) {
            $this->send_error( array(
                'message' => __( 'No product found to import!', 'erp-pro' )
            ) );
        }

        // get product object
        $query = new \WC_Product_Query( array(
            'status'    => 'publish',
            'type'      => array( 'simple', 'external' ),
            'limit'     => 1, // will process one product at a time
            'page'      => $page,
            'return'    => 'objects',
        ) );

        $products = $query->get_products();

        if ( ! $products ) {
            $this->send_error( array(
                'message' => __( 'No products found.', 'erp-pro' )
            ) );
        }

        $product = $products[0];

        if ( ! $product instanceof \WC_Product ) {
            $this->send_error( array(
                'message' => __( 'Invalid product object.', 'erp-pro' )
            ) );
        }

        $synced = $this->sync_product( $product, $default_product_type, $default_product_owner, $default_product_cat, $default_tax_cat, $replace_original );

        if ( $synced ) {
            $done++;
        }

        if (  $done == $total_count ) {
            $this->send_success( array(
                'done'        => $done,
                'total_count' => $total_count,
                'complete'    => true,
                'message'     => __( 'All products have been synchronized.', 'erp-pro' )
            ) );
        } else if ( $total_count == $page ) {
            if ( 0 === $done ) {
                $this->send_error( array(
                    'message' => __( 'No product was synchronized! Something might go wrong.', 'erp-pro' )
                ) );
            }

            $this->send_success( array(
                'done'        => $done,
                'total_count' => $total_count,
                'complete'    => true,
                'message'     => __( 'Some products could not be synchronized.', 'erp-pro' )
            ) );
        }

        $this->send_success( array(
            'page'         => $page + 1,
            'total_count'  => $total_count,
            'done'         => $done,
            'complete'     => false,
            'message'      => sprintf( __( '%1$d out of %2$d products synchronized', 'erp-pro' ), $done, $total_count )
        ) );
    }

    /**
     * This method will sync a single woocommerce product.
     *
     * @since 1.3.2
     * @param $product \WC_Product instance of \WC_Product class
     * @param $default_product_type int
     * @param $default_product_owner string|int
     * @param $default_product_cat int
     * @param $default_tax_cat int
     * @param $replace_original string
     */
    public function sync_product( &$product, $default_product_type, $default_product_owner, $default_product_cat = '', $default_tax_cat = '', $replace_original = '' ) {
        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        // start product sync process
        global $wpdb;

        $product_sync_type_id = erp_wc_acct_product_sync_type_id( 'woocommerce' );

        // step1: check if product is already synced
        $synced_product_id    = $this->is_product_synced( $product->get_id(), $product_sync_type_id );
        $duplicate_product_id = $this->is_product_exists( $product->get_name(), $default_product_type );

        $tax_class            = erp_wc_get_tax_class_details( $product->get_tax_class() );
        $tax_cat_id           = null;

        if ( empty( $tax_class ) && ! empty( $product->get_tax_class() ) ) {
            $tax_class = [
                'tax_rate_class_id' => false,
                'name'              => 'Standard',
                'slug'              => 'standard'
            ];
        }

        if ( ! empty( $tax_class ) ) {
            $tax_cat_id = erp_acct_get_synced_tax_system_id(
                'tax-cat',
                'woocommerce',
                $tax_class['tax_rate_class_id'],
                $tax_class['slug']
            );

            if ( empty( $tax_cat_id ) ) {
                $tax_cat_data = [
                    'name'        => $tax_class['name'],
                    'description' => 'WooCommerce Tax Class',
                ];

                $tax_cat_id = erp_acct_insert_tax_cat( $tax_cat_data );

                if ( ! is_wp_error( $tax_cat_id ) ) {

                    erp_acct_insert_synced_tax( [
                        'system_id'   => $tax_cat_id,
                        'sync_id'     => $tax_class['tax_rate_class_id'],
                        'sync_slug'   => $tax_class['slug'],
                        'sync_type'   => 'tax-cat',
                        'sync_source' => 'woocommerce'
                    ] );

                } else {
                    $tax_cat_id = null;
                }
            }

            if ( ! empty( $tax_cat_id ) ) {
                $tax_rates = \WC_Tax::get_rates_for_tax_class( $product->get_tax_class() );

                $tax_zone  = [
                    'country'  => null,
                    'state'    => null,
                    'postcode' => null,
                    'city'     => null
                ];

                foreach ( $tax_rates as $tax_rate ) {

                    $locations = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT tax_rate_country AS country, tax_rate_state AS state
                            FROM {$wpdb->prefix}woocommerce_tax_rates
                            WHERE tax_rate_id = %s",
                            $tax_rate->tax_rate_id
                        )
                    );

                    $tax_zone['country'] = $locations->country;
                    $tax_zone['state']   = $locations->state;

                    $locations = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT location_type, location_code
                            FROM {$wpdb->prefix}woocommerce_tax_rate_locations
                            WHERE tax_rate_id = %s",
                            $tax_rate->tax_rate_id
                        ),
                        ARRAY_A
                    );

                    foreach ( $locations as $loc ) {
                        $tax_zone[ $loc['location_type'] ] = $loc['location_code'];
                    }

                    $tax_zone_id   = null;
                    $tax_zone_name = [];

                    foreach ( $tax_zone as $zone ) {
                        if ( ! empty( $zone ) ) {
                            $tax_zone_name[] = $zone;
                        }
                    }

                    $tax_zone_name = ! empty( $tax_zone_name ) ? implode( '-', $tax_zone_name ) : 'General';
                    $tax_zone_slug = strtolower( $tax_zone_name );
                    $tax_zone_id   = erp_acct_get_synced_tax_system_id( 'tax-zone', 'woocommerce', false, $tax_zone_slug );

                    if ( empty( $tax_zone_id ) ) {
                        $tax_rate_data = [
                            'tax_rate_name' => $tax_zone_name,
                            'tax_number'    => '',
                            'default'       => 0
                        ];

                        $tax_zone_id = erp_acct_insert_tax_rate_name( $tax_rate_data );

                        if ( ! is_wp_error( $tax_zone_id ) ) {

                            erp_acct_insert_synced_tax( [
                                'system_id'   => $tax_zone_id,
                                'sync_slug'   => $tax_zone_slug,
                                'sync_type'   => 'tax-zone',
                                'sync_source' => 'woocommerce'
                            ] );

                        } else {
                            $tax_zone_id = null;
                        }
                    }

                    if (
                        ! empty( $tax_zone_id ) &&
                        ! empty( (float) $tax_rate->tax_rate ) &&
                        ! erp_wc_exists_tax_cat_agency( $tax_zone_id, $tax_cat_id )
                    ) {
                        $tax_rate_data = [
                            'tax_rate_name'  => $tax_zone_id,
                            'tax_components' => [
                                [
                                    'component_name'  => "WC-{$tax_rate->tax_rate_name}-{$tax_cat_id}",
                                    'tax_category_id' => $tax_cat_id,
                                    'agency_id'       => erp_wc_get_woocommerce_agency_id(),
                                    'tax_rate'        => $tax_rate->tax_rate
                                ]
                            ]
                        ];

                        $inserted = erp_acct_insert_tax_rate( $tax_rate_data );

                        foreach ( $inserted as $insert_id ) {
                            erp_acct_insert_synced_tax( [
                                'system_id'   => $insert_id,
                                'sync_id'     => $tax_rate->tax_rate_id,
                                'sync_type'   => 'tax-rate',
                                'sync_source' => 'woocommerce'
                            ] );
                        }
                    }
                }
            }
        }

        // product exist, sync product data
        $product_data = [
            'name'                  => $product->get_name( 'edit' ),
            'cost_price'            => $product->get_regular_price( 'edit' ),
            'sale_price'            => $product->get_sale_price( 'edit' ),
            'tax_cat_id'            => $tax_cat_id,
            'product_sync_type_id'  => $product_sync_type_id,
            'synced_product_id'     => $product->get_id(),
            'created_at'            => erp_current_datetime()->format( 'Y-m-d' ),
            'created_by'            => get_current_user_id()
        ];

        // fix sale price if price is zero
        if ( intval( $product_data['sale_price'] ) == 0 ) {
            $product_data['sale_price'] = $product_data['cost_price'];
        }

        $product_categories = $product->get_category_ids();

        if ( ! empty( $product_categories ) ) {
            $product_data['category_id'] = $this->get_category_id_from_woo_category_id( $product_categories[0] );
        }

        $suffix_string = ' (WC-' . $product->get_id() . ')';

        // check if product is already added
        if ( null !== $synced_product_id ) {
            // if replace existing is enabled, we'll update the product, else we'll ignore it
            if ( 'yes' === $replace_original || 'on' === $replace_original ) {
                unset( $product_data['created_at'], $product_data['created_by'] );

                $product_data['updated_at'] = erp_current_datetime()->format( 'Y-m-d' );
                $product_data['updated_by'] = get_current_user_id();

                if ( null !== $duplicate_product_id ) {
                    // why're we checking this!? cz woocommerce product update hook runs twice for each product :/
                    if ( absint( $this->get_product_synced_id( $duplicate_product_id ) ) === absint( $product->get_id() ) ) {
                        // so if it's the consecutive second run for the same product, we won't update the name
                        unset( $product_data['name'] );
                    } else {
                        $product_data['name'] = $product->get_name( 'edit' ) . $suffix_string;
                    }
                } else { // why're we doing this!? cz woocommerce product update hook runs twice for each product :/
                    $old_data = $this->get_product( $synced_product_id );
                    $new_name = ( $product->get_name( 'edit' ) . $suffix_string );

                    if ( $new_name === $old_data['name'] ) {
                        unset( $product_data['name'] );
                    }
                }

                // update the product
                $wpdb->update( "{$wpdb->prefix}erp_acct_products", $product_data, [ 'id' => $synced_product_id ] );
                return $synced_product_id;
            }

            return false;
        }

        // set default category for new import
        if ( empty( $product_categories ) && ! empty( $default_product_cat ) ) {
            $product_data['category_id'] = $default_product_cat;
        }

        // set tax category for new import
        if ( ! empty( $default_tax_cat ) && empty( $product_data['tax_cat_id'] ) ) {
            $product_data['tax_cat_id'] = $default_tax_cat;
        }

        // set vendor for new import
        if ( ! empty( $default_product_owner ) ) {
            $product_data['vendor'] = $default_product_owner;
        }

        // set product_type_id for new import
        if ( ! empty( $default_product_type ) ) {
            $product_data['product_type_id'] = $default_product_type;
        }

        // step2: check product exists with same name
        // if exists, we'll rename the product by adding a suffix
        if ( null !== $duplicate_product_id ) {
            $product_data['name'] = $product->get_name( 'edit' ) . $suffix_string;
        }

        // step3: Insert as new product
        $wpdb->insert( "{$wpdb->prefix}erp_acct_products", $product_data );

        return $wpdb->insert_id;
    }

    /**
     * This method will check if a woocommerce product is already synced with accounting product.
     *
     * @since 1.3.2
     * @param int $product_id
     * @param int $sync_type_id eg: woocommerce, edd etc
     * @return string|null
     */
    public function is_product_synced( $product_id, $sync_type_id ) {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_acct_products WHERE product_sync_type_id = %d AND synced_product_id = %d LIMIT 1",
                array( $sync_type_id, $product_id )
            )
        );
    }

    /**
     * Retrieves synced product id
     *
     * @since 1.3.3
     *
     * @param int $product_id
     *
     * @return string|null
     */
    public function get_product_synced_id( $product_id ) {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT synced_product_id FROM {$wpdb->prefix}erp_acct_products WHERE id = %d",
                $product_id
            )
        );
    }

    /**
     * This method will check if a external sourced product (eg: woocommerce, edd etc) already exists as accounting product.
     *
     * @since 1.3.2
     * @param string $product_name
     * @param int $product_type_id eg: service, inventory
     * @return string|null
     */
    public function is_product_exists( $product_name, $product_type_id ) {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_acct_products WHERE `name` = %s AND product_type_id = %d ORDER BY id LIMIT 1",
                array( $product_name, $product_type_id )
            )
        );
    }

    /**
     * This method will recursively insert woocommerce categories as accounting product categories.
     *
     * @since 1.3.2
     * @param int $category_id
     * @return bool|int|string|null
     */
    public function get_category_id_from_woo_category_id( $category_id ) {
        // get category data
        $category = get_term_by('id', $category_id, 'product_cat' );

        if ( ! $category ) {
            return false;
        }

        global $wpdb;

        // 1. check category already exists
        $existing_cat_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM `{$wpdb->prefix}erp_acct_product_categories` WHERE `name` = %s",
                array( $category->name )
            )
        );

        if ( null !== $existing_cat_id ) {
            return $existing_cat_id;
        }

        $parent = 0;

        if ( $category->parent ) {
            $parent = $this->get_category_id_from_woo_category_id( $category->parent );
        }

        // insert new category
        $category_data = [
            'name'       => $category->name,
            'parent'     => $parent,
            'created_at' => date( 'Y-m-d' ),
            'created_by' => get_current_user_id()
        ];

        $wpdb->insert( "{$wpdb->prefix}erp_acct_product_categories", $category_data );

        return $wpdb->insert_id;
    }

    /**
     * Retrieves a single product by id
     *
     * @since 1.3.3
     *
     * @param int|string $id
     *
     * @return mixed
     */
    public function get_product( $id ) {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_acct_products WHERE id = %d LIMIT 1", intval( $id )
            ),
            ARRAY_A
        );

        return $row;
    }
}
