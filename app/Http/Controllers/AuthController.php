<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\AdminUser;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function memberLogin(Request $request)
    {
        $login = $request->input('username') ?: $request->input('email');
        $password = $request->input('password');

        \Log::info('Login attempt', [
            'login' => $login,
            'password_length' => strlen($password),
            'has_username' => $request->has('username'),
            'has_email' => $request->has('email'),
            'has_password' => $request->has('password')
        ]);
        
        // 1. Try to find in members table (Retail/Wholesale)
        $users = Member::where(function($query) use ($login) {
            $query->where('username', $login)
                  ->orWhere('email', $login);
        })->get();

        $inactiveFound = false;

        foreach ($users as $user) {
            if ($password == $user->password) {
                if ($user->active != 'yes') {
                    $inactiveFound = true;
                    continue;
                }

                \Log::info('Member logged in successfully', [
                    'username' => $user->username,
                    'type' => $user->type
                ]);

                $token = auth('api')->login($user);

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'type' => $user->type,
                        'table' => 'members'
                    ]
                ]);
            }
        }

        if ($inactiveFound) {
            return response()->json(['success' => false, 'message' => 'Your account is inactive.'], 401);
        }

        // 2. Try to find in admin_users table (Admin/Supplier/Printer)
        $admins = AdminUser::where(function($query) use ($login) {
            $query->where('username', $login)
                  ->orWhere('email', $login);
        })->get();

        foreach ($admins as $admin) {
            if ($password == $admin->password) {
                if ($admin->active != 'yes') {
                    return response()->json(['success' => false, 'message' => 'Your account is inactive.'], 401);
                }

                $token = auth('api_admin')->login($admin);

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $admin->id,
                        'username' => $admin->username,
                        'full_name' => $admin->full_name,
                        'type' => $admin->user_type,
                        'table' => 'admin_users'
                    ]
                ]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Wrong username or password.'], 401);
    }

    public function adminLogin(Request $request)
    {
        $ip = $request->ip();
        
        // Check Banlist
        if (DB::table('banned')->where('ip', $ip)->exists()) {
            return response()->json(['success' => false, 'message' => 'You are banned from the server!'], 403);
        }

        $user = AdminUser::where('username', $request->username)->first();

        if ($user) {
            if ($user->active != 'yes') {
                return response()->json(['success' => false, 'message' => 'Your account is inactive.'], 403);
            }

            if ($request->password == $user->password) {
                $token = auth('api_admin')->login($user);

                // Log User
                UserLog::create([
                    'username' => $user->username,
                    'ip' => $ip,
                    'proxy' => $request->header('X-Forwarded-For') ?? ''
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Admin login successful',
                    'token' => $token,
                    'user_type' => $user->user_type
                ]);
            }
        }

        // Handle Failed Attempts & Banning
        $attempts = Session::get('attempts', 0) + 1;
        Session::put('attempts', $attempts);

        if ($attempts >= 3) {
            DB::table('banned')->insert(['ip' => $ip]);
            Session::put('attempts', 0);
            return response()->json(['success' => false, 'message' => 'You are banned!'], 403);
        }

        return response()->json(['success' => false, 'message' => 'The username or password you entered is incorrect!'], 401);
    }

    public function register(Request $request)
    {
        $type = $request->input('type', 'client'); // default to client
        
        // Validate uniqueness
        if (Member::where('username', $request->username)->exists()) {
            return response()->json(['success' => false, 'message' => 'Username already exists'], 422);
        }
        if (Member::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email already exists'], 422);
        }

        $key = substr(md5(rand(0, 1000000000) . time()), 7, 12);

        $memberData = [
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password, // Legacy uses plain text
            'address' => $request->address,
            'post_code' => $request->post_code,
            'city' => $request->city,
            'country' => $request->country,
            'phone' => $request->phone,
            'fax' => $request->fax ?? '',
            'active' => 'no',
            'type' => $type,
            'skey' => $key,
            'date' => now(),
        ];

        if ($type == 'wholesaler') {
            $memberData['vat_num'] = $request->vat_num ?? '';
            $memberData['company_reg_num'] = $request->company_reg_num ?? '';
            $memberData['cperson'] = $request->cperson ?? '';
            $memberData['website'] = $request->website ?? '';
        }

        $member = Member::create($memberData);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Please check your email for confirmation.',
            'data' => $member
        ]);
    }

    public function logout()
    {
        if (auth('api')->check()) auth('api')->logout();
        if (auth('api_admin')->check()) auth('api_admin')->logout();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}
