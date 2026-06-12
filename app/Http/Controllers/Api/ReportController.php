<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Examyou\RestAPI\ApiResponse;

class ReportController extends ApiBaseController
{
    // public function profitLoss()
    // {
    //     $request = request();
    //     $warehouse = warehouse();

    //     $sales = Order::where('order_type', 'sales');
    //     $purchases = Order::where('order_type', 'purchases');
    //     $salesReturns = Order::where('order_type', 'sales-returns');
    //     $purchaseReturns = Order::where('order_type', 'purchase-returns');
    //     $stockTransferTransfered = Order::where('order_type', 'stock-transfers');
    //     $stockTransferReceived = Order::where('order_type', 'stock-transfers');
    //     $expenses = Expense::select('amount');

    //     $paymentReceived = Payment::where('payment_type', 'in');
    //     $paymentSent = Payment::where('payment_type', 'out');

    //     // Dates Filters
    //     if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
    //         $dates = $request->dates;
    //         $startDate = $dates[0];
    //         $endDate = $dates[1];

    //         $sales = $sales->whereRaw('orders.order_date >= ?', [$startDate])
    //             ->whereRaw('orders.order_date <= ?', [$endDate]);
    //         $purchases = $purchases->whereRaw('orders.order_date >= ?', [$startDate])
    //             ->whereRaw('orders.order_date <= ?', [$endDate]);
    //         $salesReturns = $salesReturns->whereRaw('orders.order_date >= ?', [$startDate])
    //             ->whereRaw('orders.order_date <= ?', [$endDate]);
    //         $purchaseReturns = $purchaseReturns->whereRaw('orders.order_date >= ?', [$startDate])
    //             ->whereRaw('orders.order_date <= ?', [$endDate]);
    //         $stockTransferTransfered = $stockTransferTransfered->whereRaw('orders.order_date >= ?', [$startDate])
    //             ->whereRaw('orders.order_date <= ?', [$endDate]);
    //         $stockTransferReceived = $stockTransferReceived->whereRaw('orders.order_date >= ?', [$startDate])
    //             ->whereRaw('orders.order_date <= ?', [$endDate]);
    //         $expenses = $expenses->whereRaw('expenses.date >= ?', [$startDate])
    //             ->whereRaw('expenses.date <= ?', [$endDate]);

    //         $paymentReceived = $paymentReceived->whereRaw('payments.date >= ?', [$startDate])
    //             ->whereRaw('payments.date <= ?', [$endDate]);
    //         $paymentSent = $paymentSent->whereRaw('payments.date >= ?', [$startDate])
    //             ->whereRaw('payments.date <= ?', [$endDate]);
    //     }

    //     $sales = $sales->where('orders.warehouse_id', $warehouse->id);
    //     $purchases = $purchases->where('orders.warehouse_id', $warehouse->id);
    //     $salesReturns = $salesReturns->where('orders.warehouse_id', $warehouse->id);
    //     $purchaseReturns = $purchaseReturns->where('orders.warehouse_id', $warehouse->id);
    //     $stockTransferTransfered = $stockTransferTransfered->where('orders.from_warehouse_id', $warehouse->id);
    //     $stockTransferReceived = $stockTransferReceived->where('orders.warehouse_id', $warehouse->id);
    //     $expenses = $expenses->where('expenses.warehouse_id', $warehouse->id);

    //     $paymentReceived = $paymentReceived->where('payments.warehouse_id', $warehouse->id);
    //     $paymentSent = $paymentSent->where('payments.warehouse_id', $warehouse->id);

    //     $sales = $sales->sum('total');
    //     $purchases = $purchases->sum('total');
    //     $salesReturns = $salesReturns->sum('total');
    //     $purchaseReturns = $purchaseReturns->sum('total');
    //     $stockTransferTransfered = $stockTransferTransfered->sum('total');
    //     $stockTransferReceived = $stockTransferReceived->sum('total');
    //     $expenses = $expenses->sum('amount');

    //     $paymentReceived = $paymentReceived->sum('amount');
    //     $paymentSent = $paymentSent->sum('amount');

    //     $profit = $sales + $purchaseReturns + $stockTransferTransfered - $purchases - $salesReturns - $stockTransferReceived - $expenses;
    //     $profitByPayment = $paymentReceived - $paymentSent - $expenses;

    //     return ApiResponse::make('Success', [
    //         'sales' => $sales,
    //         'purchases' => $purchases,
    //         'sales_returns' => $salesReturns,
    //         'purchase_returns' => $purchaseReturns,
    //         'stock_transfer_transfered' => $stockTransferTransfered,
    //         'stock_transfer_received' => $stockTransferReceived,
    //         'expenses' => $expenses,
    //         'profit' => $profit,
    //         'payment_received' => $paymentReceived,
    //         'payment_sent' => $paymentSent,
    //         'profit_by_payment' => $profitByPayment,
    //     ]);
    // }

    public function profitLoss()
    {
        $request = request();
        $dateResults = [];
        $company = company();
        $startDate = null;
        $endDate = null;
        $dateArray = [];

        // Dates Filters
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $startDateStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startDate, 'UTC')
                ->setTimezone($company->timezone)
                ->startOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $startDateEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $startDate, 'UTC')
                ->setTimezone($company->timezone)
                ->endOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');

            $startDateInCompanyTimezone = Carbon::createFromFormat('Y-m-d H:i:s', $startDate, 'UTC')
                ->setTimezone($company->timezone)
                ->format('Y-m-d');

            $endDateInCompanyTimezone = Carbon::createFromFormat('Y-m-d H:i:s', $endDate, 'UTC')
                ->setTimezone($company->timezone)
                ->format('Y-m-d');

            $period = CarbonPeriod::create($startDateInCompanyTimezone, $endDateInCompanyTimezone);

            $dateRangeArray = [];
            foreach ($period as $date) {
                $dateRangeArray[] = $date->format('Y-m-d');
            }


            $dateRanges = array_reverse($dateRangeArray);
            foreach ($dateRanges as $dateRange) {
                $startDateStartTime = Carbon::createFromFormat('Y-m-d', $dateRange, 'UTC')
                    ->setTimezone($company->timezone)
                    ->startOfDay()
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');
                $startDateEndTime = Carbon::createFromFormat('Y-m-d', $dateRange, 'UTC')
                    ->setTimezone($company->timezone)
                    ->endOfDay()
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');

                $dateArray[] = [
                    'date' => $dateRange,
                    'start' => $startDateStartTime,
                    'end' => $startDateEndTime,
                ];

                $dateResults[] = [
                    'date' => Carbon::createFromFormat('Y-m-d', $dateRange, $company->timezone)
                        ->startOfDay()
                        ->setTimezone('UTC')
                        ->format('Y-m-d\TH:i:sP'),
                    'result' => $this->getProfitLossByDates($startDateStartTime, $startDateEndTime)
                ];
            }
        }

        return ApiResponse::make('Success', [
            'results' => $this->getProfitLossByDates($startDate, $endDate),
            'dates' => $dateResults,
            'dateArray' => $dateArray,
        ]);
    }

    public function monthlyGstReport()
    {
        $request = request();
        $warehouse = warehouse();
        $company = company();

        [$startDate, $endDate] = $this->getMonthDateRange($request, $company);

        $query = Order::where('order_type', 'sales')
            ->where('orders.warehouse_id', $warehouse->id)
            ->with([
                'user:id,name,phone',
                'orderPayments.payment.paymentMode:id,name',
            ])
            ->orderBy('order_date');

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [$startDate, $endDate]);
        }

        $orders = $query->get();

        $results = $orders->map(function ($order) use ($company) {
            $halfTax = round($order->tax_amount / 2, 2);

            $paymentModes = $order->orderPayments
                ->map(fn($op) => optional(optional($op->payment)->paymentMode)->name)
                ->filter()
                ->unique()
                ->implode(', ');

            return [
                'order_date'      => $order->order_date->setTimezone($company->timezone)->format('d/m/Y'),
                'invoice_number'  => $order->invoice_number,
                'customer_name'   => $order->user ? $order->user->name : '-',
                'customer_phone'  => $order->user ? $order->user->phone : '-',
                'total_quantity'  => (float) $order->total_quantity,
                'taxable_value'   => round($order->subtotal, 2),
                'cgst_amount'     => $halfTax,
                'sgst_amount'     => $halfTax,
                'total_tax'       => round($order->tax_amount, 2),
                'discount'        => round($order->discount, 2),
                'shipping'        => round($order->shipping, 2),
                'total'           => round($order->total, 2),
                'payment_mode'    => $paymentModes ?: '-',
                'payment_status'  => $order->payment_status,
                'due_amount'      => round($order->due_amount, 2),
            ];
        });

        $totals = [
            'total_quantity' => $results->sum('total_quantity'),
            'taxable_value'  => round($results->sum('taxable_value'), 2),
            'cgst_amount'    => round($results->sum('cgst_amount'), 2),
            'sgst_amount'    => round($results->sum('sgst_amount'), 2),
            'total_tax'      => round($results->sum('total_tax'), 2),
            'discount'       => round($results->sum('discount'), 2),
            'shipping'       => round($results->sum('shipping'), 2),
            'total'          => round($results->sum('total'), 2),
        ];

        return ApiResponse::make('Success', [
            'results' => $results->values(),
            'totals'  => $totals,
        ]);
    }

    private function getMonthDateRange($request, $company)
    {
        if ($request->has('month_year') && $request->month_year) {
            $parts = explode('-', $request->month_year);
            $year = (int) $parts[0];
            $month = (int) $parts[1];

            $startDate = Carbon::createFromDate($year, $month, 1, $company->timezone)
                ->startOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');

            $endDate = Carbon::createFromDate($year, $month, 1, $company->timezone)
                ->endOfMonth()
                ->endOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');

            return [$startDate, $endDate];
        }

        return [null, null];
    }

    public function getProfitLossByDates($startDate, $endDate)
    {
        $request = request();
        $warehouse = warehouse();

        $sales = Order::where('order_type', 'sales');
        $purchases = Order::where('order_type', 'purchases');
        $salesReturns = Order::where('order_type', 'sales-returns');
        $purchaseReturns = Order::where('order_type', 'purchase-returns');
        $stockTransferTransfered = Order::where('order_type', 'stock-transfers');
        $stockTransferReceived = Order::where('order_type', 'stock-transfers');
        $expenses = Expense::select('amount');

        $paymentReceived = Payment::where('payment_type', 'in');
        $paymentSent = Payment::where('payment_type', 'out');

        // Dates Filters
        if ($startDate != null && $endDate != null) {
            $sales = $sales->whereBetween('orders.order_date', [$startDate, $endDate]);
            $purchases = $purchases->whereBetween('orders.order_date', [$startDate, $endDate]);
            $salesReturns = $salesReturns->whereBetween('orders.order_date', [$startDate, $endDate]);
            $purchaseReturns = $purchaseReturns->whereBetween('orders.order_date', [$startDate, $endDate]);
            $stockTransferTransfered = $stockTransferTransfered->whereBetween('orders.order_date', [$startDate, $endDate]);
            $stockTransferReceived = $stockTransferReceived->whereBetween('orders.order_date', [$startDate, $endDate]);
            $expenses = $expenses->whereBetween('expenses.date', [$startDate, $endDate]);

            $paymentReceived = $paymentReceived->whereBetween('payments.date', [$startDate, $endDate]);
            $paymentSent = $paymentSent->whereBetween('payments.date', [$startDate, $endDate]);
        }

        $sales = $sales->where('orders.warehouse_id', $warehouse->id);
        $purchases = $purchases->where('orders.warehouse_id', $warehouse->id);
        $salesReturns = $salesReturns->where('orders.warehouse_id', $warehouse->id);
        $purchaseReturns = $purchaseReturns->where('orders.warehouse_id', $warehouse->id);
        $stockTransferTransfered = $stockTransferTransfered->where('orders.from_warehouse_id', $warehouse->id);
        $stockTransferReceived = $stockTransferReceived->where('orders.warehouse_id', $warehouse->id);
        $expenses = $expenses->where('expenses.warehouse_id', $warehouse->id);

        $paymentReceived = $paymentReceived->where('payments.warehouse_id', $warehouse->id);
        $paymentSent = $paymentSent->where('payments.warehouse_id', $warehouse->id);

        $sales_quantities = $sales->sum('total_quantity');
        $sales = $sales->sum('total');
        $purchases = $purchases->sum('total');
        $salesReturns = $salesReturns->sum('total');
        $purchaseReturns = $purchaseReturns->sum('total');
        $stockTransferTransfered = $stockTransferTransfered->sum('total');
        $stockTransferReceived = $stockTransferReceived->sum('total');
        $expenses = $expenses->sum('amount');

        $paymentReceived = $paymentReceived->sum('amount');
        $paymentSent = $paymentSent->sum('amount');

        $profit = $sales + $purchaseReturns + $stockTransferTransfered - $purchases - $salesReturns - $stockTransferReceived - $expenses;
        $profitByPayment = $paymentReceived - $paymentSent - $expenses;

        return [
            'sales' => $sales,
            'sales_quantities' => $sales_quantities,
            'purchases' => $purchases,
            'sales_returns' => $salesReturns,
            'purchase_returns' => $purchaseReturns,
            'stock_transfer_transfered' => $stockTransferTransfered,
            'stock_transfer_received' => $stockTransferReceived,
            'expenses' => $expenses,
            'profit' => $profit,
            'payment_received' => $paymentReceived,
            'payment_sent' => $paymentSent,
            'profit_by_payment' => $profitByPayment,
        ];
    }
}
