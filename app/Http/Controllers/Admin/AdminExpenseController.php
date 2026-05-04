<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class AdminExpenseController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $query = Expense::query();

        // Date range filter
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        // Type filter
        if ($request->filled('type') && $request->type != '0' && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('s')) {
            $search = $request->s;
            $query->where(function($q) use ($search) {
                $q->where('invoice', 'like', "%$search%")
                  ->orWhere('other', 'like', "%$search%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('full_name', 'like', "%$search%");
                  });
            });
        }

        // Calculate Stats based on the filtered query (without pagination)
        $statsQuery = clone $query;
        $allFilteredExpenses = $statsQuery->get();

        $stats = [
            'total_gross' => $allFilteredExpenses->where('cancelled', 0)->sum('GROSS'),
            'vat_19' => $allFilteredExpenses->where('cancelled', 0)->where('VAT', 0.19)->sum('Calculated_VAT'),
            'vat_9' => $allFilteredExpenses->where('cancelled', 0)->where('VAT', 0.09)->sum('Calculated_VAT'),
            'vat_5' => $allFilteredExpenses->where('cancelled', 0)->where('VAT', 0.05)->sum('Calculated_VAT'),
            'service_receipt_gross' => $allFilteredExpenses->where('cancelled', 0)->where('Service_Receipt', 0)->sum('GROSS')
        ];

        $expenses = $query->with('supplier')->orderBy('date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $expenses,
            'stats' => $stats
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $expense = Expense::findOrFail($request->id);
        $expense->update([$request->field => $request->value]);
        return response()->json(['success' => true]);
    }
}
