<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 40px 20px;">
    <div style="max-width: 560px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); border-radius: 16px 16px 0 0; padding: 28px; text-align: center;">
            <h1 style="color: white; font-size: 22px; font-weight: 700; margin: 0;">{{ $isAdminReply ? 'Support Team Replied' : 'New Reply on Your Ticket' }}</h1>
        </div>
        <div style="background: #1e293b; border-radius: 0 0 16px 16px; padding: 28px; border: 1px solid #334155;">
            <p style="font-size: 14px; color: #94a3b8; margin: 0 0 16px;">
                {{ $isAdminReply ? 'The support team has responded to your ticket.' : 'A new message has been added to your ticket.' }}
            </p>
            
            <div style="background: #0f172a; border-radius: 12px; padding: 16px; margin-bottom: 16px; border: 1px solid #334155;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Ticket: {{ $ticket->ticket_number }}</div>
                <div style="font-size: 14px; color: #e2e8f0; font-weight: 600; margin-bottom: 12px;">{{ $ticket->subject }}</div>
                <div style="border-top: 1px solid #334155; padding-top: 12px;">
                    <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Reply from {{ $message->user?->name ?? 'Support Team' }}:</div>
                    <div style="font-size: 13px; color: #cbd5e1; line-height: 1.6; white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($message->message, 300) }}</div>
                </div>
            </div>
            
            <p style="font-size: 13px; color: #64748b; margin: 16px 0 0; text-align: center;">Reply from your dashboard to continue the conversation.</p>
        </div>
    </div>
</body>
</html>
