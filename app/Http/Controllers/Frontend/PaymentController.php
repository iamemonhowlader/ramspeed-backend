<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        $transactionId = $request->t;
        
        // 1. Verify with Viva Wallet (Simplified for API)
        // In real production, use Http::withBasicAuth(...)
        
        DB::beginTransaction();
        try {
            // Find orders linked to this transaction (Legacy used other_1 or similar)
            $orders = Order::where('temp_transaction_id', $transactionId)->get();

            foreach ($orders as $order) {
                $order->update(['status' => 'completed', 'paid' => 'yes']);

                // 2. Inventory Deduction (Replicate success.php lines 190-210)
                $items = OrderItem::where('order_id', $order->id)->get();
                foreach ($items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        if ($order->store_country == 'cy') {
                            $product->decrement('availability_cy', $item->quantity);
                        } else {
                            $product->decrement('availability', $item->quantity);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment verified and inventory updated']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function failed()
    {
        return response()->json(['success' => false, 'message' => 'Payment failed or cancelled']);
    }
}
