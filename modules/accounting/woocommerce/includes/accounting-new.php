<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Accounting data manupulation class
*
* @since 1.2.0
*/
class Accounting {

    /**
     * $order_id int
     *
     * @var object
     */
    protected $order_id;

    /**
     * $line_items int
     *
     * @var object
     */
    protected $line_items;

    /**
     * $order object
     *
     * @var object
     */
    protected $order;

    /**
     * Contact info
     */
    protected $customer;

    /**
     * Tax agency id
     */
    protected $tax_agency_id;

    /**
     * Autometically loaded when class initiate
     *
     * @since 1.2.0
     */
    public function __construct( $order_id = NULL, $contact_id = 0 ) {
        if ( $order_id ) {
            $this->order_id             = $order_id;
            $this->order                = new \WC_Order( $order_id );

            $this->order->tax_zone_id   = null;
            $this->order->tax_zone_name = null;
        }

        $this->tax_agency_id = erp_wc_get_woocommerce_agency_id();
    }

    /**
    * Create a customer
    *
    * @since 1.2.0
    *
    * @return void
    **/
    public function create_customer( $id ) {
        if ( !$id ) {
            return;
        }

        $people   = \WeDevs\ERP\Framework\Models\People::find( $id );

        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( 'customer' )->first();

        if ( ! empty( $type_obj ) && ! $people->hasType( 'customer' ) ) {
            $people->assignType( $type_obj );
        }

        $this->customer = $people;

        return $this->customer;
    }

    /**
    * Create a transaction base of order status
    *
    * @since 1.2.0
    *
    * @return void
    **/
    public function create_transaction() {
        global $wpdb;
        global $current_user;

        $order_status = $this->order->get_status();

        if ( ! $this->customer->id ) {
            return;
        }

        $transaction_id          = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );
        $transaction_payment_id  = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_payment_id', true );
        $already_inserted_status = get_post_meta( $this->order->get_id(), '_erp_wc_order_status', true );

        if ( $transaction_id && ( $already_inserted_status == $order_status ) ) {
            // no need for duplicate entries
            return;
        }

        // check previous order status, if status is void, then empty transaction id
        $invoice = erp_acct_get_invoice( $transaction_id );

        if (
            ! empty( $invoice['status'] ) &&
            (
                $invoice['status'] == erp_acct_trn_status_by_id( 'void' ) ||
                $invoice['status'] == erp_acct_trn_status_by_id( 'returned' )
            )
        ) {
            // delete old transaction reference so that we can create new invoice and payment
            $transaction_id = '';
            delete_post_meta( $this->order->get_id(), '_erp_ac_transaction_id' );
        }

        $address  = $this->order->get_address();

        $tax_zone = [
            $address['country'],
            $address['state'],
            $address['postcode'],
            $address['city']
        ];

        foreach ( $tax_zone as $index => $zone ) {
            if ( empty( $zone ) ) {
                unset( $tax_zone[ $index ] );
            }
        }

        $tax_zone_name = ! empty( $tax_zone ) ? implode( '-', $tax_zone ) : 'General';
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

        if ( ! empty( $tax_zone_id ) ) {
            $this->order->tax_zone_id   = $tax_zone_id;
            $this->order->tax_zone_name = $tax_zone_name;
        }

        $billing_address    = explode( '<br/>', $this->order->get_formatted_billing_address() );

        if ( ! empty( $billing_address ) ) {
            unset( $billing_address[0] );
            unset( $billing_address[1] );
            $billing_address = implode( ', ', $billing_address );
        }

        $invoice_fields = [
            'customer_id'     => $this->customer->id,
            'customer_name'   => $this->customer['first_name'] . ' ' . $this->customer['last_name'],
            'trn_date'        => $this->order->get_date_created()->date( 'Y-m-d' ),
            'due_date'        => $this->order->get_date_created()->date( 'Y-m-d' ),
            'billing_address' => $billing_address,
            'amount'          => $this->order->get_subtotal(),
            'discount'        => $this->order->get_discount_total(),
            'discount_type'   => 'discount-value',
            'shipping'        => $this->order->get_shipping_total(),
            'shipping_tax'    => $this->order->get_shipping_tax(),
            'tax'             => $this->order->get_total_tax() - $this->order->get_shipping_tax(),
            'estimate'        => 0,
            'status'          => erp_acct_trn_status_by_id('awaiting_payment'),
            'tax_rate_id'     => $tax_zone_id,
            'attachments'     => null,
            'particulars'     => 'from woocommerce',
            'currency'        => get_woocommerce_currency(),
        ];

        $payment_fields = [
            'customer_id'      => $this->customer->id,
            'customer_name'    => $this->customer['first_name'] . ' ' . $this->customer['last_name'],
            'trn_date'         => $this->order->get_date_created()->date( 'Y-m-d' ),
            'amount'           => $this->order->get_subtotal() + $this->order->get_total_tax(),
            'trn_by'           => 1,
            'trn_by_ledger_id' => $this->get_ledger_id_by_slug('cash'),
            'deposit_to'       => $this->get_ledger_id_by_slug('cash'),
            'status'           => erp_acct_trn_status_by_id('closed'),
            'attachments'      => null,
            'particulars'      => 'from woocommerce',
            'currency'         => get_woocommerce_currency(),
            'ref'              => 'WC-' . $this->order->get_id(),
        ];

        $current_user->add_cap( 'erp_ac_publish_sales_invoice' );

        //prepare line items
        $this->prepare_line_item();

        if ( 'completed' == $order_status ) {

            if ( empty( $transaction_id ) ) {
                $invoice_fields['line_items'] = $this->line_items;

                // create new invoice with paid status
                $invoice_fields['status'] = erp_acct_trn_status_by_id('paid');

                $order_status = 'pending_payment';

                $trn = erp_acct_insert_invoice( $invoice_fields );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $trn['voucher_no'] );
                update_post_meta( $this->order->get_id(), '_erp_wc_order_status', $order_status );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s created successfully and status set as  awaiting payment', 'erp-pro' ),  $trn['voucher_no'] );

                $this->create_transaction();
            } else {
                $payment_fields['line_items'] = $this->line_items;

                $trn = erp_acct_insert_payment( $payment_fields );
                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_payment_id', $trn['voucher_no'] );
                update_post_meta( $this->order->get_id(), '_erp_wc_order_status', 'completed' );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Payment: %s created successfully', 'erp-pro' ), $trn['voucher_no'] );
            }
        } else if ( 'cancelled' == $order_status ||  'failed' == $order_status ) {

            if ( empty( $transaction_id ) ) {
                // create new invoice with void status
                $invoice_fields['line_items'] = $this->line_items;
                $trn = erp_acct_insert_invoice( $invoice_fields );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $trn['voucher_no'] );
                update_post_meta( $this->order->get_id(), '_erp_wc_order_status', $order_status );

                // void newly created invoice
                erp_acct_void_invoice( $trn['voucher_no'] );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s is void', 'erp-pro' ),  $trn['voucher_no'] );
            } else {
                // void associated invoice
                erp_acct_void_invoice( $transaction_id );

                //void associated payment if exist
                erp_acct_void_payment( $transaction_payment_id );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s is void', 'erp-pro' ),  $transaction_id );
            }

        } else if ( 'refunded' == $order_status ) {

            if ( ! empty( $transaction_id ) ) {
                $invoice_fields['sales_voucher_no'] = $transaction_id;
                $invoice_fields['line_items']       = $this->line_items;

                $trn_no = erp_acct_insert_sales_return( $invoice_fields );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s returend successfully', 'erp-pro' ), $trn_no );
            }

        } else {
            // remove associated invoice if exists
            erp_acct_void_invoice( $transaction_id );

            //void associated payment if exist
            erp_acct_void_payment( $transaction_payment_id );

            $invoice_fields['line_items'] = $this->line_items;
            $trn = erp_acct_insert_invoice( $invoice_fields );

            $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

            update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $trn['voucher_no'] );
            update_post_meta( $this->order->get_id(), '_erp_wc_order_status', $order_status );

            $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s created successfully and status set as  awaiting payment', 'erp-pro' ),  $trn['voucher_no'] );
        }

        if ( empty( $trn['voucher_no'] ) && empty( $transaction_id ) ) {
            $this->order->add_order_note( 'Something went wrong.' );
        } else {
            $this->order->add_order_note( $order_note_mesg );
        }
    }

    /**
    * Prepare line item
    *
    * @since 1.2.0
    *
    * @return
    **/
    public function prepare_line_item() {
        global $wpdb;

        $this->line_items = []; // reset old data

        $items = $this->order->get_items();

        if ( ! $items ) {
            return;
        }

        $transaction_id     = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );

        if ( 'completed' == $this->order->get_status() && $transaction_id ) {
            $this->line_items[] = [
                'invoice_no' => $transaction_id,
                'amount'     => $this->order->get_subtotal(),
                'line_total' => $this->order->get_total()
            ];
        } else {
            foreach ( $items as $item ) {
                $tax_rate_agency = [];
                $tax_cat_id      = null;
                $tax_rate        = 0;
                $tax_class       = erp_wc_get_tax_class_details( $item->get_tax_class() );

                if ( ! empty( $item['taxes']['total'] ) ) {
                    $tax_amount  = $item['taxes']['total'];
                    $tax_rate_id = key( $tax_amount );

                    if ( ! empty( $tax_rate_id ) ) {
                        $tax_rate = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT tax_rate
                                FROM {$wpdb->prefix}woocommerce_tax_rates
                                WHERE tax_rate_id = %d", $tax_rate_id
                            )
                        );
                    } else {
                        $tax_rate    = (float) $item['subtotal_tax'] / (float) $item['total'] * 100.00;

                        $tax_rate_id = $wpdb->get_var(
                            "SELECT tax_rate_id
                            FROM {$wpdb->prefix}woocommerce_tax_rates
                            WHERE tax_rate_class = '' OR tax_rate_class IS NULL"
                        );
                    }

                    $tax_rate_agency[] = [
                        'agency_id' => $this->tax_agency_id,
                        'tax_rate'  => $tax_rate,
                    ];

                    if ( empty( $tax_class ) ) {
                        $tax_class = [
                            'tax_rate_class_id' => false,
                            'name'              => 'Standard',
                            'slug'              => 'standard'
                        ];
                    }
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

                    if (
                        ! empty( $this->order->tax_zone_id ) &&
                        ! empty( $tax_cat_id ) &&
                        ! empty( (float) $tax_rate ) &&
                        ! erp_wc_exists_tax_cat_agency( $this->order->tax_zone_id, $tax_cat_id )
                    ) {
                        $tax_rate_data = [
                            'tax_rate_name'  => $this->order->tax_zone_id,
                            'tax_components' => [
                                [
                                    'component_name'  => "WC-{$this->order->tax_zone_name}-{$tax_cat_id}",
                                    'tax_category_id' => $tax_cat_id,
                                    'agency_id'       => $this->tax_agency_id,
                                    'tax_rate'        => $tax_rate
                                ]
                            ]
                        ];

                        $inserted = erp_acct_insert_tax_rate( $tax_rate_data );

                        foreach ( $inserted as $insert_id ) {
                            erp_acct_insert_synced_tax( [
                                'system_id'   => $insert_id,
                                'sync_id'     => $tax_rate_id,
                                'sync_type'   => 'tax-rate',
                                'sync_source' => 'woocommerce'
                            ] );
                        }
                    }
                }

                $this->line_items[] = [
                    'tax_cat_id'         => $tax_cat_id,
                    'tax_rate_agency'    => $tax_rate_agency,
                    'invoice_details_id' => ! empty( $transaction_id ) ? $this->parse_invoice_details_id( $transaction_id, $item['product_id'] ) : null,
                    'product_id'         => isset( $item['product_id'] ) ? intval( $item['product_id'] ) : 0,
                    'qty'                => $item['qty'],
                    'unit_price'         => wc_format_decimal( $item['total'] / $item['qty'] ),
                    'discount'           => wc_format_decimal( $item['subtotal'] - $item['total'] ),
                    'item_total'         => $item['total'],
                    'line_total'         => $item['total'],
                    'tax_rate'           => $tax_rate,
                    'tax'                => isset( $item['subtotal_tax'] ) ? wc_format_decimal( $item['subtotal_tax'] ) : 0,
                    'ecommerce_type'     => 'woocommerce'
                ];
            }
        }
    }

    /**
     * Parses invoice details id
     *
     * @since 1.4.0
     *
     * @param int|string $invoice_id
     * @param int|string $product_id
     *
     * @return int|string|null
     */
    public function parse_invoice_details_id( $invoice_id, $product_id ) {
        global $wpdb;

        $id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                FROM {$wpdb->prefix}erp_acct_invoice_details
                WHERE trn_no = %d
                AND product_id = %d",
                [ $invoice_id, $product_id ]
            )
        );

        if ( is_wp_error( $id ) || empty( $id ) ) {
            return null;
        }

        return $id;
    }

    /**
     * Get ledger id by slug
     *
     * @return int
     */
    public function get_ledger_id_by_slug( $slug ) {
        $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();

        return $ledger_map->get_ledger_id_by_slug( $slug );
    }

}
