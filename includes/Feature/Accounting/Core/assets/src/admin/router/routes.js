// Components for purchase return
import PurchaseReturnInvoice from './../components/purchase-return/PurchaseReturnInvoice.vue';
import PurchaseReturnDetails from './../components/purchase-return/PurchaseReturnSingle.vue';
import PurchaseReturnList from './../components/purchase-return/PurchaseReturnList.vue';

// Components for sales return
import SalesReturnInvoice from './../components/sales-return/SalesReturnInvoice.vue';
import SalesReturnDetails from './../components/sales-return/SalesReturnSingle.vue';
import SalesReturnList from './../components/sales-return/SalesReturnList.vue';

// Components for purchase return reports
import PurchaseReturnReport from '../components/reports/purchase-return/Report.vue';

// Components for sales return reports
import SalesReturnReport from '../components/reports/sales-return/Report.vue';

// Components for VAT on purchase reports
import PurchaseVatReportOverview from '../components/reports/purchase-vat/Overview.vue';
import PurchaseVatReportAgencyBased from '../components/reports/purchase-vat/AgencyBased.vue';
import PurchaseVatReportCategoryBased from '../components/reports/purchase-vat/CategoryBased.vue';
import PurchaseVatReportTransactionBased from '../components/reports/purchase-vat/TransactionBased.vue';
import PurchaseVatReportVendorBased from '../components/reports/purchase-vat/VendorBased.vue';

const routes = [
    {
        path      : '/transactions',
        component : {
            render(c) {
                return c('router-view');
            }
        },
        children  : [
            {
                path      : 'purchases/return',
                component : {
                    render(c) {
                        return c('router-view');
                    }
                },
                children  : [
                    {
                        path      : '',
                        name      : 'PurchaseReturnList',
                        component : PurchaseReturnList
                    },
                    {
                        path      : ':id',
                        name      : 'PurchaseReturnDetails',
                        component : PurchaseReturnDetails
                    },
                    {
                        path      : ':id/create',
                        name      : 'PurchaseReturnInvoice',
                        component : PurchaseReturnInvoice
                    },
                ]
            },
            {
                path      : 'sales/return',
                component : {
                    render(c) {
                        return c('router-view');
                    }
                },
                children  : [
                    {
                        path      : '',
                        name      : 'SalesReturnList',
                        component : SalesReturnList
                    },
                    {
                        path      : ':id',
                        name      : 'SalesReturnDetails',
                        component : SalesReturnDetails
                    },
                    {
                        path      : ':id/create',
                        name      : 'SalesReturnInvoice',
                        component : SalesReturnInvoice
                    },
                ]
            },
            {
                path      : '/reports/purchase/returns',
                name      : 'PurchaseReturnReport',
                component : PurchaseReturnReport,
                meta      : {
                    title : __( 'Purchase Return Report', 'erp-pro' )
                }
            },
            {
                path      : '/reports/sales/returns',
                name      : 'SalesReturnReport',
                component : SalesReturnReport,
                meta      : {
                    title : __( 'Sales Return Report', 'erp-pro' )
                }
            },
            {
                path      : '/reports/purchase/vat',
                name      : 'PurchaseVatReportOverview',
                component : PurchaseVatReportOverview,
                meta      : {
                    title : __( 'Purchase VAT Reports', 'erp-pro' )
                }
            },
            {
                path      : '/reports/purchase/vat/agency-based',
                name      : 'PurchaseVatReportAgencyBased',
                component : PurchaseVatReportAgencyBased,
                meta      : {
                    title : __( 'Purchase VAT Report (Agency Based)', 'erp-pro' )
                }
            },
            {
                path      : '/reports/purchase/vat/category-based',
                name      : 'PurchaseVatReportCategoryBased',
                component : PurchaseVatReportCategoryBased,
                meta      : {
                    title : __( 'Purchase VAT Report (Category Based)', 'erp-pro' )
                }
            },
            {
                path      : '/reports/purchase/vat/transaction-based',
                name      : 'PurchaseVatReportTransactionBased',
                component : PurchaseVatReportTransactionBased,
                meta      : {
                    title : __( 'Purchase VAT Report (Transaction Based)', 'erp-pro' )
                }
            },
            {
                path      : '/reports/purchase/vat/vendor-based',
                name      : 'PurchaseVatReportVendorBased',
                component : PurchaseVatReportVendorBased,
                meta      : {
                    title : __( 'Purchase VAT Report (Vendor Based)', 'erp-pro' )
                }
            },
        ]
    }
];

export default routes;
