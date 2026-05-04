<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminBalanceSheetController extends Controller
{
    public function getQuarterlyData(Request $request, $quarter)
    {
        $year = $request->input('year', date('Y'));
        
        // Define months for each quarter
        $quarterMonths = [
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12]
        ];
        
        if (!isset($quarterMonths[$quarter])) {
            return response()->json(['success' => false, 'message' => 'Invalid quarter'], 400);
        }
        
        $months = $quarterMonths[$quarter];
        
        // Format names
        $monthNamesEn = [
            1 => 'January', 2 => 'February', 3 => 'March', 
            4 => 'April', 5 => 'May', 6 => 'June', 
            7 => 'July', 8 => 'August', 9 => 'September', 
            10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $monthNamesGr = [
            1 => 'Ιανουάριος', 2 => 'Φεβρουάριος', 3 => 'Μάρτιος', 
            4 => 'Απρίλιος', 5 => 'Μάιος', 6 => 'Ιούνιος', 
            7 => 'Ιούλιος', 8 => 'Αύγουστος', 9 => 'Σεπτέμβριος', 
            10 => 'Οκτώβριος', 11 => 'Νοέμβριος', 12 => 'Δεκέμβριος'
        ];
        
        $result = [];
        
        // Row 1: VAT due on output (Sales VAT)
        // Row 2: VAT on 0% purchases
        // Row 3: Total VAT (Row 1 + Row 2)
        // Row 4: VAT deducted (Purchases VAT)
        // Row 5: Payable/Receivable
        // Row 6: Net Sales
        // Row 7: Net Purchases
        
        $salesVat = [];
        $purchasesVat0 = [];
        $purchasesVat = [];
        $netSales = [];
        $netPurchases = [];
        
        foreach ($months as $index => $m) {
            $monthStr = str_pad($m, 2, '0', STR_PAD_LEFT);
            $startDate = $year . $monthStr . '01';
            $endDate = $year . $monthStr . '31235959';
            $endDateExpenses = $year . $monthStr . '31';
            
            // Sales VAT & Net Sales from orders_temp
            // In the legacy system, it filters by status='completed' or (status='pending' and payment='Bank transfer')
            $ordersSum = DB::table('orders_temp')
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhere(function($sq) {
                          $sq->where('status', 'pending')
                             ->where('payment_type', 'Bank transfer');
                      });
                })
                ->where('cancelled', 'no')
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->selectRaw('SUM(vat) as total_vat, SUM(grand_total) as grand_total')
                ->first();

            $salesVat[$index] = $ordersSum->total_vat ?? 0;
            // Net Sales = Grand Total - VAT (simplified approximation based on legacy behavior)
            $netSales[$index] = ($ordersSum->grand_total ?? 0) - ($ordersSum->total_vat ?? 0);

            $purchasesVat0[$index] = DB::table('expenses')
                ->where('cancelled', '0')
                ->where('VAT', '0.00')
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->sum('GROSS') * 0.19;
                
            $purchasesVat[$index] = DB::table('expenses')
                ->where('cancelled', '0')
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->sum('Calculated_VAT');
                
            $netPurchases[$index] = DB::table('expenses')
                ->where('cancelled', '0')
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->sum('Calculated_NET');
        }
        
        // Formulate the response to match the React components
        $data = [
            [
                'serial' => '1',
                'month' => 'Φ.Π.Α. οφειλόμενος αυτήν την περίοδο επί των εκροών',
                'month1' => $salesVat[0] > 0 ? number_format($salesVat[0], 2) : null,
                'month2' => $salesVat[1] > 0 ? number_format($salesVat[1], 2) : null,
                'month3' => $salesVat[2] > 0 ? number_format($salesVat[2], 2) : null,
                'totals' => array_sum($salesVat) > 0 ? number_format(array_sum($salesVat), 2) : null
            ],
            [
                'serial' => '2',
                'month' => 'Φ.Π.Α. οφειλόμενος αυτήν την περίοδο επί αποκτήσεων από άλλα Κράτη μέλη',
                'month1' => $purchasesVat0[0] > 0 ? number_format($purchasesVat0[0], 2) : null,
                'month2' => $purchasesVat0[1] > 0 ? number_format($purchasesVat0[1], 2) : null,
                'month3' => $purchasesVat0[2] > 0 ? number_format($purchasesVat0[2], 2) : null,
                'totals' => array_sum($purchasesVat0) > 0 ? number_format(array_sum($purchasesVat0), 2) : null
            ],
            [
                'serial' => '3',
                'month' => 'Συνολικό οφειλόμενο Φ.Π.Α. (το άθροισμα των τετραγώνων 1 και 2)',
                'month1' => ($salesVat[0] + $purchasesVat0[0]) > 0 ? number_format($salesVat[0] + $purchasesVat0[0], 2) : null,
                'month2' => ($salesVat[1] + $purchasesVat0[1]) > 0 ? number_format($salesVat[1] + $purchasesVat0[1], 2) : null,
                'month3' => ($salesVat[2] + $purchasesVat0[2]) > 0 ? number_format($salesVat[2] + $purchasesVat0[2], 2) : null,
                'totals' => (array_sum($salesVat) + array_sum($purchasesVat0)) > 0 ? number_format(array_sum($salesVat) + array_sum($purchasesVat0), 2) : null
            ],
            [
                'serial' => '4',
                'month' => 'Φ.Π.Α. που εκπίπτει αυτήν την περίοδο επί αγορών και άλλων εισροών',
                'month1' => $purchasesVat[0] > 0 ? number_format($purchasesVat[0], 2) : null,
                'month2' => $purchasesVat[1] > 0 ? number_format($purchasesVat[1], 2) : null,
                'month3' => $purchasesVat[2] > 0 ? number_format($purchasesVat[2], 2) : null,
                'totals' => array_sum($purchasesVat) > 0 ? number_format(array_sum($purchasesVat), 2) : null
            ],
            [
                'serial' => '5',
                'month' => 'Φ.Π.Α. ΠΛΗΡΩΤΕΟ Ή ΕΠΙΣΤΡΕΠΤΕΟ (Διαφορά μεταξύ των τετραγώνων 3 και 4)',
                'month1' => null, // Payable/Receivable is only calculated in totals in the legacy system
                'month2' => null,
                'month3' => null,
                'totals' => number_format((array_sum($salesVat) + array_sum($purchasesVat0)) - array_sum($purchasesVat), 2)
            ],
            [
                'serial' => '6',
                'month' => 'Ολική αξία εκροών (χωρίς Φ.Π.Α.)',
                'month1' => $netSales[0] > 0 ? number_format($netSales[0], 2) : null,
                'month2' => $netSales[1] > 0 ? number_format($netSales[1], 2) : null,
                'month3' => $netSales[2] > 0 ? number_format($netSales[2], 2) : null,
                'totals' => array_sum($netSales) > 0 ? number_format(array_sum($netSales), 2) : null
            ],
            [
                'serial' => '7',
                'month' => 'Ολική αξία εισροών (χωρίς Φ.Π.Α.)',
                'month1' => $netPurchases[0] > 0 ? number_format($netPurchases[0], 2) : null,
                'month2' => $netPurchases[1] > 0 ? number_format($netPurchases[1], 2) : null,
                'month3' => $netPurchases[2] > 0 ? number_format($netPurchases[2], 2) : null,
                'totals' => array_sum($netPurchases) > 0 ? number_format(array_sum($netPurchases), 2) : null
            ],
            [
                'serial' => '8A',
                'month' => 'Όλική αξία όλων των παραδόσεων αγαθών και συνδεόμενων υπηρεσιών (χωρίς Φ.Π.Α.) προς άλλα Κράτη μέλη.',
                'month1' => null, 'month2' => null, 'month3' => null, 'totals' => null
            ],
            [
                'serial' => '8B',
                'month' => 'Ολική αξία παροχής υπηρεσιών σε υ.φ.π. σε άλλα Κράτη μέλη',
                'month1' => null, 'month2' => null, 'month3' => null, 'totals' => null
            ],
            [
                'serial' => '9',
                'month' => 'Όλική αξία πωλήσεων που υπόκεινται στο ειδικό καθεστώς άρθρου 42Α.',
                'month1' => null, 'month2' => null, 'month3' => null, 'totals' => null
            ],
            [
                'serial' => '10',
                'month' => 'Όλική αξία πωλήσεων προς άλλα πρόσωπα που είναι εγγεγραμμένα σε άλλα Κράτη Μέλη',
                'month1' => null, 'month2' => null, 'month3' => null, 'totals' => null
            ],
            [
                'serial' => '11A',
                'month' => 'Όλική αξία αποκτήσεων αγαθών... από άλλα Κράτη Μέλη',
                'month1' => null, 'month2' => null, 'month3' => null, 'totals' => null
            ],
            [
                'serial' => '11B',
                'month' => 'Όλική αξία λήψης υπηρεσιών...',
                'month1' => null, 'month2' => null, 'month3' => null, 'totals' => null
            ],
        ];
        
        return response()->json([
            'success' => true,
            'year' => $year,
            'quarter' => $quarter,
            'months' => [
                'm1' => $monthNamesGr[$months[0]],
                'm2' => $monthNamesGr[$months[1]],
                'm3' => $monthNamesGr[$months[2]],
            ],
            'data' => $data
        ]);
    }
}
