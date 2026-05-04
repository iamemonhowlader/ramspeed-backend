<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['invoiceStore', 'invoiceOnline', 'invoiceWire', 'invoiceWholesale', 'orderItems']);

        // Search filter
        if ($request->has('s') && $request->s) {
            $search = $request->s;
            $numericSearch = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $numericSearch) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
                
                if ($numericSearch) {
                    $q->orWhereHas('invoiceStore', function($iq) use ($numericSearch) {
                        $iq->where('id', 'like', '%' . $numericSearch . '%');
                    })
                    ->orWhereHas('invoiceOnline', function($iq) use ($numericSearch) {
                        $iq->where('id', 'like', '%' . $numericSearch . '%');
                    })
                    ->orWhereHas('invoiceWire', function($iq) use ($numericSearch) {
                        $iq->where('id', 'like', '%' . $numericSearch . '%');
                    })
                    ->orWhereHas('invoiceWholesale', function($iq) use ($numericSearch) {
                        $iq->where('id', 'like', '%' . $numericSearch . '%');
                    });
                }
            });
        }

        // Date range filter
        if ($request->has('from') && $request->from) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->has('to') && $request->to) {
            $query->whereDate('date', '<=', $request->to);
        }

        $orders = $query->orderBy('id', 'desc')->paginate(50);

        // Calculate Stats
        $stats = $this->calculateStats($request);

        return response()->json([
            'success' => true,
            'data' => $orders,
            'stats' => $stats
        ]);
    }

    protected function calculateStats(Request $request)
    {
        // Total Sales (All non-cancelled orders)
        $totalSales = Order::where('cancelled', 'no')
            ->sum('grand_total');

        // Estimated Profit
        $activeOrders = Order::where('cancelled', 'no')
            ->with('orderItems.product')
            ->get();

        $estimatedProfit = 0;
        foreach ($activeOrders as $order) {
            $cost = $this->findOrderCost($order);
            // Profit = Grand Total - VAT - Cost
            // Note: Grand total in legacy might include shipping, but profit usually excludes it if shipping is a pass-through cost.
            // Following legacy: $orderProfit = $grandtotal - $vat - findOrderProfit($order['id']);
            $profit = (float)$order->grand_total - (float)$order->vat - $cost;
            if ($profit > 0) {
                $estimatedProfit += $profit;
            }
        }

        // Total Expenses
        $totalExpenses = DB::table('expenses')->sum('GROSS');

        // Cyprus Stock Calculation
        $vatCy = DB::table('vat')->where('id', 2)->value('vat') ?? 19;
        
        $cyStockProducts = DB::table('products')
            ->where('active', 'yes')
            ->where('availability_cy', '>', 0)
            ->where(function($q) {
                $q->where('price_cy', '>', 0)->orWhere('price_sup_cy', '>', 0);
            })
            ->get();

        $cyStockValue = 0;
        foreach ($cyStockProducts as $p) {
            $cost = 0;
            if ($p->price_sup_cy > 0) {
                $cost = $p->price_sup_cy;
            } else {
                $cyProfit = DB::table('suppliers_info')->where('user_id', $p->supplier_id)->value('cyprofit') ?? 0;
                // price_cy is usually Selling Price in Cyprus. Cost = Selling Price / (1 + Profit%)
                $cost = $p->price_cy / (1 + ($cyProfit / 100));
            }
            $val = $cost * $p->availability_cy;
            // Add VAT
            $val = $val * (1 + ($vatCy / 100));
            $cyStockValue += $val;
        }

        return [
            'totalSales' => round($totalSales, 2),
            'estimatedProfit' => round($estimatedProfit, 2),
            'totalExpenses' => round($totalExpenses, 2),
            'cyprusStock' => round($cyStockValue, 2),
        ];
    }

    protected function findOrderCost($order)
    {
        $totalCost = 0;
        foreach ($order->orderItems as $item) {
            $cost = 0;
            if ($item->product_id == 713) {
                // DIAFORA
                $cost = $item->price;
            } elseif ($item->store_type == 1) {
                // China store - use product dollar price (cost)
                $cost = $item->product ? $item->product->price : $item->price;
            } else {
                // Cyprus store
                if ($item->product && $item->product->price_sup_cy > 0) {
                    $cost = $item->product->price_sup_cy;
                } elseif ($item->product) {
                    $cyProfit = DB::table('suppliers_info')->where('user_id', $item->product->supplier_id)->value('cyprofit') ?? 0;
                    $cost = $item->product->price_cy / (1 + ($cyProfit / 100));
                } else {
                    $cost = $item->price;
                }
            }
            $totalCost += ($cost * $item->quantity);
        }
        return $totalCost;
    }

    public function show($id)
    {
        $order = Order::with(['orderItems.product', 'member', 'invoiceStore', 'invoiceOnline', 'invoiceWire', 'invoiceWholesale'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->status; // e.g., 'completed'
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order status updated']);
    }

    public function toggleCancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->cancelled = $request->cancelled; // 'yes' or 'no'
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order cancellation status updated']);
    }

    public function toggleShipping(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->delivered = $request->delivered; // 'yes' or 'no'
        $order->save();

        return response()->json(['success' => true, 'message' => 'Shipping status updated']);
    }

    public function invoice($id)
    {
        return response("Invoice view for Order #$id (Implementation pending)");
    }

    public function pdf($id)
    {
        return response("PDF generation for Order #$id (Implementation pending)");
    }

    public function exportPdf(Request $request)
    {
        return response("Exporting Orders PDF with filters: " . json_encode($request->all()));
    }

    public function trialBalance(Request $request)
    {
        return response("Generating Trial Balance with filters: " . json_encode($request->all()));
    }
}
