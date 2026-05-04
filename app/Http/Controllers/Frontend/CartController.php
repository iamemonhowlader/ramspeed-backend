<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function getCart()
    {
        $user = auth('api')->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $cartItems = Cart::where('member_id', $user->id)->get();
        return response()->json(['success' => true, 'data' => $cartItems]);
    }

    public function addItem(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $item = Cart::updateOrCreate(
            ['member_id' => $user->id, 'product_id' => $request->product_id, 'store_type' => $request->store_type ?? 2],
            [
                'quantity' => $request->quantity,
                'price' => $request->price,
                'supplier_id' => $request->supplier_id ?? 0
            ]
        );

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function removeItem(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        Cart::where('member_id', $user->id)
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function clearCart()
    {
        $user = auth('api')->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        Cart::where('member_id', $user->id)->delete();

        return response()->json(['success' => true]);
    }
}
