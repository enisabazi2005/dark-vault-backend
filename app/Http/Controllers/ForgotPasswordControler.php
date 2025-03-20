<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateResetCode;
use App\Mail\PasswordResetMail;
use App\Models\DarkUsers;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class ForgotPasswordControler extends Controller
{
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:dark_users,email'
        ]);
    
        $user = DarkUsers::where('email', $request->email)->first();
        $code = rand(100000, 999999);
    
        PasswordReset::updateOrCreate(
            ['dark_users_id' => $user->id, 'email' => $user->email],
            ['code' => $code, 'created_at' => now()]
        );
    
        Mail::to($user->email)->send(new PasswordResetMail($code));
        // dispatch(new UpdateResetCode($user->email));

        return response()->json(['message' => 'Reset code sent to email']);
    }

    public function verifyResetCode(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:dark_users,email',
            'code' => 'required|digits:6'
        ]);
    
        $resetEntry = PasswordReset::where('email', $request->email)
                                   ->where('code', $request->code)
                                   ->first();
    
        if (!$resetEntry) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }
    
        return response()->json(['message' => 'Code verified successfully']);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:dark_users,email',
            'code' => 'required|digits:6',
            'password' => 'required|min:6|confirmed'
        ]);
    
        $resetEntry = PasswordReset::where('email', $request->email)
                                   ->where('code', $request->code)
                                   ->first();
    
        if (!$resetEntry) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }
    
        $user = DarkUsers::where('email', $request->email)->first();
        $user->update(['password' => bcrypt($request->password)]);
    
        $resetEntry->delete();
    
        return response()->json(['message' => 'Password reset successfully']);
    }
}
