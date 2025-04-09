<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateResetCode;
use App\Mail\PasswordResetMail;
use App\Models\DarkUsers;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


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
    
        // Mail::to($user->email)->send(new PasswordResetMail($code));
        Mail::to($user->email)->send(new PasswordResetMail($code, $user->email));

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
    
        // Update the verified flag properly
        $resetEntry->verified = true;
        $resetEntry->save();
    
        return response()->json(['message' => 'Code verified successfully']);
    }
    

    public function resetPassword(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:dark_users,email',
            'password' => 'required|min:6|confirmed'
        ]);
    
        $resetEntry = PasswordReset::where('email', $request->email)
                                   ->where('verified', true)
                                   ->first();
    
        if (!$resetEntry) {
            return response()->json(['message' => 'Reset code not verified or expired'], 400);
        }
    
        // Update the password
        $user = DarkUsers::where('email', $request->email)->first();
        $user->update(['password' => bcrypt($request->password)]);
    
        // Delete the reset entry from the table
        $resetEntry->delete();
    
        return response()->json(['message' => "Password changed for {$request->email}"]);
    }
    public function track($code, $email)
    {
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'type' => 'email_opened',
            'code' => $code,
            'email' => $email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        Storage::append('password_reset_tracking.log', json_encode($logData));

        return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='))
            ->header('Content-Type', 'image/png');
    }

    public function verify($code, $email)
    {
        // Log the click
        $clickData = [
            'timestamp' => now()->toDateTimeString(),
            'type' => 'link_clicked',
            'code' => $code,
            'email' => $email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];
        Storage::append('password_reset_tracking.log', json_encode($clickData));

        // Lookup and verify
        $resetEntry = PasswordReset::where('email', $email)
                                   ->where('code', $code)
                                   ->first();

        if (!$resetEntry) {
            return response()->view('errors.404', [], 404); // optional custom 404
        }

        $resetEntry->verified = true;
        $resetEntry->save();

        return view('password-verified');
    }

    public function checkVerificationStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:dark_users,email'
        ]);

        $resetEntry = PasswordReset::where('email', $request->email)->first();

        return response()->json([
            'verified' => $resetEntry && $resetEntry->verified,
            'email' => $request->email
        ]);
    }
}
