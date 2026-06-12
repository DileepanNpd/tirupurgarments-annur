<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t('menu.monthly_report')" class="p-0">
                <template #extra>
                    <ExprotTable
                        exportType="monthly_gst_reports"
                        tableName="monthly-gst-report-table"
                        :title="`Monthly GST Report - ${selectedMonthLabel}`"
                    />
                </template>
            </a-page-header>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t("menu.dashboard") }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>{{ $t("menu.reports") }}</a-breadcrumb-item>
                <a-breadcrumb-item>{{ $t("menu.monthly_report") }}</a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="16" :xl="16">
                <a-typography-text type="secondary" style="font-size: 13px;">
                    Select a month to generate GST filing report with CGST / SGST / IGST breakup
                </a-typography-text>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
                <a-row :gutter="[8, 8]" justify="end">
                    <a-col :xs="24" :sm="16" :md="14" :lg="12" :xl="12">
                        <a-date-picker
                            v-model:value="selectedMonth"
                            picker="month"
                            placeholder="Select Month"
                            style="width: 100%"
                            @change="onMonthChange"
                        />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </admin-page-filters>

    <admin-page-table-content>
        <a-spin :spinning="loading">
            <div v-if="results.length === 0 && !loading" style="padding: 40px; text-align: center;">
                <a-empty :description="monthYear ? 'No sales invoices found for this month' : 'Please select a month'" />
            </div>

            <div v-else class="table-responsive">
                <a-table
                    id="monthly-gst-report-table"
                    :columns="columns"
                    :data-source="results"
                    :row-key="(_, idx) => idx"
                    :pagination="false"
                    bordered
                    size="middle"
                    :scroll="{ x: 1400 }"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'taxable_value'">
                            {{ formatAmountCurrency(record.taxable_value) }}
                        </template>
                        <template v-if="column.dataIndex === 'cgst_amount'">
                            {{ record.cgst_amount > 0 ? formatAmountCurrency(record.cgst_amount) : '—' }}
                        </template>
                        <template v-if="column.dataIndex === 'sgst_amount'">
                            {{ record.sgst_amount > 0 ? formatAmountCurrency(record.sgst_amount) : '—' }}
                        </template>
                        <template v-if="column.dataIndex === 'total_tax'">
                            <strong>{{ formatAmountCurrency(record.total_tax) }}</strong>
                        </template>
                        <template v-if="column.dataIndex === 'discount'">
                            {{ record.discount > 0 ? formatAmountCurrency(record.discount) : '—' }}
                        </template>
                        <template v-if="column.dataIndex === 'shipping'">
                            {{ record.shipping > 0 ? formatAmountCurrency(record.shipping) : '—' }}
                        </template>
                        <template v-if="column.dataIndex === 'total'">
                            <strong>{{ formatAmountCurrency(record.total) }}</strong>
                        </template>
                        <template v-if="column.dataIndex === 'payment_status'">
                            <a-tag :color="record.payment_status === 'paid' ? 'green' : record.payment_status === 'partial' ? 'orange' : 'red'">
                                {{ record.payment_status }}
                            </a-tag>
                            <div v-if="record.payment_status === 'partial'" style="font-size:11px; color:#d46b08; margin-top:2px;">
                                Due: {{ formatAmountCurrency(record.due_amount) }}
                            </div>
                        </template>
                    </template>

                    <template #summary>
                        <a-table-summary-row>
                            <a-table-summary-cell :col-span="4">
                                <a-typography-text strong>TOTAL ({{ results.length }} invoices)</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ totals.total_quantity }} pcs</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.taxable_value) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.cgst_amount) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.sgst_amount) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.total_tax) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.discount) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.shipping) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell align="right">
                                <a-typography-text strong>{{ formatAmountCurrency(totals.total) }}</a-typography-text>
                            </a-table-summary-cell>
                            <a-table-summary-cell></a-table-summary-cell>
                            <a-table-summary-cell></a-table-summary-cell>
                        </a-table-summary-row>
                    </template>
                </a-table>
            </div>
        </a-spin>
    </admin-page-table-content>
</template>

<script>
import { ref, onBeforeMount, watch } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import { filter } from "lodash-es";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import ExprotTable from "../../../components/report-exports/ExportTable.vue";

export default {
    components: {
        AdminPageHeader,
        ExprotTable,
    },
    setup() {
        const { permsArray, formatAmountCurrency, selectedWarehouse, willSubscriptionModuleVisible } = common();
        const router = useRouter();
        const store = useStore();

        const selectedMonth = ref(null);
        const monthYear = ref(null);
        const selectedMonthLabel = ref('');
        const loading = ref(false);
        const results = ref([]);
        const totals = ref({
            total_quantity: 0, taxable_value: 0,
            cgst_amount: 0, sgst_amount: 0,
            total_tax: 0, discount: 0, shipping: 0, total: 0,
        });

        const columns = [
            { title: '#', dataIndex: 'index', width: 50, customRender: ({ index }) => index + 1 },
            { title: 'Date', dataIndex: 'order_date', width: 105 },
            { title: 'Invoice No', dataIndex: 'invoice_number', width: 130 },
            { title: 'Customer', dataIndex: 'customer_name', width: 160 },
            { title: 'Qty', dataIndex: 'total_quantity', width: 70, align: 'right' },
            { title: 'Taxable Value', dataIndex: 'taxable_value', width: 130, align: 'right' },
            { title: 'CGST', dataIndex: 'cgst_amount', width: 140, align: 'right' },
            { title: 'SGST', dataIndex: 'sgst_amount', width: 140, align: 'right' },
            { title: 'Total Tax', dataIndex: 'total_tax', width: 120, align: 'right' },
            { title: 'Discount', dataIndex: 'discount', width: 110, align: 'right' },
            { title: 'Shipping', dataIndex: 'shipping', width: 110, align: 'right' },
            { title: 'Invoice Total', dataIndex: 'total', width: 130, align: 'right' },
            { title: 'Payment Mode', dataIndex: 'payment_mode', width: 140 },
            { title: 'Status', dataIndex: 'payment_status', width: 100, align: 'center' },
        ];

        onBeforeMount(() => {
            if (
                !(permsArray.value.includes('sales_view') || permsArray.value.includes('admin')) ||
                !willSubscriptionModuleVisible('reports')
            ) {
                router.push('admin.dashboard.index');
            }
        });

        const getData = () => {
            if (!monthYear.value) return;
            loading.value = true;

            axiosAdmin
                .post('reports/monthly-gst-report', { month_year: monthYear.value })
                .then((response) => {
                    results.value = response.data.results;
                    totals.value = response.data.totals;

                    const exportType = 'monthly_gst_reports';
                    const existing = filter(
                        store.state.auth.allExportData,
                        (d) => d.export_type !== exportType
                    );
                    store.commit('auth/updatAllExportData', [
                        ...existing,
                        {
                            export_type: exportType,
                            data: response.data.results,
                            url: 'reports/monthly-gst-report',
                        },
                    ]);
                })
                .finally(() => {
                    loading.value = false;
                });
        };

        const onMonthChange = (date) => {
            if (date) {
                monthYear.value = date.format('YYYY-MM');
                selectedMonthLabel.value = date.format('MMMM YYYY');
            } else {
                monthYear.value = null;
                selectedMonthLabel.value = '';
                results.value = [];
                totals.value = { total_quantity: 0, taxable_value: 0, cgst_amount: 0, sgst_amount: 0, total_tax: 0, discount: 0, shipping: 0, total: 0 };
            }
        };

        watch(monthYear, getData);
        watch(selectedWarehouse, getData);

        return {
            selectedMonth, monthYear, selectedMonthLabel,
            loading, results, totals, columns,
            formatAmountCurrency, onMonthChange,
        };
    },
};
</script>
