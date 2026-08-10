<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'We could not find an account with that email.');
        }

        // Generate token
        $token = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Send reset email
        Mail::send('auth.passwords.reset_email', [
            'user'  => $user,
            'token' => $token,
        ], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Password Reset Request');
        });

        return back()->with('success', 'Password reset link has been sent to your email.');
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email'                 => ['required', 'email'],
            'token'                 => ['required', 'string'],
            'password'             => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->with('error', 'Invalid or expired reset token.');
        }

        // Check token expiry (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            return back()->with('error', 'This reset link has expired. Please request a new one.');
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => $request->password]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset. You can now log in.');
    }
}
