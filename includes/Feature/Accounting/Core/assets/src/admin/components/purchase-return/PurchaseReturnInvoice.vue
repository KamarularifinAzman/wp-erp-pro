<template>
    <div class="wperp-container invoice-create">
        <div class="wperp-modal-dialog" >
            <div class="wperp-modal-content">
                <!-- Start .header-section -->
                <div class="content-header-section separator">
                    <div class="wperp-row wperp-between-xs">
                        <div class="wperp-col">
                            <h2 class="content-header__title">{{ __('Purchase Return', 'erp-pro') }}</h2>

                            <router-link :to="{ name: 'PurchaseReturnList' }">
                                <button class="wperp-btn btn--primary add-line-trigger pull-left">
                                    {{ __('Back', 'erp-pro') }}
                                </button>
                            </router-link>
                        </div>
                    </div>
                </div>
                <div class="wperp-modal-body">
                    <div class="wperp-invoice-panel" v-if="invoice.id">
                        <div class="invoice-body">
                            <h4> {{ __('Purchase Return Invoice', 'erp-pro') }}</h4>
                            <div class="wperp-row">
                                <div class="wperp-col-sm-6">
                                    <h5>{{ __('Bill to', 'erp-pro') }}:</h5>
                                    <div class="persons-info">
                                        <strong>{{ invoice.vendor_name }}</strong><br>
                                        {{ invoice.billing_address }}
                                    </div>
                                </div>
                                <div class="wperp-col-sm-6">

                                    <table class="invoice-info">
                                        <tr>
                                            <th>{{ __('Return Date', 'erp-pro') }}<span class="wperp-required-sign"> *</span></th>
                                            <td>
                                                <datepicker v-model="invoice.return_date"></datepicker>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Voucher No', 'erp-pro') }}:</th>
                                            <td>#{{ invoice.voucher_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Transaction Date', 'erp-pro') }}:</th>
                                            <td>{{ invoice.trn_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Due Date', 'erp-pro') }}:</th>
                                            <td>{{ invoice.due_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Created At', 'erp-pro') }}:</th>
                                            <td>{{ invoice.created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Amount Due', 'erp-pro') }}:</th>
                                            <!--<td>{{ moneyFormat( invoice.total_due ) }}</td>-->
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="wperp-invoice-table">
                            <table class="wperp-table wperp-form-table invoice-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Select', 'erp-pro') }}</th>
                                    <th>{{ __('Sl', 'erp-pro') }}.</th>
                                    <th>{{ __('Product', 'erp-pro') }}</th>
                                    <th>{{ __('Qty', 'erp-pro') }}</th>
                                    <th>{{ __('Unit Discount', 'erp-pro') }}</th>
                                    <th>{{ __('Unit Price', 'erp-pro') }}</th>
                                    <th>{{ __('Amount', 'erp-pro') }}</th>
                                    <th>{{ __('Action', 'erp-pro') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :key="index" v-for="(detail, index) in invoice.line_items" class="inline-edit-row">
                                    <th>
                                       <label :for="'select-item-'+index">
                                           <input v-if="detail.qty > 0"  type="checkbox" class=" custom-checkbox" v-model="detail.selected" :id="'select-item-'+index" value="edit.php?post_type=page">
                                       </label>
                                    </th>
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ detail.name }} <span v-if="detail.return_qty">({{__( 'Returned', 'erp-pro' )}} {{detail.return_qty}}/{{detail.existing_qty}})</span></td>
                                    <td> <input v-if="detail.selected" @keyup="quantityUpdate(detail, index)" type="number" v-model="detail.qty" /> <span v-else> {{ detail.qty }}</span> </td>
                                    <td>{{ moneyFormat( detail.discount ) }}</td>
                                    <td>{{ moneyFormat(detail.price) }}</td>
                                    <td>{{ moneyFormat( (detail.price * parseFloat(detail.qty) ) - ( detail.discount * parseFloat(detail.qty) ) ) }}</td>
                                    <td   class="col--actions delete-row">
                                        <span  @click="deleteItem(index)" class="wperp-btn"> <i  class="flaticon-trash"></i></span>
                                    </td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="inline-edit-row">
                                    <td class="wperp-invoice-amounts" colspan="7">
                                        <ul>
                                            <li><span>{{ __('Subtotal', 'erp-pro') }}:</span> {{ moneyFormat(summery.line_total)
                                                }}
                                            </li>
                                            <li><span>{{ __('Discount', 'erp-pro') }}:</span> (-) {{
                                                moneyFormat(summery.discount)
                                                }}
                                            </li>
                                            <li><span>{{ __('VAT', 'erp-pro') }}:</span> (+) {{ moneyFormat(summery.tax) }}
                                            </li>
                                             <li><span>{{ __('Total', 'erp-pro') }}:</span> {{ moneyFormat( summery.total ) }}</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr class="wperp-form-group inline-edit-row">
                                    <td colspan="9" style="text-align: left;">
                                        <label>{{ __( 'Return Reason', 'erp-pro' ) }}</label>
                                        <textarea rows="4" maxlength="250" v-model="invoice.return_reason" placeholder="Particulars" class="wperp-form-field display-flex"></textarea>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="wperp-form-group  text-right">
                            <button type="submit" class="wperp-btn btn--primary voucher-search" @click="submitReturn" >{{ __( 'Save', 'erp-pro') }}</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import {mapState} from 'vuex';
import HTTP       from './../../request';

const Swal       = window.acct.libs['Swal'];
const Datepicker = window.acct.libs['Datepicker'];

export default {
    name: 'PurchaseReturnInvoice',

    components: {
        Datepicker
    },

    data() {
        return {
            invoice : {},
        }
    },

    watch: {

    },

    computed: {
        ...mapState({ invoiceTotalAmount: state => state.sales.invoiceTotalAmount }),
        ...mapState({ actionType: state => state.combo.btnID }),

        summery(){
            let s = {
                line_total: 0,
                tax       : 0,
                discount  : 0,
                total     : 0
            };

            this.invoice.line_items.forEach(item=>{
                if (item.selected){
                    s.line_total += item.price  * parseFloat(item.qty);
                    s.tax        += ( item.tax || 0 ) * parseFloat(item.qty);
                    s.discount   += 0;
                }
            })
            s.total = ( s.line_total  + s.tax ) - s.discount
          return s ;
        },
    },

    created() {
        if ( this.$route.params.invoice ) {
            this.invoice = this.$route.params.invoice;
        } else {
            this.getPurchaseInfo();
        }
    },

    methods: {
        quantityUpdate(item, index){
            if ( parseFloat(item.qty) > item.existing_qty || parseFloat(item.qty) <= 0 ){
                item.qty = item.existing_qty ;
            }
        },

        getPurchaseInfo(){
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get( `/purchase-return/search-invoice/${this.$route.params.id}`).then(response => {
                this.$store.dispatch('spinner/setSpinner', false);

                this.invoice               = response.data;
                this.invoice.amount        = parseFloat(this.invoice.amount);
                this.invoice.discount      = parseFloat(this.invoice.discount);
                this.invoice.tax           = parseFloat(this.invoice.tax);
                this.invoice.return_reason = '';
                this.invoice.discount_type = null;
                this.invoice.line_items.map( item => {
                    item.return_qty     = parseFloat( item.return_qty )
                    item.returnable_qty = parseFloat(item.qty) - parseFloat( item.return_qty )
                    item.price          = parseFloat( item.price )
                    item.tax            = ( parseFloat( item.tax ) || 0) / parseFloat(item.qty)
                    item.existing_qty   = item.qty
                    item.qty            = parseFloat( item.qty ) - ( parseFloat(item.return_qty) || 0 )
                    item.discount       = 0
                } );

                if ( ! this.invoice.id ) {
                    return Swal.fire( __( 'No voucher found!', 'erp-pro' ), '', 'error' )  ;
                }
            });
        },

        submitReturn(){

            if ( ! this.summery.total || this.summery.total < 1 ) {
                Swal.fire( __( 'Please Select minimum one item', 'erp-pro' ), '', 'info' )  ;
                return false;
            }

            if ( ! this.invoice.return_date ) {
                return Swal.fire( __( 'Please choose a Return date', 'erp-pro' ), '', 'info' )  ;
            }

            Swal.fire({
                title             : __( 'Are you sure?', 'erp-pro' ),
                text              : __( "You won't be able to revert this!", 'erp-pro' ),
                type              : 'warning',
                showCancelButton  : true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor : '#d33',
                confirmButtonText : __( 'Yes, Confirm!', 'erp-pro' ),
                reverseButtons    : true
            }).then( async (result) => {
                if (result.value) {
                    this.invoice.line_items.forEach( (item, index) => {
                        if ( ! item.selected ) {
                            this.invoice.line_items.splice(index, 1)
                        }
                    });
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.post('/purchase-return/create', this.invoice).then(res => {
                        this.invoice = { line_items: [] }
                        Swal({
                            position         : 'center',
                            type             : 'success',
                            title            : __( 'Invoice Saved successfully', 'erp-pro' ),
                            showConfirmButton: false,
                            timer            : 2500
                        });
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.$router.push({ name: 'PurchaseReturnList' });
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    }) ;
                } else if (result.isDenied) {
                    console.log( result );
                }

            })

        },

        deleteItem(index){
            this.invoice.line_items.splice(index, 1)
        }
    }

};
</script>

<style lang="less">
tr.padded {
    height: 50px;
}

.discount-rate-row {
    select {
        width: 235px;
        height: 34px;
    }

    input {
        width: 130px !important;
    }
}

.tax-rate-row {
    .tax-rates {
        width: 235px;
        float: right;
    }
}

.attachment-item {
    box-shadow: 0 0 0 1px rgba(76, 175, 80, 0.3);
    padding: 3px;
    position: relative;
    height: 58px;
    margin: 10px 0;

    .remove-file {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 13px;
        color: #fff;
        cursor: pointer;
        background: #f44336;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        text-align: center;
    }

    img {
        float: left;
    }
}

.attachment-meta {
    h3 {
        margin-left: 50px;
        text-align: left;
        line-height: 2;
    }
}

.invoice-create {
    .dropdown {
        width: 100%;
    }

    .col--products {
        width: 400px;
    }

    .col--qty {
        width: 80px;
    }

    .col--unit-price {
        width: 120px;
    }

    .col--amount {
        width: 200px;
    }

    .col--tax {
        text-align: center;
        width: 100px;
    }

    .product-select {
        .with-multiselect .multiselect__select,
        .with-multiselect .multiselect__tags {
            min-height: 33px !important;
            margin-top: 4px;
        }

        .with-multiselect .multiselect__placeholder {
            margin-top: 3px;
        }
    }

    .invoice-create .erp-help-tip {
        color: #2f4f4f;
        font-size: 1.2em;
    }
 }

.voucher-search {
    margin-top: 30px;
    line-height: 1.95;
}

</style>
