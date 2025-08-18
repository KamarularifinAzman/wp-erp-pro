<template>
    <div class="wperp-transactions-section wperp-section">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Purchase Returns', 'erp-pro') }}</h2>
                    <input type="number" v-model="voucher_no" class="pull-left voucher-input" v-if="showSearchVoucher" @keyup="bindInputEvent" :placeholder="__('Purchase Voucher No.', 'erp-pro')" style="margin-right: 5px;" />
                    <button v-if="! voucher_no.trim().length"
                        @click="toggleSearchVoucher"
                        class="wperp-btn btn--primary add-line-trigger pull-left">
                        {{ invoiceBtnText }}
                    </button>
                    <button type="submit"
                        v-if="voucher_no.trim().length"
                        @click="searchVoucher"
                        class="wperp-btn btn--primary add-line-trigger pull-left">
                        {{ __( 'Search', 'erp-pro' ) }}
                    </button>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>{{ __('Trash', 'erp') }}</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>

            <transactions-filter :status="false" :people="{title: 'Vendor', items: vendors}"/>

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
                    <strong>
                        <router-link :to="{ name: 'PurchaseReturnDetails', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <template slot="type" slot-scope="data">
                   {{ __("Purchase Return", "erp") }}
                </template>
                <template slot="customer_name" slot-scope="data">
                    {{ data.row.vendor_name }}
                </template>
                <template slot="trn_date" slot-scope="data">
                    {{ data.row.bill_trn_date }}
                </template>
                <template slot="amount" slot-scope="data">
                    {{ formatAmount( parseFloat(data.row.amount) + parseFloat(data.row.tax) - parseFloat(data.row.discount) ) }}
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

import HTTP from './../../request';

const ListTable  = window.acct.libs['ListTable'];
const TransactionsFilter = window.acct.libs['TransactionsFilter'];
const Swal = window.acct.libs['Swal'] ;

import {mapState} from "vuex";

export default {
    name: 'PurchaseReturnList',

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

            filterTypes:[
                { id: 'purchase', name: __('Purchase', 'erp-pro') },
                { id: 'pay_purchase', name: __('Payment', 'erp-pro') },
                { id: 'receive_pay_purchase', name: __('Receive', 'erp-pro') }
            ],
            showSearchVoucher: false,
            invoiceBtnText: __( 'Create Return Invoice', 'erp-pro' ),
        };
    },
    computed: mapState({
        vendors: state => state.purchase.vendors
    }),
    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.$root.$on('transactions-filter', filters => {
            /*  this.$router.push({
                path : '/transactions/purchases',
                query: { start: filters.start_date, end: filters.end_date, status: filters.status }
            });
            */
            this.fetchItems(filters);
            this.fetched = true;
        });

        const filters = {};

        // Get start & end date from url on page load
        if (this.$route.query.start && this.$route.query.end) {
            filters.start_date = this.$route.query.start;
            filters.end_date   = this.$route.query.end;
        }

        if (this.$route.query.status) {
            filters.status = this.$route.query.status;
        }

        if (!this.fetched) {
            this.fetchItems(filters);
        }

        if(!this.vendors.length){
            HTTP.get('/people', {
                params: {
                    type: 'vendor',
                    per_page: -1,
                    page: 1 // *offset issue
                }
            }).then(response => {
                this.$store.dispatch('purchase/fillVendors', response.data);
            });
        }

        this.$store.dispatch('spinner/setSpinner', false);
    },

    methods: {

        async fetchItems(filters = {}) {
            this.rows = [];
            let data =  {
                per_page  : this.paginationData.perPage,
                page      : this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                start_date: filters.start_date,
                end_date  : filters.end_date,
                status    : filters.status,
                type      : filters.type,
                vendor_id : filters.people_id
            } ;

            this.listLoading = true;

            HTTP.get( '/purchase-return/list', data ).then(response => {
                const mappedData = response.data.map(item => {
                    item['actions'] = [ { key: '#', label: __('No actions found', 'erp') } ];
                    return item;
                });

                this.rows = mappedData;

                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.listLoading = false;
            });
        },

        async searchVoucher() {
            if ( this.voucher_no.trim().length === 0 ) {
                return Swal.fire( __( 'Enter a voucher number', 'erp-pro' ), '', 'error' ) ;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get( '/purchase-return/search-invoice/'+ this.voucher_no).then(response => {
                this.$store.dispatch('spinner/setSpinner', false);

                this.invoice               = response.data;
                this.invoice.amount        = parseFloat(this.invoice.amount);
                this.invoice.discount      = parseFloat(this.invoice.discount);
                this.invoice.tax           = parseFloat(this.invoice.tax);
                this.invoice.return_reason = '';
                this.invoice.discount_type = null;
                this.invoice.line_items.map(item => {
                    item.return_qty     = parseFloat( item.return_qty );
                    item.returnable_qty = parseFloat( item.qty ) - parseFloat( item.return_qty );
                    item.price          = parseFloat( item.price );
                    item.tax            = ( parseFloat( item.tax ) || 0) / parseFloat( item.qty );
                    item.existing_qty   = item.qty;
                    item.qty            = parseFloat( item.qty ) - ( parseFloat(item.return_qty) || 0 );
                    item.discount       = 0;
                });

                if ( ! this.invoice.id ) {
                    return Swal.fire( __( 'No voucher found!', 'erp-pro' ), '', 'error' )  ;
                }

                this.$router.push({
                    path  : `/transactions/purchases/return/${this.voucher_no}/create`,
                    params: {
                        invoice: this.invoice
                    }
                });
            });
        },

        bindInputEvent(e) {
            if(e.keyCode === 13 || e.which === 13) {
                this.searchVoucher();
            }
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if ( confirm( __('Are you sure to delete?', 'erp-pro') ) ) {
                    HTTP.delete('purchases/' + row.id).then(response => {
                        this.$delete(this.rows, index);
                    });
                }
                break;

            case 'edit':
                if (row.type === 'purchase') {
                    this.$router.push({ name: 'PurchaseEdit', params: { id: row.id } });
                }

                break;

            case 'payment':
                if (row.type === 'purchase') {
                    this.$router.push({
                        name  : 'PayPurchaseCreate',
                        params: {
                            vendor_id  : row.vendor_id,
                            vendor_name: row.vendor_name
                        }
                    });
                }
                break;

            case 'void':
                if ( confirm( __('Are you sure to void the transaction?', 'erp-pro') ) ) {
                    if (row.type === 'purchase') {
                        HTTP.post('purchases/' + row.id + '/void').then(response => {
                            this.showAlert('success', __('Transaction has been void!', 'erp-pro') );
                        }).catch(error => {
                            throw error;
                        });
                    }

                    if (row.type === 'pay_purchase' || row.type === 'receive_pay_purchase') {
                        HTTP.post('pay-purchases/' + row.id + '/void').then(response => {
                            this.showAlert('success', __('Transaction has been void!', 'erp-pro') );
                        }).then(() => {
                            this.$router.push({ name: 'Purchases' });
                        }).catch(error => {
                            throw error;
                        });
                    }
                }
                break;

            case 'to_purchase':
                this.$router.push({ name: 'PurchaseEdit', params: { id: row.id }, query: { convert: true } });
                break;

            default :
                break;
            }
        },

        goToPage(page) {
            this.listLoading                = true;
            const queries                   = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name  : 'PaginatePurchases',
                params: { page: page },
                query : queries
            });

            this.fetchItems();
        },

        isPayment(row) {
            return row.type === 'pay_purchase' || row.type === 'receive_pay_purchase';
        },

        getTrnType(row) {
            if (row.type === 'purchase') {
                if (row.purchase_order === '1') {
                    return __('Purchase Order', 'erp-pro');
                }

                return __('Purchase', 'erp-pro');
            } else if (row.type === 'pay_purchase') {
                return __('Payment', 'erp-pro');
            } else {
                return __('Receive', 'erp-pro');
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

    .voucher-input {
        margin-left: 5px;
    }
</style>
