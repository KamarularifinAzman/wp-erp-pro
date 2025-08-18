<div class="erp-attendance-status-widget" v-cloak>
    <div v-if="doingAjax" class="erp-attendance-status-chart">
        <p class="erp-att-status-chart-no-data">{{ i18n.loadingData }}...</p>
    </div>

    <div v-if="!attendance_data.length && !doingAjax" class="erp-attendance-status-chart">
        <p class="erp-att-status-chart-no-data">{{ i18n.notEnoughData }}</p>
    </div>

    <div v-if="attendance_data.length && !doingAjax" class="erp-attendance-status-chart" :id="'erp-attendance-status-chart-' + _uid"></div>

    <div class="filter-selector">
        <span>{{ i18n.filterBy }}: </span>

        <select class="filter-status" v-model="filter">
            <option value="today">{{ i18n.today }}</option>
            <option value="yesterday">{{ i18n.yesterday }}</option>
            <option value="this_month">{{ i18n.thisMonth }}</option>
            <option value="last_month">{{ i18n.lastMonth }}</option>
            <option value="this_quarter">{{ i18n.thisQuarter }}</option>
            <option value="this_year">{{ i18n.thisYear }}</option>
        </select>
    </div>
</div>