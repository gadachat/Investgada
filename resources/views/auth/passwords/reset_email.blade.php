<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset — {{ config('app.name', 'APTrades') }}</title>
</head>
<body style="font-family: 'Inter', Arial, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 40px 20px;">
    <div style="max-width: 480px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 16px; overflow: hidden;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #6366f1, #7c3aed); padding: 28px 32px; text-align: center;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.15); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 22px;">🔑</div>
            <h1 style="color: white; margin: 0; font-size: 22px; font-weight: 700;">Password Reset Request</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px;">{{ config('app.name', 'APTrades') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 28px 32px;">
            <p style="font-size: 14px; line-height: 1.6; color: #e2e8f0;">
                Hello <strong>{{ $user->name }}</strong>,
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #94a3b8;">
                We received a request to reset your password. Click the button below to choose a new password:
            </p>

            <!-- CTA Button -->
            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ url('/reset-password/' . $token) }}?email={{ urlencode($user->email) }}"
                   style="display: inline-block; background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; font-weight: 600; font-size: 14px; text-decoration: none; padding: 12px 32px; border-radius: 10px;">
                    Reset Password
                </a>
            </div>

            <p style="font-size: 12px; color: #64748b; line-height: 1.6;">
                If the button doesn't work, copy and paste this link into your browser:<br>
                <a href="{{ url('/reset-password/' . $token) }}?email={{ urlencode($user->email) }}" style="color: #6366f1; word-break: break-all;">{{ url('/reset-password/' . $token) }}?email={{ urlencode($user->email) }}</a>
            </p>

            <hr style="border: none; border-top: 1px solid #334155; margin: 24px 0;">

            <p style="font-size: 12px; color: #64748b; line-height: 1.6;">
                <strong>Security note:</strong> This link expires in 60 minutes. If you didn't request a password reset, you can safely ignore this email — your password will not be changed.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #111827; padding: 16px 32px; text-align: center;">
            <p style="font-size: 11px; color: #475569; margin: 0;">
                © {{ date('Y') }} {{ config('app.name', 'APTrades') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
