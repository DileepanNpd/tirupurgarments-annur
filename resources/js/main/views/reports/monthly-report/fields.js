import common from "../../../../common/composable/common";

const fields = () => {
    const { formatAmountCurrency } = common();

    const columns = [
        { title: 'Date', dataIndex: 'order_date', dbKey: 'order_date' },
        { title: 'Invoice No', dataIndex: 'invoice_number', dbKey: 'invoice_number' },
        { title: 'Customer', dataIndex: 'customer_name', dbKey: 'customer_name' },
        { title: 'Phone', dataIndex: 'customer_phone', dbKey: 'customer_phone' },
        { title: 'Qty', dataIndex: 'total_quantity', dbKey: 'total_quantity' },
        {
            title: 'Taxable Value',
            dataIndex: 'taxable_value',
            dbKey: 'taxable_value',
            dataFormat: (row) => formatAmountCurrency(row.taxable_value),
        },
        {
            title: 'CGST Amount',
            dataIndex: 'cgst_amount',
            dbKey: 'cgst_amount',
            dataFormat: (row) => formatAmountCurrency(row.cgst_amount),
        },
        {
            title: 'SGST Amount',
            dataIndex: 'sgst_amount',
            dbKey: 'sgst_amount',
            dataFormat: (row) => formatAmountCurrency(row.sgst_amount),
        },
        {
            title: 'Total Tax',
            dataIndex: 'total_tax',
            dbKey: 'total_tax',
            dataFormat: (row) => formatAmountCurrency(row.total_tax),
        },
        {
            title: 'Discount',
            dataIndex: 'discount',
            dbKey: 'discount',
            dataFormat: (row) => formatAmountCurrency(row.discount),
        },
        {
            title: 'Shipping',
            dataIndex: 'shipping',
            dbKey: 'shipping',
            dataFormat: (row) => formatAmountCurrency(row.shipping),
        },
        {
            title: 'Invoice Total',
            dataIndex: 'total',
            dbKey: 'total',
            dataFormat: (row) => formatAmountCurrency(row.total),
        },
        { title: 'Payment Mode', dataIndex: 'payment_mode', dbKey: 'payment_mode' },
        { title: 'Payment Status', dataIndex: 'payment_status', dbKey: 'payment_status' },
        {
            title: 'Due Amount',
            dataIndex: 'due_amount',
            dbKey: 'due_amount',
            dataFormat: (row) => row.due_amount > 0 ? formatAmountCurrency(row.due_amount) : '',
        },
    ];

    return { columns };
};

export default fields;
