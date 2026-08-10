<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 40px 20px;">
    <div style="max-width: 560px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); border-radius: 16px 16px 0 0; padding: 28px; text-align: center;">
            <h1 style="color: white; font-size: 22px; font-weight: 700; margin: 0;">{{ $title }}</h1>
        </div>
        <div style="background: #1e293b; border-radius: 0 0 16px 16px; padding: 28px; border: 1px solid #334155;">
            <p style="font-size: 15px; color: #e2e8f0; margin: 0 0 16px; line-height: 1.6;">
                Hi {{ $user->name }},
            </p>
            <p style="font-size: 14px; color: #94a3b8; margin: 0 0 20px; line-height: 1.6;">
                {{ $message }}
            </p>

            @if(!empty($data))
            <div style="background: #0f172a; border-radius: 12px; padding: 16px; margin-bottom: 16px; border: 1px solid #334155;">
                @foreach($data as $key => $value)
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; @if($loop->last) margin-bottom: 0; @endif">
                    <span style="color: #64748b; font-size: 12px; text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}:</span>
                    <span style="color: #818cf8; font-weight: 600; font-size: 13px;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ config('app.url', url('/')) }}" style="display: inline-block; background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; text-decoration: none; padding: 12px 32px; border-radius: 10px; font-weight: 600; font-size: 14px;">
                    Go to Dashboard
                </a>
            </div>

            <hr style="border: none; border-top: 1px solid #334155; margin: 24px 0;">
            <p style="font-size: 12px; color: #475569; text-align: center; margin: 0;">
                You received this email because you have notifications enabled.<br>
                © {{ date('Y') }} {{ config('app.name', 'Platform') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
