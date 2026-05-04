<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoController extends Controller
{
    public function index()
    {
        $logos = DB::table('logo')->get();
        
        return response()->json([
            'success' => true,
            'data' => $logos
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'required|file',
            'location' => 'required|in:1,2' // 1: Everywhere, 2: PDF
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename = 'logo_' . time() . '.' . $extension;
            
            // In legacy it was saved in ../images/logos/
            // We'll save it in public/images/logos/ to match the legacy structure
            $path = 'images/logos/' . $filename;
            $file->move(public_path('images/logos'), $filename);

            DB::table('logo')->where('id', $request->location)->update([
                'path' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logo updated successfully',
                'path' => $path
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded'
        ], 400);
    }
}
