<?php
namespace WeDevs\Payroll;

/**
 * General class
 */
class Settings {

    private $id = 'payroll';

    /**
     * Class constructor
     */
    private function __construct() {
        add_filter( 'erp_settings_hr_sections', function ( $sections ) {
            return $sections + [ $this->id  => __( 'Payroll', 'erp-pro' ) ];
        } );

        add_filter( 'erp_settings_hr_section_fields', [ $this, 'payroll_section_fields' ], 10, 2 );
    }

    /**
     * Initializes the class
     *
     * Checks for an existing instance
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
     * Settings fields for payroll
     *
     * @param array $fields
     * @param array $sections
     *
     * @return array
     */
    public function payroll_section_fields( $fields, $sections ) {
        $sub_sections = $this->get_subsections();

        $sub_sections = $this->get_subsections();

        $data         = [
            'sub_sections' => $sub_sections
        ];

        foreach ($sub_sections as $key => $sub_section) {
            $data[ $key ] = $this->get_subsection_fields( $key );
        }

        $fields[ $this->id ] = $data;

        return $fields;
    }

    /**
     * Get sections
     *
     * @return array
     */
    private function get_subsections() {
        $sub_sections = array(
            'general' => __( 'Accounting Settings', 'erp-pro' ),
            'payment' => __( 'Payment Settings', 'erp-pro' ),
            'payitem' => __( 'Pay Item Settings', 'erp-pro' )
        );

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            array_shift( $sub_sections );
        }

        return apply_filters( 'erp_get_sections_' . $this->id, $sub_sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    private function get_subsection_fields( $section = '' ) {
        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $fields['general'] = [];
        } else {
            $assets_head = [];
            $ah          = erp_ac_get_ledger_by_class_id( 1 );

            foreach ( $ah as $key => $value ) {
                $assets_head[ $value->id ] = $value->name;
            }

            $expense_head = [];
            $ah           = erp_ac_get_ledger_by_class_id( 3 );

            foreach ( $ah as $key => $value ) {
                $expense_head[ $value->id ] = $value->name;
            }

            $expense_head_tax = [];
            $ah               = erp_ac_get_ledger_by_class_id( 3 );

            foreach ( $ah as $key => $value ) {
                $expense_head_tax[ $value->id ] = $value->name;
            }

            $fields['general'] = [
                [
                    'title' => __( '', 'erp-pro' ),
                    'type'  => 'title'
                ],
                [
                    'title'   => 'Account head for Assets',
                    'id'      => 'erp_payroll_account_head_assets',
                    'type'    => 'select',
                    'options' => $assets_head
                ],
                [
                    'title'   => 'Account head for salary reporting',
                    'id'      => 'erp_payroll_account_head_salary',
                    'type'    => 'select',
                    'options' => $expense_head
                ],
                [
                    'title'   => 'Account head for tax reporting',
                    'id'      => 'erp_payroll_account_head_salary_tax',
                    'type'    => 'select',
                    'options' => $expense_head_tax
                ],
                [
                    'type' => 'sectionend',
                    'id'   => 'script_styling_options'
                ]
            ];
        }

        $pm = [
            'cash'   => __( 'Cash', 'erp-pro' ),
            'cheque' => __( 'Cheque', 'erp-pro' ),
            'bank'   => __( 'Bank', 'erp-pro' )
        ];

        $fields['payment'] = [
            [
                'title' => __( 'Payment Method Selection', 'erp-pro' ),
                'type'  => 'title'
            ],
            [
                'title'   => __( 'Select a method', 'erp-pro' ),
                'id'      => 'erp_payroll_payment_method_settings',
                'type'    => 'select',
                'options' => $pm
            ]
        ];

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $fields['payment'][] = [
                'title'   => __( 'Select a bank', 'erp-pro' ),
                'id'      => 'erp_payroll_payment_bank_settings',
                'type'    => 'select',
                'value'   => get_option( 'erp_payroll_payment_bank_settings' ),
                'options' => array_column( erp_acct_get_banks(), 'name', 'id' )
            ];
        }

        $fields['payment'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        $fields['payitem'] = [
            [
                'title' => __( 'Pay Item Settings', 'erp-pro' ),
                'type'  => 'title'
            ],
            [
                'title'   => __( 'Pay Type', 'erp-pro' ),
                'id'      => 'paytype',
                'type'    => 'select',
                'options' => erp_payroll_paytypes()
            ],
            [
                'title'   => __( 'Pay Item', 'erp-pro' ),
                'id'      => 'payitem',
                'type'    => 'text'
            ],
            [
                'type'     => 'payitem_settings',
                'nonce'    => wp_create_nonce( 'payroll_nonce' )
            ]
        ];

        $fields['payitem']['submit_button'] = false;

        $section = $section === false ? $fields['general'] : ( isset( $fields[ $section ] ) ? $fields[ $section ] : [] );

        return apply_filters( 'erp_payroll_settings_section_fields', $section );
    }
}
