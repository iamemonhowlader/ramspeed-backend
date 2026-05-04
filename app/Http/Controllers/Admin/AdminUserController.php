<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminUser::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('full_name')->paginate(50);
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:admin_users',
            'email' => 'required|email|unique:admin_users',
            'password' => 'required|min:6',
            'full_name' => 'required',
        ]);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        
        $user = AdminUser::create($data);
        return response()->json(['success' => true, 'message' => 'Admin user created successfully', 'data' => $user]);
    }

    public function show($id)
    {
        $user = AdminUser::findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = AdminUser::findOrFail($id);
        $data = $request->only(['full_name', 'email', 'active', 'user_type']);
        if ($request->filled('password')) $data['password'] = bcrypt($request->password);
        
        $user->update($data);
        return response()->json(['success' => true, 'message' => 'Admin user updated successfully']);
    }

    public function destroy($id)
    {
        $user = AdminUser::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Admin user deleted']);
    }
}
