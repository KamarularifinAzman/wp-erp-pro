<template>
    <div class="purchase-vat-report">
        <h2 class="title-container">
            <span>{{ __( 'Purchase VAT Report (Category Based)', 'erp-pro' ) }}</span>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'PurchaseVatReportOverview' }">
                {{ __( 'Back', 'erp-pro' ) }}
            </router-link>
        </h2>

        <form action="" method="" @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
                <div class="with-multiselect">
                    <multi-select v-model="taxCategory" :options="taxCategories" @input="getReport" />
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

        <ul class="report-header" v-if="null !== taxCategory">
            <li>
                <strong>{{ __( 'Category Name', 'erp-pro' ) }}:</strong>
                <em> {{ taxCategory.name }}</em>
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
                                id   : data.row.voucher_no,
                                type : 'purchase'
                            }
                        }">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>

            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat( parseFloat(data.row.tax_amount) ) }}
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
    const ListTable   = window.acct.libs['ListTable']  ;
    const Datepicker  = window.acct.libs['Datepicker']  ;
    const MultiSelect = window.acct.libs['MultiSelect']  ;

    export default {
        name: 'PurchaseVatCategoryBased',

        components: {
            ListTable,
            Datepicker,
            MultiSelect
        },

        data() {
            return {
                startDate      : null,
                endDate        : null,
                taxCategory    : null,
                taxCategories  : [],
                taxes          : [],
                symbol         : erp_acct_var.symbol,
                columns        : {
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

        created() {
            this.$nextTick(() => {
                const dateObj  = new Date();
                const month    = ( '0' + ( dateObj.getMonth() + 1 ) ).slice( -2 );
                const year     = dateObj.getFullYear();

                this.startDate = `${year}-${month}-01`;
                this.endDate   = erp_acct_var.current_date;

                this.fetchData();
            });
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

        watch: {
            taxCategory() {
                this.rows = [];
            }
        },

        methods: {
            fetchData() {
                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get('/tax-cats').then(res => {
                    this.taxCategories = res.data;
                }).then(() => {
                    if ( this.taxCategories && this.taxCategories[0] !== undefined ) {
                        this.taxCategory = this.taxCategories[0];
                        this.getReport();
                    }

                    this.$store.dispatch('spinner/setSpinner', false);
                });
            },

            getReport() {
                if ( ! this.taxCategory ) {
                    return this.$store.dispatch('spinner/setSpinner', false);
                }

                this.$store.dispatch('spinner/setSpinner', true);
                this.rows = [];

                HTTP.get('/reports/purchase-vat', {
                    params: {
                        category_id : this.taxCategory.id,
                        start_date  : this.startDate,
                        end_date    : this.endDate
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
