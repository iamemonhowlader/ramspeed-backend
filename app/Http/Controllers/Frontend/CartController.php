<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SupplierDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        $storeType = Session::get('store_type', 1);
        
        return response()->json([
            'success' => true,
            'cart' => $cart,
            'store_type' => $storeType
        ]);
    }

    public function add(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity;
        $storeType = $request->store_type;

        $cart = Session::get('cart', []);

        // 1. Validate Store Type (Mixing logic from cart.php lines 55-80)
        if (!empty($cart)) {
            $firstItem = reset($cart);
            if ($firstItem['store_type'] != $storeType) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Mixing store types in cart is not allowed.'
                ], 422);
            }
        }

        $product = Product::findOrFail($productId);
        
        // 2. Min Quantity Check
        if ($quantity < ($product->minquantity ?? 1)) {
            $quantity = $product->minquantity ?? 1;
        }

        // 3. Add or Update Cart
        $cart[$productId] = [
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => $quantity,
            'price' => $product->price,
            'store_type' => $storeType,
            'supplier_id' => $product->supplier_id
        ];

        Session::put('cart', $cart);
        Session::put('store_type', $storeType);

        return response()->json(['success' => true, 'message' => 'Product added to cart', 'cart' => $cart]);
    }

    public function remove($id)
    {
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);
        
        return response()->json(['success' => true, 'message' => 'Product removed', 'cart' => $cart]);
    }
}
