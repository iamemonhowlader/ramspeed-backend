<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vat;
use App\Helpers\PricingHelper;

class InvoiceService
{
    public function getInvoiceDetails($orderId)
    {
        $order = Order::with('member')->findOrFail($orderId);
        $vat = $this->getInvoiceVat($order);
        $items = $this->getInvoiceItems($order, $vat);
        
        $total = 0;
        $discount = 0;
        foreach ($items as $item) {
            $total += $item['line_price'];
            $discount += $item['line_discount'];
        }

        $invoiceDiscount = $this->getAdditionalDiscount($order);
        
        $subtotal = $total - $discount - $invoiceDiscount;
        $totalVat = ($order->ZeroVAT == 1) ? 0 : (($subtotal * $vat['percentage']) / 100);
        $grandTotal = $subtotal + $totalVat + $order->shipping_cost;

        return [
            'order' => $order,
            'items' => $items,
            'vat' => $vat,
            'total' => $total,
            'discount' => $discount,
            'invoice_discount' => $invoiceDiscount,
            'subtotal' => $subtotal,
            'total_vat' => $totalVat,
            'grand_total' => $grandTotal
        ];
    }

    public function getInvoiceVat($order)
    {
        if ($order->date > '2014-01-13 00:00:00') {
            $percentage = ($order->vat_percentage > 0) ? $order->vat_percentage : 19;
        } else {
            $percentage = 18;
        }
        return ['percentage' => $percentage, 'decimal' => (1 + ($percentage / 100))];
    }

    public function getInvoiceItems($order, $vat)
    {
        $items = OrderItem::where('order_id', $order->id)->get();
        $formattedItems = [];

        foreach ($items as $r) {
            $data = [
                'id' => $r->id,
                'product_id' => $r->product_id,
                'name' => ($r->product_id == 713) ? $r->temp_name : ($r->product->name ?? 'Unknown'),
                'quantity' => $r->quantity,
                'discount' => $r->discount
            ];

            // Replicate Price Logic from store_functions.php lines 172-183
            if ($r->store_type == 2 || $r->store_type == 3) {
                $itemPrice = round($r->price / $vat['decimal'], 2);
            } else {
                $itemPrice = round($r->price_euro / $vat['decimal'], 2);
            }

            $data['price'] = $itemPrice;
            $data['line_price'] = $itemPrice * $r->quantity;
            $data['line_discount'] = ($order->code_version == 1 || $r->store_type == 3) 
                ? $r->discount 
                : round($r->discount / $vat['decimal'], 2);

            $data['line_vat'] = ($order->ZeroVAT == 1) ? 0 : (($data['line_price'] - $data['line_discount']) * $vat['percentage'] / 100);
            $data['line_total'] = $data['line_price'] - $data['line_discount'] + $data['line_vat'];

            $formattedItems[] = $data;
        }

        return $formattedItems;
    }

    public function getAdditionalDiscount($order)
    {
        if ($order->code_version == 1) {
            if ($order->discount_type == 1) return $order->discount;
            if ($order->discount_type == 2) return $order->discount_percentage_to_amount;
        }
        return 0;
    }
}
