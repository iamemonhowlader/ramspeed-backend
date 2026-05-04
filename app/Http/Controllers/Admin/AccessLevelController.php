<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLevel;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AccessLevelController extends Controller
{
    public function index(Request $request)
    {
        $levels = AccessLevel::with('user')->paginate(50);
        return response()->json(['success' => true, 'data' => $levels]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'uid' => 'required|unique:access_level,uid',
        ]);

        $data = $request->all();
        // Default values for permissions if not provided
        $permissions = ['menu', 'featured', 'news', 'shipping', 'banlist', 'user_account', 'user_level', 'members', 'suppliers', 'BalanceSheet'];
        foreach ($permissions as $p) {
            if (!isset($data[$p])) $data[$p] = '0';
        }

        $level = AccessLevel::create($data);
        return response()->json(['success' => true, 'message' => 'Access level created', 'data' => $level]);
    }

    public function show($id)
    {
        $level = AccessLevel::with('user')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $level]);
    }

    public function update(Request $request, $id)
    {
        $level = AccessLevel::findOrFail($id);
        $data = $request->all();
        $level->update($data);
        return response()->json(['success' => true, 'message' => 'Access level updated']);
    }

    public function destroy($id)
    {
        $level = AccessLevel::findOrFail($id);
        $level->delete();
        return response()->json(['success' => true, 'message' => 'Access level deleted']);
    }
}
