<template>
    <div class="purchase-vat-report">
        <h2 class="title-container">
            <span>{{ __( 'Purchase VAT Report (Transaction Based)', 'erp-pro' ) }}</span>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'PurchaseVatReportOverview' }">
                {{ __( 'Back', 'erp-pro' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
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


        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="taxes"
            :showCb="false">

            <template slot="trn_no" slot-scope="data">
                <strong>
                    <router-link
                        :to="{
                            name   : 'DynamicTrnLoader',
                            params : {
                                id   : data.row.voucher_no,
                                type : 'purchase'
                            }
                        }">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>

            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat( parseFloat( data.row.tax_amount ) ) }}
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
    import HTTP from './../../../request';
    const ListTable  = window.acct.libs['ListTable'];
    const Datepicker = window.acct.libs['Datepicker'];

    export default {
        name: 'PurchaseVatTransactionBased',

        components: {
            ListTable,
            Datepicker,
        },

        data() {
            return {
                startDate : null,
                endDate   : null,
                taxes     : [],
                columns   : {
                    trn_no     : {
                        label  : __( 'Voucher No', 'erp' ),
                        isColPrimary: true
                    },
                    trn_date   : {
                        label  : __( 'Transaction Date', 'erp' )
                    },
                    tax_amount : {
                        label  : __( 'Tax Amount', 'erp' )
                    }
                },
            };
        },

        computed: {
            totalTax() {
                let total = 0;

                this.taxes.forEach(item => {
                    total += parseFloat( item.tax_amount );
                });

                return total;
            }
        },

        created() {
            this.$nextTick(() => {
                const dateObj  = new Date();
                const month    = ('0' + (dateObj.getMonth() + 1)).slice(-2);
                const year     = dateObj.getFullYear();

                this.startDate = `${year}-${month}-01`;
                this.endDate   = erp_acct_var.current_date;

                this.getReport();
            });
        },

        methods: {
            getReport() {
                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get('/reports/purchase-vat', {
                    params: {
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
