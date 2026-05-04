<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Menu;
use App\Models\Vat;
use App\Models\Wishlist;
use App\Models\SupplierDiscount;
use App\Services\CurrencyService;
use App\Helpers\PricingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $query = Product::with('productImages')->where('active', 'yes');

        // Filtering by category (menu_item_id)
        if ($request->has('category')) {
            $query->where('menu_item_id', $request->category);
        }

        // Filtering by featured
        if ($request->has('featured')) {
            $featuredPids = DB::table('featured')->pluck('pid');
            if ($featuredPids->isNotEmpty()) {
                $query->whereIn('id', $featuredPids);
            } else {
                // If no featured items, just get latest products as fallback
                // or we can just let it be empty if that's preferred
            }
        }

        // Filtering by new arrivals (can be explicit or just latest)
        if ($request->has('new_arrival')) {
            if ($request->new_arrival === 'yes') {
                $query->where('new_arrival', 'yes');
            }
            // If explicit new_arrival=yes returns nothing, we usually want latest products
        }

        // Filtering by offers
        if ($request->has('offer')) {
            $query->where('offer', 'yes');
        }

        // Limit results if requested
        if ($request->has('limit')) {
            $products = $query->orderBy('id', 'desc')->limit($request->limit)->get();
            
            // Fallback: If filtered results are empty, get latest products
            if ($products->isEmpty()) {
                $products = Product::with('productImages')
                    ->where('active', 'yes')
                    ->orderBy('id', 'desc')
                    ->limit($request->limit)
                    ->get();
            }
        } else {
            $products = $query->orderBy('id', 'desc')->paginate(20);
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function categories()
    {
        $categories = Menu::orderBy('sort')->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show($id, Request $request)
    {
        $product = Product::with(['productImages', 'supplier'])->where('id', $id)->where('active', 'yes')->firstOrFail();
        
        $supplierInfo = $product->supplier;
        $vatData = Vat::find(1);
        $vat = $vatData ? (1 + ($vatData->vat / 100)) : 1.19;

        if ($product->store_profit > 0) {
            $profit = ($product->price * $product->store_profit) / 100;
        } else {
            $profit = ($product->price * ($supplierInfo->profit ?? 0)) / 100;
        }
        
        $price = ($product->price + $profit) * $vat;

        $priceCyprus = 'no';
        $prCy = 0;
        $whPrice = 0;

        if ($product->price_cy > 0 || $product->price_sup_cy > 0) {
            $priceCyprus = 'yes';
            $vatCyData = Vat::find(2);
            $vatCy = $vatCyData ? (1 + ($vatCyData->vat / 100)) : 1.19;

            if ($product->price_sup_cy > 0) {
                if ($product->store_profit > 0) {
                    $profitCy = ($product->price_sup_cy * $product->store_profit) / 100;
                    $prCy = ($product->price_sup_cy + $profitCy);
                    $whPrice = $product->price_sup_cy;
                } else {
                    $profitCy = ($product->price_sup_cy * ($supplierInfo->cysupprofit ?? 0)) / 100;
                    $prCy = ($product->price_sup_cy + $profitCy);
                    $whPrice = $prCy;
                }
                $prCy = $prCy * $vatCy;
            } else {
                if ($product->store_profit > 0) {
                    $removePercent = PricingHelper::amountBeforePercentageAdded($supplierInfo->cyprofit ?? 0, $product->price_cy);
                    $profitCy = ($removePercent * $product->store_profit) / 100;
                    $prCy = ($removePercent + $profitCy);
                    $whPrice = $removePercent;
                } else {
                    $prCy = $product->price_cy;
                    $removePercent = PricingHelper::amountBeforePercentageAdded($supplierInfo->cyprofit ?? 0, $product->price_cy);
                    $whPrice = $removePercent;
                }
                $prCy = $prCy * $vatCy;
            }
        }

        $discounts = SupplierDiscount::where('supplier_id', $product->supplier_id)->orderBy('min_products', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'price' => $price,
                'price_cy' => $prCy,
                'price_cyprus' => $priceCyprus,
                'wh_price' => $whPrice,
                'discounts' => $discounts
            ]
        ]);
    }
}
