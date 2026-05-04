<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WishlistController extends Controller
{
    public function index()
    {
        $memberId = Session::get('member_id');
        $wishlist = Wishlist::with('product')->where('member_id', $memberId)->get();
        return response()->json(['success' => true, 'data' => $wishlist]);
    }

    public function toggle(Request $request)
    {
        $productId = $request->q; // Legacy parameter name 'q'
        $memberId = Session::get('member_id');

        if (!$memberId) return response()->json(['success' => false, 'message' => 'Please login'], 401);

        $exists = Wishlist::where('product_id', $productId)->where('member_id', $memberId)->first();

        if ($exists) {
            $exists->delete();
            return response()->json(['success' => true, 'action' => 'removed']);
        } else {
            Wishlist::create(['product_id' => $productId, 'member_id' => $memberId]);
            return response()->json(['success' => true, 'action' => 'added']);
        }
    }
    
}
