<template>
    <div class="purchase-vat-report">
        <h2 class="title-container">
            <span>{{ __( 'Purchase VAT Report (Vendor Based)', 'erp-pro' ) }}</span>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'PurchaseVatReportOverview' }">
                {{ __( 'Back', 'erp-pro' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
                <div class="with-multiselect">
                    <multi-select v-model="vendor" :options="vendors" @input="getReport" />
                </div>

                <datepicker v-model="startDate" />

                <datepicker v-model="endDate" />

                <button class="wperp-btn btn--primary add-line-trigger" type="submit">
                    {{ __( 'Filter', 'erp-pro' ) }}
                </button>
            </div>


            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; {{ __( 'Print', 'erp-pro' ) }}
            </a>
        </form>

        <ul class="report-header" v-if="null !== vendor">
            <li>
                <strong>{{ __( 'Vendor Name', 'erp-pro' ) }}:</strong>
                <em> {{ vendor.name }}</em>
            </li>

            <li>
                <strong>{{ __( 'Currency', 'erp-pro' ) }}:</strong>
                <em> {{ symbol }}</em>
            </li>

            <li v-if="startDate && endDate">
                <strong>{{ __( 'For the period of (Transaction date)', 'erp-pro' ) }}:</strong>
                <em> {{ formatDate( startDate ) }}</em> to <em>{{ formatDate( endDate ) }}</em>
            </li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat purchase-vat-table"
            :columns="columns"
            :rows="taxes"
            :showCb="false">

            <template slot="voucher_no" slot-scope="data">
                <strong>
                    <router-link
                        :to="{
                            name   : 'DynamicTrnLoader',
                            params : {
                                id : data.row.voucher_no
                            }
                        }">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>

            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat( data.row.tax_amount ) }}
            </template>

            <template slot="tfoot">
                <tr class="tfoot">
                    <td></td>
                    <td>{{ __( 'Total', 'erp-pro' ) }} =</td>
                    <td>{{ moneyFormat( totalTax ) }}</td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

<script>
    import HTTP         from './../../../request';
    import { mapState } from 'vuex';

    const ListTable   = window.acct.libs['ListTable'];
    const Datepicker  = window.acct.libs['Datepicker'];
    const MultiSelect = window.acct.libs['MultiSelect'];

    export default {
        name: 'PurchaseVatVendorBased',

        components: {
            ListTable,
            Datepicker,
            MultiSelect,
        },

        data() {
            return {
                startDate : null,
                endDate   : null,
                vendor    : null,
                taxes     : [],
                symbol    : erp_acct_var.symbol,
                columns   : {
                    voucher_no : {
                        label  : __( 'Voucher No', 'erp-pro' ),
                        isColPrimary: true
                    },
                    trn_date   : {
                        label  : __( 'Transaction Date', 'erp-pro' )
                    },
                    tax_amount : {
                        label  : __( 'Tax Amount', 'erp-pro' )
                    },
                },
            };
        },

        computed: {
            ...mapState({
                vendors: state => state.purchase.vendors,
            }),

            totalTax() {
                let total = 0;

                this.taxes.forEach(item => {
                    total += parseFloat( item.tax_amount )
                });

                return total;
            }
        },

        created() {
            this.$nextTick(() => {
                const dateObj  = new Date();
                const month    = ( '0' + ( dateObj.getMonth() + 1 ) ).slice( -2 );
                const year     = dateObj.getFullYear();

                this.startDate = `${year}-${month}-01`;
                this.endDate   = erp_acct_var.current_date;

                if ( ! this.vendors.length ) {
                    this.$store.dispatch('purchase/fetchVendors', []);
                }

                if ( this.vendors[0] !== undefined ) {
                    this.vendor = this.vendors[0];
                }

                this.getReport();
            });
        },

        methods: {
            getReport() {
                if ( ! this.vendor ) {
                    return;
                }

                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get('/reports/purchase-vat', {
                    params: {
                        vendor_id  : this.vendor.id,
                        start_date : this.startDate,
                        end_date   : this.endDate
                    }
                }).then(response => {
                    this.taxes = response.data;
                    this.$store.dispatch('spinner/setSpinner', false);
                }).catch(e => {
                    this.$store.dispatch('spinner/setSpinner', false);
                });
            },

            printPopup() {
                window.print();
            }
        }
    };
</script>
