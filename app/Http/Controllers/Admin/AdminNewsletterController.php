<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class AdminNewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $newsletters
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string'
        ]);

        $newsletter = Newsletter::create([
            'Email' => $request->email,
            'Phone_Number' => $request->phone ?? ''
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Newsletter subscription added successfully!',
            'data' => $newsletter
        ]);
    }

    public function show($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $newsletter
        ]);
    }

    public function destroy($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Newsletter subscription deleted successfully!'
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $deletedCount = Newsletter::whereIn('id', $request->ids)->delete();
        
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} newsletter subscriptions deleted successfully!"
        ]);
    }
}
