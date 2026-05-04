<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function registerMember(Request $request)
    {
        DB::beginTransaction();
        try {
            $key = substr(md5(rand()), 0, 12);
            
            $member = Member::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'post_code' => $request->post_code,
                'phone' => $request->phone,
                'active' => 'no',
                'confirm_key' => $key,
                'type' => 'member'
            ]);

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Registration successful. Please check your email.',
                'confirm_key' => $key // For development/testing
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request)
    {
        $key = $request->k;
        $member = Member::where('confirm_key', $key)->first();

        if ($member) {
            $member->update(['active' => 'yes', 'confirm_key' => '']);
            return response()->json(['success' => true, 'message' => 'Account activated']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid key'], 400);
    }
}
