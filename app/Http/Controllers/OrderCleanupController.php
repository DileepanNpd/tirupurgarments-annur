<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderCleanupController extends Controller
{
    public function deletePosOrders(Request $request)
    {
        Log::info("Monthly Orders Cleanup - Token check");
        
        // 🔐 Token check
        $token = $request->header('X-API-TOKEN');
        if ($token !== config('app.cleanup_api_token')) {
            Log::info("Unauthorized Token Received: ".$token);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        
        Log::info("Monthly Orders Cleanup - Last month check");
        // ✅ Only run on last day of month
        if (now()->day <= 10) {
            Log::info("Day of Month: Less than 10 days - ".now()->day);
            return response()->json([
                'status' => 'skipped',
                'message' => 'Not the last day of the month.'
            ]);
        }
        Log::info("Monthly Orders Cleanup");
        $deleted = DB::table('orders as o')
                    ->where('o.invoice_type', 'pos')
                    ->where('o.created_at', '<', now()->startOfMonth())
                
                    // Must have at least one CASH payment
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                          ->from('order_payments as op')
                          ->join('payments as p', 'p.id', '=', 'op.payment_id')
                          ->whereColumn('op.order_id', 'o.id')
                          ->where('p.payment_mode_id', 1);
                    })
                
                    // Must NOT have any NON-CASH payment (ever)
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                          ->from('order_payments as op')
                          ->join('payments as p', 'p.id', '=', 'op.payment_id')
                          ->whereColumn('op.order_id', 'o.id')
                          ->where('p.payment_mode_id', '!=', 1);
                    })->delete();
        Log::info("Orders Deleted: ".$deleted);
        return response()->json([
            'status' => 'success',
            'deleted' => $deleted,
            'message' => "Deleted $deleted POS orders."
        ]);
    }
}
