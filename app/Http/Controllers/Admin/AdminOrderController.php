<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $query = Order::with('member');

        if ($request->has('dateSort')) {
            $fDay = $request->get('fDay', 1);
            $fMonth = $request->get('fMonth', 1);
            $fYear = $request->get('fYear', date('Y'));
            
            $fromDate = "{$fYear}-" . str_pad($fMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($fDay, 2, '0', STR_PAD_LEFT) . " 00:00:00";
            $query->where('date', '>=', $fromDate);
            
            if ($request->has('tDay') && $request->tDay > 0) {
                $toDate = "{$request->tYear}-{$request->tMonth}-{$request->tDay} 23:59:59";
                $query->where('date', '<=', $toDate);
            }
        }

        $orders = $query->orderBy('date', 'desc')->paginate(50);
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $details = $this->invoiceService->getInvoiceDetails($id);
        return response()->json([
            'success' => true,
            'data' => $details
        ]);
    }

    public function toggleCancel(Request $request)
    {
        $order = Order::findOrFail($request->q);
        $order->cancelled = $request->v;
        $order->save();
        return response()->json(['success' => true]);
    }

    public function toggleShipping(Request $request)
    {
        $order = Order::findOrFail($request->q);
        $order->shipped = $request->v;
        $order->save();
        return response()->json(['success' => true]);
    }

    public function addExtraProduct(Request $request, $orderId)
    {
        OrderItem::create([
            'order_id' => $orderId,
            'product_id' => $request->product_id ?? 713, 
            'temp_name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'price_euro' => $request->price, 
            'store_type' => 2 
        ]);

        return response()->json(['success' => true, 'message' => 'Product added to order']);
    }
}
