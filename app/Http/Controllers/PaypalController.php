<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\ProVersionModel;


class PaypalController extends Controller
{
    public function verify(Request $request)
    {
        $transactionId = $request->input('transaction_id');

        // 1. Get access token

        $user = Auth::user();

        $authResponse = Http::withBasicAuth(
            env('PAYPAL_CLIENT_ID'),
            env('PAYPAL_SECRET_KEY')
        )->asForm()->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        if (!$authResponse->ok()) {
            return response()->json(['error' => 'Failed to get PayPal token'], 500);
        }

        $accessToken = $authResponse->json()['access_token'];

        // 2. Verify order
        $orderResponse = Http::withToken($accessToken)
            ->get("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$transactionId}");

        if (!$orderResponse->ok()) {
            return response()->json(['error' => 'Failed to verify order'], 500);
        }

        $orderData = $orderResponse->json();

        if ($orderData['status'] === 'COMPLETED') {
            $user->update([
                'has_pro' => true,
                'MAX_STORAGE' => 50,
            
            ]);
    
            ProVersionModel::create([
                'dark_users_id' => $user->id,
                'expires_at' => null,
                'activated_at' => Carbon::now(),
                'is_active' => true,
            ]);
    
            return response()->json(['success' => true, 'message' => 'Payment verified']);
        }

        return response()->json(['success' => false, 'message' => 'Payment not completed'], 400);
    }
}
