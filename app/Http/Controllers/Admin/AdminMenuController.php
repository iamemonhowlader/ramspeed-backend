<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    public function index()
    {
        $menuItems = Menu::orderBy('sort', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $menuItems
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'namegr' => 'required|string',
            'active_page' => 'required|string',
            'type' => 'required',
            'parent' => 'nullable|integer'
        ]);

        $parent = $request->input('parent', 0);
        $maxSort = Menu::where('parent', $parent)->max('sort') ?? 0;

        $menuItem = Menu::create([
            'name' => $request->name,
            'namegr' => $request->namegr,
            'active_page' => $request->active_page,
            'type' => $request->type,
            'parent' => $parent,
            'sort' => $maxSort + 1,
            'preview' => '',
            'icon' => '',
            'custom_link' => '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu Item Added Successfully!',
            'data' => $menuItem
        ]);
    }

    public function show($id)
    {
        $menuItem = Menu::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $menuItem
        ]);
    }

    public function update(Request $request, $id)
    {
        $menuItem = Menu::findOrFail($id);
        
        $menuItem->update([
            'name' => $request->name,
            'namegr' => $request->namegr,
            'active_page' => $request->active_page,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu Item Edited Successfully!',
            'data' => $menuItem
        ]);
    }

    public function destroy($id)
    {
        $menuItem = Menu::findOrFail($id);
        
        // Recursively delete sub-items if necessary, 
        // but for now let's just delete the item.
        // Legacy system has complex recursive deletion.
        
        $menuItem->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted!'
        ]);
    }
}
