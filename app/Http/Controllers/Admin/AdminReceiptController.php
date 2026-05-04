<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;

class AdminReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Receipt::query();

        if ($request->filled('s')) {
            $search = $request->s;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%");
        }

        $receipts = $query->orderBy('date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $receipts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $receipt = Receipt::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New receipt added successfully',
            'data' => $receipt
        ]);
    }

    public function printReceipt($id)
    {
        $receipt = Receipt::findOrFail($id);
        
        $logoUrl = "https://ramspeed.eu/img/logo.png"; // Fallback URL or get from DB if configured

        // Reconstruct the legacy HTML structure
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Print Receipt #{$receipt->id}</title>
            <style>
                body { background: #fff; padding: 20px; font-family: Arial, sans-serif; font-size: 14px; }
                @media print {
                    @page { margin: 0; }
                    body { margin: 1cm; }
                    button.no-print { display: none; }
                }
                .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; display: flex; justify-content: space-between; }
                .title { text-align: center; font-weight: bold; font-size: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
                .table-info { width: 100%; font-size: 11px; margin-bottom: 12px; }
                .table-data { width: 100%; border-collapse: collapse; font-size: 12px; }
                .table-data th { background: #E6E6E6; padding: 5px; text-align: left; }
                .table-data td { padding: 5px; border-bottom: 1px solid #ccc; vertical-align: top; }
                .footer { margin-top: 12px; padding: 7px; font-size: 11px; background: #E6E6E6; }
            </style>
        </head>
        <body onload="window.print()">
            <div class="header">
                <div>
                    <!-- Fallback to text if logo is missing -->
                    <h1 style="margin:0;">RAMSPEED</h1>
                </div>
                <div style="font-size:11px; text-align:right;">
                    RAMSPEED COMPUTERS<br>
                    Cyprus<br>
                    info@ramspeed.eu
                </div>
            </div>
            
            <div class="title">Receipt</div>

            <table class="table-info">
                <tr>
                    <td width="50%"></td>
                    <td width="50%" align="right">Receipt Number: {$receipt->id}</td>
                </tr>
            </table>

            <table class="table-data">
                <tr>
                    <th width="25%">Date</th>
                    <th width="50%">Name</th>
                    <th width="25%" style="text-align:center;">Price</th>
                </tr>
                <tr>
                    <td>{$receipt->date->format('F j Y')}</td>
                    <td>
                        <strong>{$receipt->name}</strong><br>
                        " . nl2br(e($receipt->description)) . "
                    </td>
                    <td align="center">&euro;" . number_format($receipt->amount, 2) . "</td>
                </tr>
                <tr>
                    <td colspan="3" align="right" style="padding-top:15px;">
                        <strong>Total: &euro;" . number_format($receipt->amount, 2) . "</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top:15px; font-size:11px;">
                        Δεν γίνονται αλλαγές σε προϊόντα στα οποία οι συσκευασίες έχουν ανοιχθεί ή έχουν υποστεί ζημιά.
                    </td>
                </tr>
            </table>

            <div class="footer">Thank you for your preference.</div>
            
            <div style="margin-top:20px;" class="no-print">
                <button onclick="window.print()" style="padding:10px 20px; cursor:pointer;">Print Now</button>
            </div>
        </body>
        </html>
        HTML;

        return response($html)->header('Content-Type', 'text/html');
    }
}
