<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    private function getAuthenticatedUser()
    {
        if (auth('api')->check()) {
            return auth('api')->user();
        }
        if (auth('api_admin')->check()) {
            return auth('api_admin')->user();
        }
        return null;
    }

    public function index()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        return response()->json(['success' => true, 'data' => $user]);
    }

    public function update(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        if ($request->filled('password')) {
            if (!$request->filled('current_password')) {
                return response()->json(['success' => false, 'message' => 'Current password is required to set a new password'], 422);
            }
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Current password does not match'], 422);
            }
        }

        if ($user instanceof Member) {
            $data = $request->only(['full_name', 'email', 'phone', 'address', 'city', 'post_code', 'country']);
            
            if ($user->type == 'wholesaler') {
                $data = array_merge($data, $request->only(['cperson', 'vat_num', 'company_reg_num', 'website']));
            }
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
            $user->update($data);
        } else {
            $data = $request->only(['full_name', 'email']);
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
            $user->update($data);
        }

        return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
    }

    public function getClients()
    {
        $user = $this->getAuthenticatedUser();
        
        $isPrinter = ($user instanceof \App\Models\AdminUser && $user->user_type == 'printer') ||
                     ($user instanceof \App\Models\Member && $user->type == 'printer');

        if (!$user || !$isPrinter) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $clients = Member::whereIn('type', ['client', 'wholesaler'])->where('active', 'yes')->orderBy('full_name')->get(['id', 'full_name', 'address', 'city', 'post_code', 'country', 'email', 'phone', 'type', 'vat_num']);
        return response()->json(['success' => true, 'data' => $clients]);
    }

    public function getOrders()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $memberId = ($user instanceof Member) ? $user->id : 0;
        
        $orders = \App\Models\Order::where('member_id', $memberId)
            ->with(['items.product' => function($query) {
                $query->select('id', 'name', 'code')->with('productImages');
            }])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }
}
