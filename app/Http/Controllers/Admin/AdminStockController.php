<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStockController extends Controller
{
    public function index(Request $request)
    {
        // VAT for Cyprus products (id=2)
        $vatCy = DB::table('vat')->where('id', 2)->value('vat') ?? 19;

        $query = DB::table('products as p')
            ->leftJoin('suppliers_info as si', 'si.user_id', '=', 'p.supplier_id');

        // Active/inactive filter
        $activeFilter = $request->input('active', 'yes');
        if ($activeFilter === 'yes') {
            // Active stock: active=yes AND has qty in Cyprus
            $query->where('p.active', 'yes')
                  ->where('p.availability_cy', '>', 0)
                  ->where(function ($q) {
                      $q->where('p.price_cy', '>', 0)->orWhere('p.price_sup_cy', '>', 0);
                  });
        } elseif ($activeFilter === 'no') {
            // Inactive = active='no' (like MOMIN_Inactive_Stock.php)
            $query->where('p.active', 'no');
        } elseif ($activeFilter === 'no_qty') {
            // Inactive Without Stock = active='no' AND availability_cy=0 AND (price_cy > 0 OR price_sup_cy > 0)
            $query->where('p.active', 'no')
                  ->where('p.availability_cy', 0)
                  ->where(function ($q) {
                      $q->where('p.price_cy', '>', 0)->orWhere('p.price_sup_cy', '>', 0);
                  });
        } else {
            // all — show all with Cyprus stock
            $query->where('p.availability_cy', '>', 0)
                  ->where(function ($q) {
                      $q->where('p.price_cy', '>', 0)->orWhere('p.price_sup_cy', '>', 0);
                  });
        }

        // Search filter
        if ($request->filled('s')) {
            $s = $request->s;
            $query->where(function ($q) use ($s) {
                $q->where('p.name', 'like', "%$s%")
                  ->orWhere('p.code', 'like', "%$s%")
                  ->orWhere('si.full_name', 'like', "%$s%");
            });
        }

        // Country filter
        if ($request->filled('country') && $request->country !== 'all') {
            if ($request->country === 'cyprus') {
                $query->where('p.price_sup_cy', '>', 0);
            } elseif ($request->country === 'china') {
                $query->where('p.price_sup_cy', '<=', 0)->where('p.price_cy', '>', 0);
            }
        }

        $products = $query->select([
            'p.id', 'p.code', 'p.name', 'p.active',
            'p.availability_cy', 'p.price_cy', 'p.price_sup_cy',
            'p.supplier_id', 'si.full_name as supplier_name',
            'si.cyprofit', 'si.cytax'
        ])->orderBy('p.id', 'desc')->paginate(50);

        // Transform & calculate values
        $transformed = collect($products->items())->map(function ($p) use ($vatCy) {
            $isCyprus = $p->price_sup_cy > 0;

            if ($isCyprus) {
                // Cyprus stock: direct supplier price
                $costPrice = $p->price_sup_cy;
            } else {
                // China stock: reverse-calculate cost from selling price
                $cyProfit = $p->cyprofit ?? 0;
                $costPrice = $cyProfit > 0
                    ? round($p->price_cy / (1 + ($cyProfit / 100)), 2)
                    : $p->price_cy;
            }

            $qty = $p->availability_cy;
            $lineTotal = round($costPrice * $qty * (1 + ($vatCy / 100)), 2);

            // Count total sales for this product from completed/bank-transfer orders
            $totalSales = DB::table('order_items as oi')
                ->join('orders_temp as ot', 'ot.id', '=', 'oi.order_id')
                ->where('oi.product_id', $p->id)
                ->where(function ($q) {
                    $q->where('ot.status', 'completed')
                      ->orWhere(function ($q2) {
                          $q2->where('ot.status', 'pending')
                             ->where('ot.payment_type', 'Bank transfer');
                      });
                })
                ->sum('oi.quantity');

            return [
                'serialNumber' => $p->id,
                'productCode'  => $p->code,
                'supplier'     => $p->supplier_name ?? 'N/A',
                'productName'  => $p->name,
                'active'       => $p->active === 'yes',
                'country'      => $isCyprus ? 'Cyprus' : 'China',
                'quantity'     => (int)$qty,
                'price'        => $costPrice,
                'total'        => $lineTotal,
                'sales'        => (int)$totalSales,
            ];
        });

        // Grand totals
        $allItems = $transformed;
        $grandTotalCy  = $allItems->where('country', 'Cyprus')->sum('total');
        $grandTotalCh  = $allItems->where('country', 'China')->sum('total');

        return response()->json([
            'success'     => true,
            'data'        => [
                'data'         => $transformed->values(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
            'summary'     => [
                'cyprusTotal' => round($grandTotalCy, 2),
                'chinaTotal'  => round($grandTotalCh, 2),
                'grandTotal'  => round($grandTotalCy + $grandTotalCh, 2),
            ]
        ]);
    }
}
