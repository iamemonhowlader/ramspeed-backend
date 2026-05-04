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

        // Newsletter subscription logic (Legacy matched)
        if ($request->subscribe === 'yes') {
            $exists = DB::table('subscribers')->where('mail_adresse', $request->email)->exists();
            if (!$exists) {
                $subscriberId = DB::table('subscribers')->insertGetId([
                    'mail_adresse' => $request->email,
                    'notes' => '',
                    'created' => now(),
                    'deleted' => '1',
                    'first_name' => '',
                    'last_name' => '',
                    'custom1' => 'Unconfirmed',
                    'custom2' => '',
                    'custom3' => '',
                    'custom4' => '',
                    'unsubscribe_code' => $key,
                ]);

                // category_id: 3 for member, 4 for wholesaler
                $categoryId = ($type == 'wholesaler') ? 4 : 3;
                DB::table('categories_subscribers')->insert([
                    'category_id' => $categoryId,
                    'subscriber_id' => $subscriberId,
                ]);
            }
        }

        // Email logic (Legacy matched)
        if ($type == 'wholesaler') {
            $htmlMessage = '
            <html>
            <head>
            <title>New wholesaler registration</title>
            <style>
            html, body, div, span, h1, h2, h3, h4, h5, h6, p, font, ul, ol, dl, li, blockquote, pre, form, fieldset, label, legend, input, input, a {margin:0; padding:0px; border:0; outline:0; vertical-align:baseline;}
            :focus {outline:0;}
            ul, ol {list-style:none;}
            img {outline:0; border:0;}
            a img {border:0;}
            body {background:#fff; font-family:Helvetica; color:#808080; font-size:14px; padding:10px;}
            a {color:#09F; text-decoration: none;}
            a:hover {color:#09F; text-decoration: underline;}
            .black {color:#000;}
            .nt {color:#b1b1b1; font-size:22px;}
            .cms {color:#808080; font-size:10px; padding-top:5px;}
            </style>
            </head>
            <body>
            <table cellpadding="10" cellspacing="0" border="0" width="100%">
            <tr>
            <td colspan="2" bgcolor="#efefef">
            <span class="black">
            You have a new wholesaler registration on your website. <br /> Information are listed below</span>
            </td>
            </tr>
            <tr>
            <td colspan="2" bgcolor="#f7f7f7">
            <table cellpadding="2" cellspacing="0" border="0">
            <tr><td valign="top">Company Name:</td><td valign="top">'.$request->full_name.'</td></tr>
            <tr><td valign="top">Contact Person:</td><td valign="top">'.$request->cperson.'</td></tr>
            <tr><td valign="top">Username:</td><td valign="top">'.$request->username.'</td></tr>
            <tr><td valign="top">Email:</td><td valign="top">'.$request->email.'</td></tr>
            <tr><td valign="top">Address:</td><td valign="top">'.$request->address.'</td></tr>
            <tr><td valign="top">Post Code:</td><td valign="top">'.$request->post_code.'</td></tr>
            <tr><td valign="top">City:</td><td valign="top">'.$request->city.'</td></tr>
            <tr><td valign="top">Country:</td><td valign="top">'.$request->country.'</td></tr>
            <tr><td valign="top">Phone:</td><td valign="top">'.$request->phone.'</td></tr>
            <tr><td valign="top">Fax:</td><td valign="top">'.$request->fax.'</td></tr>
            <tr><td valign="top">VAT Number:</td><td valign="top">'.$request->vat_num.'</td></tr>
            <tr><td valign="top"></td><td valign="top">Please note that the user status has been set to inactive and you have to review his info from the administration pannel and set it to active if you approve the current wholesaler. Also, if you approved the wholesaler, you have to contact him and inform him about his account approval.</td></tr>
            </table>
            </td>
            </tr>
            </table>
            </body>
            </html>';

            \Illuminate\Support\Facades\Mail::html($htmlMessage, function ($message) {
                $message->to('info@ramspeedcy.com')
                        ->subject('New wholesaler registration');
            });

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Your account is pending approval.',
                'data' => $member
            ]);
        } else {
            $htmlMessage = '
            <html>
            <head>
            <style>
            html, body, div, span, h1, h2, h3, h4, h5, h6, p, font, ul, ol, dl, li, blockquote, pre, form, fieldset, label, legend, input, input, a {margin:0; padding:0px; border:0; outline:0; vertical-align:baseline;}
            :focus {outline:0;}
            ul, ol {list-style:none;}
            img {outline:0; border:0;}
            a img {border:0;}
            body {background:#fff; font-family:Helvetica; color:#808080; font-size:14px; padding:10px;}
            a {color:#09F; text-decoration: none;}
            a:hover {color:#09F; text-decoration: underline;}
            .black {color:#000;}
            .nt {color:#b1b1b1; font-size:22px;}
            .cms {color:#808080; font-size:10px; padding-top:5px;}
            </style>
            </head>
            <body>
            <table cellpadding="10" cellspacing="0" border="0" width="100%">
            <tr>
            <td colspan="2" bgcolor="#efefef">
            <span class="black">
            Thank you for your registration</span>
            </td>
            </tr>
            <tr>
            <td colspan="2" bgcolor="#f7f7f7">
            Please <a href="https://ramspeedcy.com/registration_confirm.php?email='.$request->email.'&key='.$key.'&lng=en">click here</a> to confirm your email address.
            </td>
            </tr>
            </table>
            </body>
            </html>';

            \Illuminate\Support\Facades\Mail::html($htmlMessage, function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Member Registration');
            });

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please check your email to confirm your account.',
                'data' => $member
            ]);
        }
    }

    public function logout()
    {
        if (auth('api')->check()) auth('api')->logout();
        if (auth('api_admin')->check()) auth('api_admin')->logout();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}
