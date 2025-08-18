<template>
    <div class="wperp-transactions-section wperp-section">

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="content-header-section separator">
                <div class="wperp-row wperp-between-xs">
                    <div class="wperp-col">
                        <h2 class="content-header__title">{{ __('Sales Returns', 'erp-pro') }}</h2>
                        <input type="number"
                            v-if="showSearchVoucher"
                            v-model="voucher_no"
                            class="pull-left voucher-input"
                            @keyup="bindInputEvent"
                            :placeholder="__('Invoice Voucher No.', 'erp-pro')"
                            style="margin-right: 5px;" />
                        <button class="wperp-btn btn--primary add-line-trigger pull-left"
                            @click="toggleSearchVoucher"
                            v-if="! voucher_no.trim().length">
                            {{ invoiceBtnText }}
                        </button>
                        <button type="submit"
                            class="wperp-btn btn--primary add-line-trigger pull-left"
                            v-if="voucher_no.trim().length"
                            @click="searchVoucher">
                            {{ __("Search", "erp-pro") }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>{{ __('Trash', 'erp') }}</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>

            <transactions-filter :status="false" :people="{title: 'Customer', items: customers}"/>

            <list-table
                :loading="listLoading"
                tableClass="wperp-table table-striped table-dark widefat table2 transactions-table"
                action-column="actions"
                :columns="columns"
                :rows="rows"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="[]"
                @action:click="onActionClick">
                <template slot="trn_no" slot-scope="data">
                    <strong v-if="isPayment(data.row)">
                        <router-link :to="{ name: 'PayPurchaseSingle', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                    <strong v-else>
                        <router-link :to="{ name: 'SalesReturnDetails', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <template slot="type" slot-scope="data">
                   {{ __( "Sales Return", "erp-pro" ) }}
                </template>
                <template slot="customer_name" slot-scope="data">
                    {{   data.row.customer_name }}
                </template>
                <template slot="trn_date" slot-scope="data">
                    {{ data.row.trn_date }}
                </template>
                <template slot="amount" slot-scope="data">
                    {{ formatAmount(data.row.sales_amount) }}
                </template>
                <template slot="status" slot-scope="data">
                    {{ data.row.status }}
                </template>

                <!-- custom row actions -->
                <template slot="action-list" slot-scope="data">
                    <li v-for="(action, index) in data.row.actions" :key="action.key" :class="action.key">
                        <a href="#" @click.prevent="onActionClick(action.key, data.row, index)">
                            <i :class="action.iconClass"></i>{{ action.label }}
                        </a>
                    </li>
                </template>

            </list-table>

        </div>
    </div>
</template>

<script>
const  ListTable = window.acct.libs['ListTable'];
const  TransactionsFilter = window.acct.libs['TransactionsFilter'] ;
const Swal = window.acct.libs['Swal'] ;

import {mapState} from "vuex";
import HTTP from './../../request';

export default {
    name: 'SalesReturnList',
    components: {
        ListTable,
        TransactionsFilter
    },

    data() {
        return {
            columns       : {
                trn_no       : { label:  __('Voucher No.', 'erp'), isColPrimary: true },
                type         : { label: __('Type', 'erp') },
                customer_name: { label: __('Customer', 'erp') },
                trn_date     : { label: __('Trn Date', 'erp') },
                amount       : { label: __('Total', 'erp') },
                status       : { label: __('Status', 'erp') },
                actions      : { label: '' }
            },
            listLoading   : false,
            rows          : [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions       : [],
            fetched       : false,

            voucher_no: '',
            invoice: {
                line_items: []
            },

            showSearchVoucher: false,
            invoiceBtnText: __( 'Create Return Invoice', 'erp-pro' ),
        };
    },

    computed: mapState({
        vendors: state => state.purchase.vendors,
        customers: state => state.sales.customers
    }),

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.$root.$on('transactions-filter', filters => {
            this.fetchItems(filters);
            this.fetched = true;
        });
        const filters = {};
        // Get start & end date from url on page load
        if (this.$route.query.start && this.$route.query.end) {
            filters.start_date = this.$route.query.start;
            filters.end_date   = this.$route.query.end;
        }
        if (!this.fetched) {
            this.fetchItems(filters);
        }
        if(!this.customers.length){
            HTTP.get('/people', {
                params: {
                    type    : 'customer',
                    per_page: -1,
                    page    : 1,
                }
            }).then(response => {
                this.$store.dispatch('sales/fillCustomers', response.data);
            });
        }
    },

    methods: {
        async fetchItems(filters = {}) {
            this.rows = [];
            let data =  {
                per_page  : this.paginationData.perPage,
                page      : this.$route.params.page === undefined ? this.paginationData.currentPage: this.$route.params.page,
                start_date: filters.start_date,
                end_date  : filters.end_date,
                status    : filters.status,
                type      : filters.type,
                vendor_id : filters.people_id
            } ;
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/sales-return/list', data ).then(response => {
                const mappedData = response.data.map(item => {
                    item['actions'] = [ { key: '#', label: __('No actions found', 'erp') } ];
                    return item;
                });

                this.rows = mappedData;
                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                this.$store.dispatch('spinner/setSpinner', false);
            });
        },

        async searchVoucher() {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/sales-return/search-invoice/'+ this.voucher_no).then(response => {
                this.invoice               = response.data ;
                this.invoice.amount        = parseFloat(this.invoice.amount);
                this.invoice.discount      = parseFloat(this.invoice.discount);
                this.invoice.tax           = parseFloat(this.invoice.tax);
                this.invoice.return_reason = '';
                this.invoice.line_items.map(item => {
                    item.unit_price     = parseFloat( item.unit_price );
                    item.tax            = ( parseFloat( item.tax ) || 0) / parseFloat(item.qty);
                    item.existing_qty   = item.qty;
                    item.return_qty     = parseFloat(item.return_qty);
                    item.qty            = parseFloat( item.qty ) - ( parseFloat(item.return_qty) || 0 );
                    item.returnable_qty = item.qty;
                    item.discount       = parseFloat( item.discount ) / item.qty;
                })
                this.$store.dispatch('spinner/setSpinner', false);

                if ( ! this.invoice.id ) {
                    return Swal.fire( __( 'No voucher found!', 'erp-pro' ), '', 'error' )  ;
                }

                this.$router.push({
                    path: `/transactions/sales/return/${this.voucher_no}/create`,
                    params: { invoice: this.invoice }
                });
            }) ;
        },

        onActionClick(action, row, index) {
        },

        bindInputEvent(e) {
            if(e.keyCode === 13 || e.which === 13) {
                this.searchVoucher();
            }
        },

        goToPage(page) {
        },

        isPayment(row) {
            return row.type === 'payment' || row.type === 'return_payment';
        },

        getTrnType(row) {
            if (row.type === 'purchase') {
                if (row.purchase_order === '1') {
                    return 'Purchase Order';
                }
                return 'Purchase';
            } else {
                return 'Pay Purchase';
            }
        },

        toggleSearchVoucher() {
            this.showSearchVoucher = ! this.showSearchVoucher;

            this.invoiceBtnText = this.showSearchVoucher ? __( 'Cancel', 'erp-pro' ) : __( 'Create Return Invoice', 'erp-pro' )
        }
    }
};
</script>

<style lang="less">
    .transactions-table {
        .tablenav,
        .column-cb,
        .check-column {
            display: none;
        }
    }
    h4.top-title-bar{
        margin-top:25px
    }
</style>
