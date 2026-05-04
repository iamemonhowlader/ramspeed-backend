<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function saveOrder(Request $request)
    {
        $user = null;
        if (auth('api')->check()) {
            $user = auth('api')->user();
        } elseif (auth('api_admin')->check()) {
            $user = auth('api_admin')->user();
        }
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Please login to place an order.'], 401);
        }

        $memberId = ($user instanceof Member) ? $user->id : 0;
        $member = ($user instanceof Member) ? $user : null;
        
        $clientId = $request->client_id ?? 0;
        $isPrinter = ($user instanceof \App\Models\AdminUser && $user->user_type == 'printer') ||
                     ($user instanceof Member && $user->type == 'printer');

        $items = $request->input('items', []);
        
        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 422);
        }

        DB::beginTransaction();
        try {
            $vatZero = $request->vatZero ?? (($member && $member->type == 'wholesaler') ? 1 : 0);
            $vatPercentage = ($vatZero == 1) ? 0 : 19;

            $order = Order::create([
                'member_id' => $memberId,
                'client_id' => $clientId,
                'full_name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'post_code' => $request->postCode,
                'country' => $request->country ?? 280,
                'shipping_type' => $request->shipping_method,
                'shipping_cost' => $request->shipping,
                'subtotal' => $request->subtotal,
                'subtotal_euro' => $request->subtotal,
                'grand_total' => $request->total,
                'grand_total_euro' => $request->total,
                'discount' => $request->totalDiscount ?? 0,
                'total_line_discount' => $request->totalDiscount ?? 0,
                'vat' => $request->tax,
                'vat_percentage' => $vatPercentage,
                'ZeroVAT' => $vatZero,
                'date' => now(),
                'status' => 'pending',
                'payment_type' => $request->payment_method,
                'code_version' => 2,
            ]);

            foreach ($items as $item) {
                // If it's a custom product (added by printer), product_id might be 0 or a placeholder
                $productId = is_numeric($item['id']) ? $item['id'] : 713; // 713 was the placeholder for custom products in legacy

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'temp_name' => $item['is_custom'] ? $item['name'] : '',
                    'options_msg' => $item['options_msg'] ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'price_euro' => $item['price'],
                    'discount' => 0,
                    'discount_percentage' => 0,
                    'temp_id' => 0,
                    'store_type' => ($member && $member->type == 'wholesaler') ? 3 : 2, // 3 for wholesaler, 2 for retail
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Order created successfully', 
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
