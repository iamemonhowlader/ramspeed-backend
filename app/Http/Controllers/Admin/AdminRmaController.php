<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RmaRepair;
use Illuminate\Http\Request;

class AdminRmaController extends Controller
{
    public function index(Request $request)
    {
        $query = RmaRepair::query();

        if ($request->has('s') && $request->s) {
            $search = $request->s;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $rmaHistory = $query->orderBy('created_at', 'desc')->paginate(50);

        // Transform data to match frontend expectations
        $rmaHistory->getCollection()->transform(function($item) {
            $date = \Carbon\Carbon::parse($item->created_at);
            return [
                'ticketId' => $item->ticket_number ?: $item->id,
                'productName' => ($item->brand ? $item->brand . ' ' : '') . $item->model,
                'customerName' => $item->customer_name,
                'phoneNumber' => $item->customer_phone,
                'repairCost' => $item->price ? '€ ' . $item->price : '-',
                'issue' => $item->problem_description,
                'repairStatus' => $item->status,
                'details' => 'View',
                'dateTime' => $date->format('n/j/y - h:i A'), // Matches "5/6/20 - 10:50 AM" format
                'delivered' => (bool)$item->delivered,
                'id' => $item->id
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $rmaHistory
        ]);
    }

    public function show($id)
    {
        $rma = RmaRepair::findOrFail($id);
        
        // Deserialize accessories if stored as JSON, or handle it as string
        // Assuming for now it's a JSON string or comma separated
        $accessories = [];
        if ($rma->accessories) {
            $accessories = json_decode($rma->accessories, true) ?: [];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $rma->id,
                'ticketId' => $rma->ticket_number,
                'customerName' => $rma->customer_name,
                'phoneNumber' => $rma->customer_phone,
                'customerEmail' => $rma->customer_email,
                'deviceType' => $rma->device_type,
                'others' => $rma->device_type_other,
                'brand' => $rma->brand,
                'model' => $rma->model,
                'accessories' => $accessories,
                'passwordType' => $rma->password_type,
                'password' => $rma->password_value,
                'issue' => $rma->problem_description,
                'repairStatus' => $rma->status,
                'customerNote' => $rma->technician_notes,
                'repairCost' => $rma->price,
                'delivered' => (bool)$rma->delivered,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $rma = RmaRepair::findOrFail($id);
        
        $rma->update([
            'customer_name' => $request->customerName,
            'customer_phone' => $request->phoneNumber,
            'device_type' => $request->deviceType,
            'device_type_other' => $request->others,
            'brand' => $request->brand,
            'model' => $request->model,
            'accessories' => json_encode($request->accessories),
            'password_type' => $request->passwordType,
            'password_value' => $request->password,
            'problem_description' => $request->issue,
            'status' => $request->repairStatus,
            'technician_notes' => $request->customerNote,
            'price' => $request->repairCost,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RMA updated successfully'
        ]);
    }

    public function toggleDelivered(Request $request, $id)
    {
        $rma = RmaRepair::findOrFail($id);
        $rma->delivered = $request->delivered ? 1 : 0;
        $rma->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
