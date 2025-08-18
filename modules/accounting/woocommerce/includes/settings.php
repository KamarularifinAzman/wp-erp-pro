<?php

namespace WeDevs\ERP\WooCommerce;

use WeDevs\ERP\Settings\Template as Settings_Page;

/**
 * Settings class
 *
 * @since 1.0.0
 *
 * @package WPERP|WooCommerce
 */
class Settings extends Settings_Page {

    /**
     * Constructor function
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->id            = 'erp-woocommerce';
        $this->label         = __( 'WooCommerce', 'erp-pro' );
        $this->single_option = true;
        $this->sections      = $this->get_sections();
        $this->icon          = WPERP_ASSETS . '/images/wperp-settings/woocommerce.png';
    }

    /**
     * Get registered tabs
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_sections() {

        $sections = [
            'wc_sync'         => __( 'Synchronization', 'erp-pro' ),
            'wc_subscription' => __( 'Subscription', 'erp-pro' ),
        ];

        if ( wperp()->modules->is_module_active( 'crm' ) ) {
            $sections['crm'] = __( 'CRM', 'erp-pro' );
        }

        if ( wperp()->modules->is_module_active( 'accounting' ) ) {
            $sections['accounting'] = __( 'Accounting', 'erp-pro' );
        }

        return $sections;
    }


    /**
     * Get sections fields
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {

        if ( wperp()->modules->is_module_active( 'crm' ) ) {
            $life_stages = erp_crm_get_life_stages_dropdown_raw();
            $crm_users   = erp_crm_get_crm_user();
            $users       = [ '' => __( '&mdash; Select Owner &mdash;', 'erp-pro' ) ];

            foreach ( $crm_users as $user ) {
                $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
            }

            $fields['crm'] = [
                [
                    'title' => __( '', 'erp-pro' ),
                    'type'  => 'title',
                ],
                [
                    'title'   => __( 'Enable CRM Sync', 'erp-pro' ),
                    'type'    => 'radio',
                    'options' => [ 'yes' => __( 'Yes', 'erp-pro' ), 'no' => __( 'No', 'erp-pro' ) ],
                    'id'      => 'erp_woocommerce_is_crm_active',
                    'desc'    => __( 'Active all crm importing functionality with WooCommerce order', 'erp-pro' ),
                    'tooltip' => true,
                    'default' => 'yes'
                ],
                [
                    'title'   => __( 'When registers as a customer', 'erp-pro' ),
                    'type'    => 'select',
                    'options' => $life_stages,
                    'id'      => 'erp_woocommerce_ls_register_user',
                    'desc'    => __( 'When user register as a customer then which life stage you want to chose when contact created( default : Lead )', 'erp-pro' ),
                    'class'   => 'erp-select2',
                    'tooltip' => true,
                    'default' => 'lead'
                ],
                [
                    'title'   => __( 'When placed an order', 'erp-pro' ),
                    'type'    => 'select',
                    'options' => $life_stages,
                    'id'      => 'erp_woocommerce_ls_place_order',
                    'desc'    => __( 'When user place an order then which life stage you want to choose for a contact( default : Opportunity )', 'erp-pro' ),
                    'class'   => 'erp-select2',
                    'tooltip' => true,
                    'default' => 'opportunity'
                ],
                [
                    'title'   => __( 'When becomes a paid user', 'erp-pro' ),
                    'type'    => 'select',
                    'options' => $life_stages,
                    'id'      => 'erp_woocommerce_ls_paid_user',
                    'desc'    => __( 'When user place an order and the order is completed then which life stage you want to choose for a contact( default : Customer )', 'erp-pro' ),
                    'class'   => 'erp-select2',
                    'tooltip' => true,
                    'default' => 'customer'
                ],
                [
                    'title'   => __( 'Default Contact Owner', 'erp-pro' ),
                    'id'      => 'erp_woocommerce_contact_owner',
                    'type'    => 'select',
                    'class'   => 'erp-select2',
                    'desc'    => __( 'Default contact owner for contact.', 'erp-pro' ),
                    'options' => $users,
                    'tooltip' => true
                ],
                [
                    'type' => 'sectionend',
                    'id'   => 'erp_woocommerce_script_styling_options'
                ]
            ];
        }

        if ( wperp()->modules->is_module_active( 'accounting' ) ) {

            // get all product categories
            $product_categories = erp_acct_get_all_product_cats();
            $product_cat = [
                '' => __( 'Select Category', 'erp-pro' )
            ];
            foreach ( $product_categories as $product_category ) {
                $product_cat[ $product_category['id'] ] = $product_category['name'];
            }

            // get all tax categories
            $args = [
                'number'  => -1,
            ];

            $tax_categories = erp_acct_get_all_tax_cats( $args );
            $tax_cat = [
                '' => __( 'Select Tax Category', 'erp-pro' )
            ];

            foreach ( $tax_categories as $tax_category ) {
                $tax_cat[ $tax_category['id'] ] = $tax_category['name'];
            }

            // get all vendors
            $args = [
                'number' => '-1',
                'type'   => 'vendor',
                'no_object' => true,
            ];
            $vendors = erp_acct_get_accounting_people( $args );
            $owners = [
                'self' => __( 'Self', 'erp-pro' )
            ];
            foreach ( $vendors as $vendor ) {
                $name = $vendor['first_name'];
                $name .= ! empty( $vendor['last_name'] ) ? ' ' . $vendor['last_name'] : '';
                $owners[ $vendor['id'] ] = $name;
            }

            $product_types    = [];
            foreach ( erp_acct_get_product_types() as $product_type ) {
                $product_types[ $product_type->id ] = $product_type->name;
            }

            if ( version_compare( WPERP_VERSION , '1.5.0', '>=' ) ) {
                $fields['accounting'] = [
                    [
                        'title' => __( '', 'erp-pro' ),
                        'type'  => 'title',
                    ],
                    [
                        'title'   => __( 'Enable Order Sync', 'erp-pro' ),
                        'type'    => 'radio',
                        'options' => [ 'yes' => __( 'Yes', 'erp-pro' ), 'no' => __( 'No', 'erp-pro' ) ],
                        'id'      => 'erp_woocommerce_is_accounting_active',
                        'desc'    => __( 'Synchronize all WooCommerce orders with Accounting', 'erp-pro' ),
                        'tooltip' => true,
                        'default' => 'yes'
                    ],
                    [
                        'title'   => __( 'Enable Product Sync', 'erp-pro' ),
                        'type'    => 'radio',
                        'options' => [ 'yes' => __( 'Yes', 'erp-pro' ), 'no' => __( 'No', 'erp-pro' ) ],
                        'id'      => 'erp_woocommerce_is_product_active',
                        'desc'    => __( 'Synchronize all WooCommerce products with Accounting', 'erp-pro' ),
                        'tooltip' => true,
                        'default' => 'yes'
                    ],
                    [
                        'title'   => __( 'Update Existing Products', 'erp-pro' ),
                        'type'    => 'radio',
                        'id'      => 'erp_woocommerce_replace_original',
                        'tooltip' => true,
                        'desc'    => __( 'If enabled, existing products that match by Product name will be updated, otherwise will be skipped.', 'erp-pro' ),
                    ],
                    [
                        'title'   => __( 'Default Product Type', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $product_types,
                        'id'      => 'erp_woocommerce_default_product_type',
                        'desc'    => __( 'Default Imported Product Type (Inventory or Service).', 'erp-pro' ),
                        'tooltip' => true,
                        'default' => '1',
                        'class'   => 'erp-select2'
                    ],
                    [
                        'title'   => __( 'Default Product Owner', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $owners,
                        'id'      => 'erp_woocommerce_default_product_owner',
                        'desc'    => __( 'Default Imported Product Owner (self or vendor).', 'erp-pro' ),
                        'tooltip' => true,
                        'default' => 'self',
                        'class'   => 'erp-select2'
                    ],
                    [
                        'title'   => __( 'Default Product Category', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $product_cat,
                        'id'      => 'erp_woocommerce_default_product_cat',
                        'desc'    => __( 'If no category is assigned on WooCommerce products, selected category will be used. Leave this field blank if you don\'t want to assign any default category', 'erp-pro' ),
                        'tooltip' => true,
                        'class'   => 'erp-select2'
                    ],
                    [
                        'title'   => __( 'Default Tax Category', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $tax_cat,
                        'id'      => 'erp_woocommerce_default_tax_cat',
                        'desc'    => __( 'Default Tax Category', 'erp-pro' ),
                        'tooltip' => true,
                        'class'   => 'erp-select2'
                    ],
                    [
                        'type' => 'sectionend',
                        'id'   => 'erp_woocommerce_script_styling_options'
                    ]

                ];
            } else {
                $accounts        = erp_ac_get_chart_dropdown( [ 'exclude' => [ 1, 2, 3, 5 ] ] );
                $account_details = reset( $accounts );
                $account_list    = wp_list_pluck( $account_details['options'], 'name', 'id' );
                $deposit_to      = erp_ac_get_bank_dropdown();

                $fields['accounting'] = [
                    [
                        'title' => __( '', 'erp-pro' ),
                        'type'  => 'title',
                    ],
                    [
                        'title'   => __( 'Enable Accounting Sync', 'erp-pro' ),
                        'type'    => 'radio',
                        'options' => [ 'yes' => __( 'Yes', 'erp-pro' ), 'no' => __( 'No', 'erp-pro' ) ],
                        'id'      => 'erp_woocommerce_is_accounting_active',
                        'desc'    => __( 'Sync all accounting data with WooCommerce order', 'erp-pro' ),
                        'tooltip' => true,
                        'default' => 'yes'
                    ],
                    [
                        'title'   => __( 'Payment account', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $deposit_to,
                        'id'      => 'erp_woocommerce_payment_account_head',
                        'desc'    => __( '', 'erp-pro' ),
                        'class'   => 'erp-select2',
                        'tooltip' => true,
                        'default' => ''
                    ],
                    [
                        'title'   => __( 'Product account', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $account_list,
                        'id'      => 'erp_woocommerce_product_account_head',
                        'desc'    => __( 'Invoice or payment items/line-item account', 'erp-pro' ),
                        'class'   => 'erp-select2',
                        'tooltip' => true,
                        'default' => ''
                    ],
                    [
                        'title'   => __( 'Shipping account', 'erp-pro' ),
                        'type'    => 'select',
                        'options' => $account_list,
                        'id'      => 'erp_woocommerce_shipping_account_head',
                        'desc'    => __( 'Shipping account for invoice', 'erp-pro' ),
                        'class'   => 'erp-select2',
                        'tooltip' => true,
                        'default' => ''
                    ],
                    [
                        'type' => 'sectionend',
                        'id'   => 'erp_woocommerce_script_styling_options'
                    ]

                ];
            }

        }

        $fields['wc_sync'] = array(
            array(
                'title' => __( 'WooCommerce Synchronization', 'erp-pro' ),
                'type'  => 'title',
                'desc'  => __( '', 'erp-pro' ),
                'id'    => 'erp-ac-tax-options'
            ),
            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),
        );

        $fields['wc_sync']['sub_sections']  = $this->get_sync_sub_sections();
        $fields['wc_sync']['submit_button'] = false;

        $contact_groups = Subscription::get_contact_groups();
        $contact_groups = array_merge( [ '0' => __( 'Select a contact group', 'erp-pro' ) ], $contact_groups );


        $fields['wc_subscription'] = [
            [
                'type'  => 'title',
                'title' => __( 'Contact Group Subscription', 'erp-pro' ),
            ],
            [
                'type'    => 'checkbox',
                'title'   => __( 'Show signup on checkout', 'erp-pro' ),
                'id'      => 'show_wc_signup_option',
                'desc'    => __( 'Show a checkbox option to allow customer to signup during checkout', 'erp-pro' ),
                'default' => 'yes'
            ],
            [
                'type'    => 'text',
                'title'   => __( 'Signup option label', 'erp-pro' ),
                'id'      => 'wc_signup_option_label',
                'desc'    => __( 'This text will show next to the signup option', 'erp-pro' ),
                'default' => __( 'Signup for the newsletter', 'erp-pro' ),
            ],
            [
                'type' => 'sectionend'
            ],
        ];

        if ( wperp()->modules->is_module_active( 'crm' ) ) {
            $fields['wc_subscription'][] = [
                'type'    => 'select',
                'title'   => __( 'Default contact group', 'erp-pro' ),
                'id'      => 'default_wc_signup_contact_group',
                'desc'    => __( 'Select the a default contact group you wish to subscribe your customers to.', 'erp-pro' ),
                'options' => $contact_groups,
                'default' => 0
            ];
        }

        if ( ! empty( $section ) ) {
            return $fields[ $section ];
        }

        return $fields;
    }

    private function get_sync_sub_sections() {
        return [
            'orders'   => __( 'Order Synchronization', 'erp-pro' ),
            'products' => __( 'Product Synchronization', 'erp-pro' ),
        ];
    }
}
