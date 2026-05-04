<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class AdminMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $members = $query->orderBy('full_name')->paginate(50);
        return response()->json(['success' => true, 'data' => $members]);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);
        $data = $request->only(['full_name', 'email', 'phone', 'address', 'city', 'post_code', 'country', 'active', 'type']);
        if ($request->filled('password')) $data['password'] = $request->password;
        
        $member->update($data);
        return response()->json(['success' => true, 'message' => 'Member updated successfully']);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();
        return response()->json(['success' => true, 'message' => 'Member deleted']);
    }
}
