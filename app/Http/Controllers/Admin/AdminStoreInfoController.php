<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStoreInfoController extends Controller
{
    public function index()
    {
        $storeInfo = DB::table('store_info')->get();
        return response()->json([
            'success' => true,
            'data' => $storeInfo
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'Store_Data' => 'required|string'
        ]);

        DB::table('store_info')
            ->where('id', $request->id)
            ->update(['Store_Data' => $request->Store_Data]);

        return response()->json([
            'success' => true,
            'message' => 'Store Data updated successfully!'
        ]);
    }
}
