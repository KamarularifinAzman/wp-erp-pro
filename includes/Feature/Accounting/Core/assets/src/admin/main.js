import routes from './router/routes';
import store from './store/index';
import PurchaseVatReportFront from './components/reports/purchase-vat/ReportFront.vue';
import PurchaseReturnReportFront from './components/reports/purchase-return/ReportFront.vue';
import SalesReturnReportFront from './components/reports/sales-return/ReportFront.vue';


if ( typeof window.erp_acct_vue_instance !== 'undefined' ) {
    window.erp_acct_vue_instance.$router.addRoutes( routes );

    acct.addFilter( 'acctExtensionReportsList', 'AdminReports', PurchaseVatReportFront );
    acct.addFilter( 'acctExtensionReportsList', 'AdminReports', PurchaseReturnReportFront );
    acct.addFilter( 'acctExtensionReportsList', 'AdminReports', SalesReturnReportFront );

    const lodash = require( 'lodash' );
    lodash.merge(
        window.erp_acct_vue_instance.$store.state,
        store.state
    );
}

//window.ERPProActivated  = true ;

window.erpAccountingHooks.addAction( 'ERPAccountingCoreActive', 'ERPAcct', () => {
    // window.erp_acct_vue_instance.$store.common.commit('setProStatus', {
     //    status: true
  // }) ;

}, 1 );
