<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class AdminMemberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:members',
            'email' => 'required|email|unique:members',
            'password' => 'required|min:6',
            'full_name' => 'required',
        ]);

        $data = $request->all();
        // Laravel's bcrypt or simple hash depends on your auth setup
        // But for legacy compatibility we might need to check how it was hashed
        $data['password'] = bcrypt($request->password);
        
        $member = Member::create($data);
        return response()->json(['success' => true, 'message' => 'Member created successfully', 'data' => $member]);
    }

    public function show($id)
    {
        $member = Member::findOrFail($id);
        return response()->json(['success' => true, 'data' => $member]);
    }

    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('b2b_approved')) {
            $query->where('b2b_approved', $request->b2b_approved);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $members = $query->orderBy('id', 'desc')->paginate(100);
        return response()->json(['success' => true, 'data' => $members]);
    }

    public function toggleB2bApproval($id)
    {
        $member = Member::findOrFail($id);
        $member->b2b_approved = $member->b2b_approved === 'yes' ? 'no' : 'yes';
        $member->save();
        return response()->json(['success' => true, 'message' => 'B2B status updated']);
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
