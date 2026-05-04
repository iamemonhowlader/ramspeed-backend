<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\SupplierInfo;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSupplierController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index()
    {
        $suppliers = AdminUser::where('user_type', 'supplier')->orderBy('full_name')->get();
        return response()->json(['success' => true, 'data' => $suppliers]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = AdminUser::create([
                'full_name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password,
                'active' => $request->active,
                'user_type' => 'supplier'
            ]);

            SupplierInfo::create([
                'user_id' => $user->id,
                'username' => $request->username,
                'full_name' => $request->name,
                'cperson' => $request->cperson,
                'profit' => $request->profit ?? 0,
                'cyprofit' => $request->cyprofit ?? 0,
                'cysupprofit' => $request->cysupprofit ?? 0,
                'cytax' => $request->cytax ?? 0,
                'email' => $request->email,
                'phone' => $request->phone,
                'fax' => $request->fax,
                'vat_num' => $request->vat_num,
                'company_reg_num' => $request->reg_num,
                'address' => $request->address,
                'post_code' => $request->post_code,
                'city' => $request->city,
                'country' => $request->country,
                'website' => $request->website
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Supplier added successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = AdminUser::findOrFail($id);
        $info = SupplierInfo::where('user_id', $id)->first();

        $recalculate = ($request->cyprofit != $info->cyprofit || $request->cytax != $info->cytax);

        if ($request->active == 'no' && $user->active == 'yes') {
            Product::where('supplier_id', $id)->update(['active' => 'no']);
        } elseif ($request->active == 'yes' && $user->active == 'no') {
            Product::where('supplier_id', $id)->update(['active' => 'yes']);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'full_name' => $request->name,
                'email' => $request->email,
                'active' => $request->active
            ]);

            $info->update($request->all());

            if ($recalculate) {
                $products = Product::where('supplier_id', $id)->get();
                foreach ($products as $product) {
                    if ($product->price_cy_unconverted > 0) {
                        $price_cy = $this->currencyService->convert($product->price_cy_unconverted, 'USD', 'EUR');
                        if ($request->cytax > 0) $price_cy += ($price_cy * $request->cytax / 100);
                        if ($request->cyprofit > 0) $price_cy += ($price_cy * $request->cyprofit / 100);
                        $product->update(['price_cy' => $price_cy]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Supplier updated successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
