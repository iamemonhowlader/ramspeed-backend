<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\AdminUser;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

class AdminProductController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $query = Product::query();

        // Security: Supplier only sees their own products
        if (Session::get('user_type') == 3) {
            $query->where('supplier_id', Session::get('userid'));
        }

        if ($request->has('Category') && $request->Category) {
            $query->where('menu_item_id', $request->Category);
        }

        if ($request->has('Supplier') && $request->Supplier) {
            $query->where('supplier_id', $request->Supplier);
        }

        if ($request->has('s') && $request->s) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->s . '%')
                  ->orWhere('code', 'like', '%' . $request->s . '%');
            });
        }

        $products = $query->with(['productImages', 'supplier.supplierInfo'])->orderBy('id', 'desc')->paginate(50);
        
        $products->getCollection()->transform(function($product) {
            return $this->attachCalculatedPrices($product);
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    protected function attachCalculatedPrices($product)
    {
        $vat = 1.0; // Default
        $vat_row = \Illuminate\Support\Facades\DB::table('vat')->where('id', 1)->first();
        if ($vat_row) {
            $vat = 1 + ($vat_row->vat / 100);
        }

        $rate = 1.1634; // Default USD to EUR
        $currency = \Illuminate\Support\Facades\DB::table('money_currency')->where('currency', 'USD')->first();
        if ($currency) {
            $rate = $currency->rate;
        }

        // 1. Price Euro (Converted from Price $)
        $product->calculated_price_euro = $product->price / $rate;

        // 2. Selling Price (Euro)
        $profit_percent = $product->store_profit;
        if ($profit_percent <= 0 && $product->supplier && $product->supplier->supplierInfo) {
            $profit_percent = $product->supplier->supplierInfo->profit;
        }
        
        $price_with_profit = $product->price + ($product->price * $profit_percent / 100);
        $price_with_vat = $price_with_profit * $vat;
        $product->calculated_selling_price_euro = $price_with_vat / $rate;

        // 3. Price In Cyprus (Euro)
        if ($product->price_sup_cy > 0) {
            $product->calculated_price_cyprus_euro = $product->price_sup_cy;
        } else {
            $product->calculated_price_cyprus_euro = $product->price_cy;
        }

        // 4. CY + PROF/TAX (Euro)
        $pr_cy = $product->calculated_price_cyprus_euro;
        if ($product->price_sup_cy > 0) {
            $cy_profit_percent = $product->store_profit;
            if ($cy_profit_percent <= 0 && $product->supplier && $product->supplier->supplierInfo) {
                $cy_profit_percent = $product->supplier->supplierInfo->cysupprofit;
            }
            $pr_cy = $product->price_sup_cy + ($product->price_sup_cy * $cy_profit_percent / 100);
        }
        $product->calculated_cy_profit_tax_euro = $pr_cy;

        return $product;
    }

    public function store(Request $request)
    {
        $product = new Product();
        $this->mapProductFields($product, $request);
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product added Successfully',
            'product' => $product
        ]);
    }

    public function update(Request $request, $listing_id)
    {
        $product = Product::findOrFail($listing_id);

        // Security check for suppliers
        if (Session::get('user_type') == 3 && $product->supplier_id != Session::get('userid')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->mapProductFields($product, $request);
        $product->save();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = "image_" . uniqid() . "." . $image->getClientOriginalExtension();
            $image->move(storage_path('app/public/product_images'), $filename);

            ProductImage::create([
                'product_id' => $product->id,
                'filename' => $filename
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product Updated Successfully',
            'product' => $product
        ]);
    }

    protected function mapProductFields($product, $request)
    {
        $product->menu_item_id = $request->id;
        $product->name = $request->name;
        $product->namegr = $request->namegr;
        $product->description = $request->description;
        $product->descriptiongr = $request->descriptiongr;
        $product->price = $request->price;
        $product->price_sup_cy = $request->price_sup_cy;
        $product->code = $request->code;
        $product->active = $request->active;
        $product->availability = $request->availability;
        $product->availability_cy = $request->availability_cy;
        $product->weihgt = $request->weight; 
        $product->size = $request->ssize;
        $product->new_arrival = $request->arrival;
        $product->offer = $request->offer;
        $product->options = $request->options ?? "";
        $product->supplier_id = $request->supplier;
        $product->store_profit = $request->store_profit ?? 0;
        $product->minquantity = $request->minquantity ?? 1;

        // Price Conversion Logic for Cyprus
        $priceCyUnconverted = $request->price_cy;
        $priceCy = $priceCyUnconverted;

        if ($priceCyUnconverted > 0) {
            $priceCy = $this->currencyService->convert($priceCyUnconverted, 'USD', 'EUR');
            $adminUser = AdminUser::with('supplierInfo')->find($request->supplier);
            $supplierInfo = $adminUser ? $adminUser->supplierInfo : null;

            if ($supplierInfo) {
                if ($supplierInfo->cytax > 0) {
                    $priceCy += ($priceCy * $supplierInfo->cytax / 100);
                }
                if ($supplierInfo->cyprofit > 0) {
                    $priceCy += ($priceCy * $supplierInfo->cyprofit / 100);
                }
            }
        }

        $product->price_cy_unconverted = $priceCyUnconverted;
        $product->price_cy = $priceCy;
    }

    public function show($id)
    {
        $product = Product::with(['productImages', 'supplier.supplierInfo'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $this->attachCalculatedPrices($product)
        ]);
    }

    public function dashboardData()
    {
        $categories = \App\Models\Menu::where('parent', '!=', 0)
            ->where('type', 4)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'namegr']);

        $suppliers = AdminUser::where('user_type', 'supplier')
            ->where('active', 'yes')
            ->orderBy('full_name', 'asc')
            ->get(['id', 'full_name']);

        $outOfStock = Product::where('availability', 0)
            ->where('availability_cy', 0)
            ->with(['productImages', 'supplier.supplierInfo'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        
        $outOfStock->transform(function($product) {
            return $this->attachCalculatedPrices($product);
        });

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'outOfStock' => $outOfStock
        ]);
    }

    public function destroy($listing_id)
    {
        $product = Product::findOrFail($listing_id);
        
        foreach ($product->images as $image) {
            $path = public_path('shop-onmi-admin/uploads/product_images/' . $image->filename);
            if (File::exists($path)) {
                File::delete($path);
            }
            $image->delete();
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product Deleted!']);
    }
}
