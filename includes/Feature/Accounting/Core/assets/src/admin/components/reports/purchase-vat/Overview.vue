<template>
    <div class="purchase-vat-report">
        <h2 class="title-container">
            <span>{{ __( 'Purchase VAT Reports', 'erp-pro' ) }}</span>
            
            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'ReportsOverview' }">
                {{ __( 'Back', 'erp-pro' ) }}
            </router-link>
        </h2>
        
        <ul class="reports-overview">
            <li>
                <h3>{{ __( 'Agency Based', 'erp-pro' ) }}</h3>
                <p>{{ __( 'It generates vat on purchase report based on agencies', 'erp-pro' ) }}.</p>

                <router-link
                    class="wperp-btn btn--primary"
                    :to="{ name: 'PurchaseVatReportAgencyBased' }">
                    {{ __( 'View Report', 'erp-pro' ) }}
                </router-link>
            </li>

            <li>
                <h3>{{ __( 'Transaction Based', 'erp-pro' ) }}</h3>
                <p>{{ __( 'It generates vat on purchase report based on transactions', 'erp-pro' ) }}.</p>

                <router-link
                    class="wperp-btn btn--primary"
                    :to="{ name: 'PurchaseVatReportTransactionBased' }">
                    {{ __( 'View Report', 'erp-pro' ) }}
                </router-link>
            </li>

            <li>
                <h3>{{ __( 'Vendor Based', 'erp-pro' ) }}</h3>
                <p>{{ __( 'It generates vat on purchase report based on vendors', 'erp-pro' ) }}.</p>

                <router-link
                    class="wperp-btn btn--primary"
                    :to="{ name: 'PurchaseVatReportVendorBased' }">
                    {{ __( 'View Report', 'erp-pro' ) }}
                </router-link>
            </li>

            <li>
                <h3>{{ __( 'Category Based', 'erp-pro' ) }}</h3>
                <p>{{ __( 'It generates vat on purchase report based on categories', 'erp-pro' ) }}.</p>

                <router-link
                    class="wperp-btn btn--primary"
                    :to="{ name: 'PurchaseVatReportCategoryBased' }">
                    {{ __( 'View Report', 'erp-pro' ) }}
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: 'PurchaseVatReportOverview',
        
        data() {
            return {
                report: []
            };
        }
    };
</script>

<style lang="less">
    .reports-overview {
        margin: 0;
        padding: 10px;
        display: flex;
        flex-wrap: wrap;
        
        li {
            font-size: 20px;
            background: #fff;
            margin-bottom: 1px;
            padding: 15px;
            width: 48%;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            margin: 10px;
            border-radius: 3px;
            
            h3 {
                border-bottom: 1px solid rgba(0, 0, 0, .08);
                padding-bottom: 10px;
                font-weight: normal;
                color: #263238;
            }
            
            p {
                font-size: 15px;
                color: #525252;
            }
        }
    }

    .purchase-vat-report {
        h2 {
            padding-top: 15px;
        }

        .query-options {
            padding: 20px 0;
        }

        .with-multiselect {
            width: 220px;
            float: left;
            margin-right: 30px;
        }

        .add-line-trigger {
            line-height: 1.9 !important;
        }

        .report-header {
            width: 420px;
            padding: 10px 0 0 0;

            li {
                display: flex;
                justify-content: space-between;
            }
        }

        .purchase-vat-table tbody tr td:last-child {
            text-align: left !important;
        }
    }

    @media print {
        .erp-nav-container {
            display: none;
        }

        .no-print, .no-print * {
            display: none !important;
        }

        .purchase-vat-report {
            .wperp-table.purchase-vat-table {
                th.trn_date,
                th.created_at {
                    min-width: 120px;
                }

                th.trn_no {
                    min-width: 100px;
                }

                td,
                th {
                    padding: 3px !important;
                }

                tr th:first-child,
                tr td:last-child {
                    padding-left: 5px;
                }

                tr th:last-child,
                tr td:last-child {
                    padding-right: 5px;
                }

                thead tr th {
                    font-weight: bold;

                    &:nth-child(5),
                    &:nth-child(6),
                    &:nth-child(7) {
                        text-align: right;
                    }
                }

                tbody tr td {
                    &:nth-child(5),
                    &:nth-child(6),
                    &:nth-child(7) {
                        text-align: right !important;
                    }
                }

                tfoot td {
                    &:nth-child(3),
                    &:nth-child(4) {
                        text-align: right !important;
                    }
                }
            }
        }
    }
</style>