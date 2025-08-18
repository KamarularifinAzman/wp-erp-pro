<template>
    <div class="inventory-products">
        <h2 class="content-header__title">
            <span>{{ __( 'Sales Return Report', 'erp-pro' ) }}</span>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'ReportsOverview' }">
                {{ __( 'Back', 'erp-pro' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="fetchItems" class="query-options no-print">
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

        <p>
            <strong>{{ __( 'For the period of (Transaction date)', 'erp-pro' ) }}:</strong>
            <em>{{ applyDateFormat( startDate ) }}</em> {{ __( 'to', 'erp-pro' ) }} <em>{{ applyDateFormat( endDate ) }}</em>
        </p>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat inventory-products"
            :columns="columns"
            :rows="rows"
            :showItemNumbers="false"
            :showCb="false">

            <template slot="voucher_no" slot-scope="data">
                <strong>
                    <router-link
                        :to="{
                            name   : 'SalesReturnDetails',
                            params : {
                                id : data.row.voucher_no
                            }
                        }">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>
        </list-table>
    </div>
</template>

<script>
const HTTP       = acct_get_lib( 'HTTP' );
const ListTable  = acct_get_lib( 'ListTable' );
const Datepicker = acct_get_lib( 'Datepicker' );

export default {
    name : 'SalesReturnReport',

    components : {
        ListTable,
        Datepicker
    },

    data() {
        return {
            startDate : null,
            endDate   : null,
            rows      : [],
            columns   : {
                'voucher_no'    : {
                    label       : __( 'Voucher No', 'erp-pro' ),
                    isColPrimary: true
                },
                'trn_date'      : {
                    label       : __( 'Date', 'erp-pro' )
                },
                'customer_name' : {
                    label       : __( 'Customer', 'erp-pro' )
                },
                'product'       : {
                    label       : __( 'Product', 'erp-pro' )
                },
                'qty'           : {
                    label       : __( 'Quantity', 'erp-pro' )
                },
                'tax'           : {
                    label       : __( 'Tax', 'erp-pro' )
                },
                'discount'      : {
                    label       : __( 'Discount', 'erp-pro' )
                },
                'price'         : {
                    label       : __( 'Amount', 'erp-pro' )
                }
            },
        }
    },

    created() {
        this.$nextTick(() => {
            const dateObj  = new Date();
            const month    = ( '0' + ( dateObj.getMonth() + 1 ) ).slice( -2 );
            const year     = dateObj.getFullYear();

            this.startDate = `${year}-${month}-01`;
            this.endDate   = erp_acct_var.current_date;

            this.fetchItems();
        });
    },

    methods: {
        fetchItems() {
            this.rows = [];
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get('reports/sales/return', {
                params: {
                    start_date: this.startDate,
                    end_date  : this.endDate
                }
            }).then(response => {
                this.rows = response.data;

                this.rows.forEach(item => {
                    item.trn_date = ! item.trn_date || item.trn_date !== '0000-00-00'
                                    ? this.applyDateFormat( item.trn_date )
                                    : '-';
                });

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
            });
        },

        transformBalance(val) {
            if (null === val && typeof val === 'object') {
                val = 0;
            }

            let currency = '$';

            if (val < 0) {
                return `Cr. ${currency}${Math.abs(val)}`;
            }

            return `Dr. ${currency}${val}`;
        },

        printPopup() {
            window.print();
        },

        applyDateFormat(date) {
            return typeof this.formatDate === 'function' ? this.formatDate(date) : date;
        }
    }
}
</script>

<style lang="less">
    .content-header__title {
        padding-top: 5px !important;
    }

    .inventory-products tbody tr td:last-child {
        text-align: left !important;
    }

    .inventory-products {
        .tablenav.top,
        .tablenav.bottom {
            display: none;
        }

        .print-btn {
            float: right;
        }

        .query-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 20px 0;
        }
    }

    .inventory-products .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }

    @media print {
        .inventory-products {
            p {
                margin-bottom: 20px;

                em {
                    font-weight: bold;
                }
            }
        }

        .erp-nav-container {
            display: none;
        }

        .no-print, .no-print * {
            display: none !important;
        }
    }
</style>
