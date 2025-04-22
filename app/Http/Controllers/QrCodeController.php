<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QrCodeController extends Controller
{

    public function generateQrCode(Request $request)
    {
        $branch_id = $request->input('branch_id'); // ✅ Get brand ID from request body
        // Check if an unused QR code exists
        $qrCode = DB::table('qr_codes')
            ->where('used', false)
            ->where('branch_id', $branch_id)
            ->latest()
            ->first();

        if (!$qrCode) {
            // Generate a new QR code only if no unused one exists
            $token = Str::random(10) . "BR" .$branch_id; // Generate a random token
            DB::table('qr_codes')->insert([
                'token' => $token,
                'used' => false,
                'branch_id' => $branch_id,
                'created_at' => now(),
            ]);
        } else {
            // Use the existing unused QR code
            $token = $qrCode->token;
        }

        $qrCodeUrl = url($token); // Example: http://your-app.com/scan-qr/abcd1234

        return response()->json(['qr_code_url' => $qrCodeUrl]);
    }

    public function scanQrCode(Request $request)
    {
        $token = $request->input('token'); // ✅ Get token from request body
        $branch_id = $request->input('branch_id'); // ✅ Get brand ID from request body
        $qrCode = DB::table('qr_codes')
            ->where('token', $token)
            ->first();
    
        if (!$qrCode || $qrCode->used) {
            return response()->json(['error' => 'Invalid or already used QR code'], 400);
        }
    
        // Mark QR as used
        DB::table('qr_codes')
            ->where('token', $token)
            ->update(['used' => true]);
    
        return response()->json(['success' => true, 'redirect_url' => '/customer-register']);
    }
    
}
