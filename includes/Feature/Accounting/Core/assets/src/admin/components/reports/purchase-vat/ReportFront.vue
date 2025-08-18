<template>
    <li>
        <h3>{{ __( 'Purchase VAT', 'erp-pro' ) }}</h3>
        <p>{{ __( 'It generates report based on the VAT on purchases charged or paid for the current financial cycle/year', 'erp-pro' ) }}.</p>

        <router-link
            class="wperp-btn btn--primary"
            :to="{ name: 'PurchaseVatReportOverview' }">
            {{ __( 'View Report', 'erp-pro' ) }}
        </router-link>
    </li>
</template>

<script>
export default {
    name: "PurchaseVatReportFront"
}
</script>

<style lang="less" scoped>
    ul {
        margin: 0;
        padding: 10px;
        display: flex;
        flex-wrap: wrap;
    }

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
</style>
