<template>
    <a-modal
        :open="visible"
        :centered="true"
        :maskClosable="false"
        :title="$t('common.print_invoice')"
        width="400px"
        @cancel="onClose"
    >
        <div id="pos-invoice">
            <div style="max-width: 400px; margin: 0px auto" v-if="order && order.xid">
                <div class="company-details">
                    <h2>Tirupur Garments - Annur</h2>
                    <div v-if="order?.invoice_number?.startsWith('SALE-')">
                        <h2>GST: {{ selectedWarehouse.address }}</h2>
                    </div>
                    <!--<h2>{{ selectedWarehouse.name }}</h2>-->
                    <h4 style="margin-bottom: 0px">
                        {{ $t("common.phone") }}: {{ selectedWarehouse.phone }}
                    </h4>
                    <h4>{{ $t("common.email") }}: {{ selectedWarehouse.email }}</h4>
                </div>
                <div class="tax-invoice-details">
                    <h3 class="tax-invoice-title">
                        <div v-if="order?.invoice_number?.startsWith('SALE-')">
                            {{ $t("sales.tax_invoice") }}
                        </div>
                    </h3>
                    <table class="invoice-customer-details">
                        <tr>
                            <td style="width: 50%">
                                No :
                                {{ order.invoice_number }}
                            </td>
                            <td style="width: 50%">
                                {{ $t("common.date") }} :
                                {{ formatDate(order.order_date) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%">
                                {{ $t("stock.customer") }} : {{ order.user.name }}
                            </td>
                            <td style="width: 50%">
                                {{ $t("stock.sold_by") }} : {{ order.staff_member.name }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="tax-invoice-items">
                    <table style="width: 100%">
                        <thead style="background: #eee">
                            <!--<td style="width: 5%">#</td>-->
                            <td style="width: 25%">{{ $t("common.item") }}</td>
                            <td
                                :style="{
                                    width: selectedWarehouse.show_mrp_on_invoice
                                        ? '10%'
                                        : '25%',
                                }"
                            >
                                {{ $t("common.qty") }}
                            </td>
                            <td
                                v-if="selectedWarehouse.show_mrp_on_invoice"
                                :style="{
                                    width: selectedWarehouse.show_mrp_on_invoice
                                        ? '20%'
                                        : '20%',
                                }"
                            >
                                {{ $t("product.mrp") }}
                            </td>
                            <td
                                :style="{
                                    width: selectedWarehouse.show_mrp_on_invoice
                                        ? '20%'
                                        : '25%',
                                }"
                            >
                                {{ $t("common.rate") }}
                            </td>
                            <td
                                :style="{
                                    width: selectedWarehouse.show_mrp_on_invoice
                                        ? '20%'
                                        : '25%',
                                    textAlign: 'right',
                                }"
                            >
                                {{ $t("common.total") }}
                            </td>
                        </thead>
                        <tbody>
                            <tr
                                class="item-row"
                                v-for="(item, index) in order.items"
                                :key="item.xid"
                            >
                                <!--<td>{{ index + 1 }}</td>-->
                                <td style="overflow-wrap: anywhere; width: 25%; min-width:125px !important;">{{ item.product.name }}</td>
                                <td>{{ item.quantity + "" + item.unit.short_name }}</td>
                                <td v-if="selectedWarehouse.show_mrp_on_invoice">
                                    {{ item.mrp ? formatAmountCurrency(item.mrp) : "-" }}
                                </td>
                                <td>{{ formatAmountCurrency(item.unit_price) }}</td>
                                <td style="text-align: right">
                                    {{ formatAmountCurrency(item.subtotal) }}
                                </td>
                            </tr>
                            <tr class="item-row-other">
                                <td style="text-align: right; padding-right:15px;" colspan="2">MRP: {{ formatAmountCurrency(order.total_mrp) }}</td>
                                <td style="text-align: right; padding-right:15px;" colspan="3">Sale Price: {{ formatAmountCurrency(order.subtotal) }}</td>
                            </tr>
                            <tr class="item-row-other">
                                <td
                                    :colspan="
                                        selectedWarehouse.show_mrp_on_invoice ? 4 : 3
                                    "
                                    style="text-align: right; padding-right:5px;"
                                >
                                    {{ $t("stock.discount") }}
                                </td>
                                <td colspan="2" style="text-align: right; padding-right:5px;">
                                    {{ formatAmountCurrency(order.discount) }}
                                </td>
                            </tr>
                            <tr class="item-row-other">
                                <td
                                    :colspan="
                                        selectedWarehouse.show_mrp_on_invoice ? 4 : 3
                                    "
                                    style="text-align: right"
                                >
                                    {{ $t("stock.shipping") }}
                                </td>
                                <td colspan="2" style="text-align: right; padding-right:5px;">
                                    {{ formatAmountCurrency(order.shipping) }}
                                </td>
                            </tr>
                            <tr class="item-row-other">
                                <td
                                    :colspan="
                                        selectedWarehouse.show_mrp_on_invoice ? 4 : 3
                                    "
                                    style="text-align: right; padding-right:5px; "
                                >
                                    CGST 2.5%
                                </td>
                                <td colspan="2" style="text-align: right; padding-right:5px;">
                                    {{ formatAmountCurrency(halfTaxAmount) }}
                                </td>
                            </tr>
                            <tr class="item-row-other">
                                <td
                                    :colspan="
                                        selectedWarehouse.show_mrp_on_invoice ? 4 : 3
                                    "
                                    style="text-align: right; padding-right:5px; "
                                >
                                    SGST 2.5%
                                </td>
                                <td colspan="2" style="text-align: right; padding-right:5px;">
                                    {{ formatAmountCurrency(halfTaxAmount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="tax-invoice-totals">
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 30%">
                                <h3 style="margin-bottom: 0px">
                                    {{ $t("common.items") }}: {{ order.total_items }}
                                </h3>
                            </td>
                            <td style="width: 30%">
                                <h3 style="margin-bottom: 0px">
                                    {{ $t("common.qty") }}: {{ order.total_quantity }}
                                </h3>
                            </td>
                            <td style="width: 40%; text-align: center">
                                <h3 style="margin-bottom: 0px">
                                    {{ $t("common.total") }}:
                                    {{ formatAmountCurrency(order.total) }}
                                </h3>
                            </td>
                        </tr>
                    </table>
                </div>
                <div v-if="order?.invoice_number?.startsWith('SALE-')" class="paid-amount-deatils">
                    <table style="width: 100%">
                        <thead style="background: #eee">
                            <td style="width: 50%">{{ $t("payments.paid_amount") }}</td>
                            <td style="width: 50%">{{ $t("payments.due_amount") }}</td>
                        </thead>
                        <tbody>
                            <tr class="paid-amount-row">
                                <td>{{ formatAmountCurrency(order.paid_amount) }}</td>
                                <td>{{ formatAmountCurrency(order.due_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="order?.invoice_number?.startsWith('SALE-')">
                    <table style="width: 100%">
                        <tr style="text-align: center">
                            <td style="width: 100%">
                                <h4
                                    style="margin-bottom: 0px"
                                    v-if="order.order_payments"
                                >
                                    {{ $t("invoice.payment_mode") }}:
                                    <span
                                        v-for="currentOrderPayments in order.order_payments"
                                        :key="currentOrderPayments.xid"
                                        style="margin-right: 5px"
                                    >
                                        {{
                                            formatAmountCurrency(
                                                currentOrderPayments.amount
                                            )
                                        }}
                                        (<span
                                            v-if="
                                                currentOrderPayments.payment &&
                                                currentOrderPayments.payment
                                                    .payment_mode &&
                                                currentOrderPayments.payment.payment_mode
                                                    .name
                                            "
                                        >
                                            {{
                                                currentOrderPayments.payment.payment_mode
                                                    .name
                                            }}
                                        </span>
                                        )
                                    </span>
                                </h4>
                                <h3 style="margin-bottom: 0px" v-else>
                                    {{ $t("invoice.payment_mode") }}: -
                                </h3>
                            </td>
                        </tr>
                    </table>
                </div>
                <div
                    v-if="selectedWarehouse.show_discount_tax_on_invoice"
                    class="discount-details"
                >
                    <p>
                        {{ $t("invoice.total_discount_on_mrp") }} :
                        {{ formatAmountCurrency(order.saving_on_mrp) }}
                    </p>
                    <p>
                        {{ $t("invoice.total_discount") }} :
                        {{ order.saving_percentage }}%
                    </p>
                    <p>
                        {{ $t("invoice.total_tax") }} :
                        {{ formatAmountCurrency(order.total_tax_on_items) }}
                    </p>
                </div>

                <!-- UPI Payment QR -->
                <div style="text-align: center; margin-top: 12px; padding: 8px 0; border-top: 1px dashed #ccc;">
                    <p style="font-size: 12px; margin-bottom: 4px; font-weight: 600;">Scan &amp; Pay via UPI</p>
                    <canvas ref="upiCanvas" class="upi-qr-canvas" style="display:block; margin: 0 auto;"></canvas>
                    <p style="font-size: 11px; margin-top: 4px; color: #555;">q069778669@ybl</p>
                    <p style="font-size: 10px; color: #888;">GPay &middot; PhonePe &middot; Paytm &middot; BHIM</p>
                </div>

            </div>
        </div>

        <template #footer>
            <div class="footer-button">
                <a-button type="primary" @click="printInvoice">
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    {{ $t("common.print_invoice") }}
                </a-button>
            </div>
        </template>
    </a-modal>
</template>

<script>
import { ref, defineComponent, computed, watch, nextTick } from "vue";
import { PrinterOutlined } from "@ant-design/icons-vue";
import QRCode from "qrcode";
import common from "../../../../common/composable/common";
import BarcodeGenerator from "../../../../common/components/barcode/BarcodeGenerator.vue";
import QRcodeGenerator from "../../../../common/components/barcode/QRcodeGenerator.vue";
const posInvoiceCssUrl = window.config.pos_invoice_css;

export default defineComponent({
    props: ["visible", "order"],
    emits: ["closed", "success"],
    components: {
        PrinterOutlined,
        BarcodeGenerator,
        QRcodeGenerator,
    },
    setup(props, { emit }) {
        const { formatAmountCurrency, formatDate, selectedWarehouse } = common();

        const upiCanvas = ref(null);

        const onClose = () => {
            emit("closed");
        };

        // Build the UPI intent string — payee + amount only for the simplest, sparsest QR
        const upiData = computed(() => {
            const amount = props.order?.total
                ? parseFloat(props.order.total).toFixed(2)
                : "0.00";
            return `upi://pay?pa=q069778669@ybl&am=${amount}`;
        });

        // Render the QR locally onto the canvas with crisp, integer-scaled modules.
        // Low error correction + a wide quiet zone = chunky, well-separated lines
        // that print cleanly on thermal paper.
        const renderQR = async () => {
            await nextTick();
            if (!upiCanvas.value) return;
            try {
                await QRCode.toCanvas(upiCanvas.value, upiData.value, {
                    errorCorrectionLevel: "L", // lowest = fewest modules = chunkiest lines
                    margin: 4, // wider white quiet zone around the QR
                    scale: 8, // bigger blocks, clearly separated
                    color: { dark: "#000000", light: "#ffffff" },
                });
            } catch (err) {
                console.error("UPI QR render failed:", err);
            }
        };

        // Re-render whenever the modal opens or the order/amount changes
        watch(
            () => [props.visible, upiData.value],
            () => {
                if (props.visible) renderQR();
            },
            { immediate: true }
        );

        const printInvoice = () => {
            // Snapshot the live canvas into a PNG <img> so the print window keeps
            // the pixels (a blank canvas would otherwise be copied).
            const canvas = document.querySelector("#pos-invoice canvas");
            if (canvas) {
                const img = document.createElement("img");
                img.src = canvas.toDataURL("image/png");
                img.width = canvas.width;
                img.height = canvas.height;
                img.className = "upi-qr-img";
                canvas.parentNode.replaceChild(img, canvas);
                // Restore the canvas in the modal after printing
                setTimeout(() => {
                    if (img.parentNode) {
                        img.parentNode.replaceChild(canvas, img);
                        renderQR();
                    }
                }, 0);
            }

            var invoiceContent = document.getElementById("pos-invoice").innerHTML;
            var newWindow = window.open("", "", "height=500, width=500");
            newWindow.document.write(
                `<link rel="stylesheet" href="${posInvoiceCssUrl}"><html><style scoped>table {border-collapse: collapse;width: 100%;}table, th, td {border: 1px solid #bbb;}th, td {padding: 1px;text-align: center;} img.upi-qr-img {image-rendering: pixelated; image-rendering: crisp-edges; -webkit-print-color-adjust: exact; print-color-adjust: exact;}</style><body>`
            );
            newWindow.document.write(invoiceContent);
            newWindow.document.write("</body></html>");
            newWindow.document.close();
            // Wait for all images (including QR) to load before printing
            var images = newWindow.document.images;
            if (images.length === 0) {
                newWindow.print();
            } else {
                var loaded = 0;
                var total = images.length;
                var printed = false;
                var doPrint = () => {
                    loaded++;
                    if (loaded >= total && !printed) {
                        printed = true;
                        newWindow.print();
                    }
                };
                for (var i = 0; i < images.length; i++) {
                    if (images[i].complete) {
                        doPrint();
                    } else {
                        images[i].onload  = doPrint;
                        images[i].onerror = doPrint; // print anyway if image fails
                    }
                }
            }
        };

        // Half of the order tax — used to display CGST and SGST separately on the invoice
        const halfTaxAmount = computed(() => {
            const total = parseFloat(props.order?.tax_amount);
            return isNaN(total) ? 0 : total / 2;
        });

        return {
            onClose,
            formatDate,
            selectedWarehouse,
            formatAmountCurrency,
            printInvoice,
            upiCanvas,
            upiData,
            halfTaxAmount,
        };
    },
});
</script>
<style scoped>
table {
    border-collapse: collapse;
    width: 100%;
}

table, th, td {
    border: 1px solid #bbb;
}

th, td {
    padding: 1px;
    text-align: center;
}
.upi-qr-canvas { image-rendering: pixelated; image-rendering: crisp-edges; }
</style>