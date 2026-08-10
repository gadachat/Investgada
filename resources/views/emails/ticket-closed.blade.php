<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 40px 20px;">
    <div style="max-width: 560px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); border-radius: 16px 16px 0 0; padding: 28px; text-align: center;">
            <h1 style="color: white; font-size: 22px; font-weight: 700; margin: 0;">Ticket Closed</h1>
        </div>
        <div style="background: #1e293b; border-radius: 0 0 16px 16px; padding: 28px; border: 1px solid #334155;">
            <p style="font-size: 14px; color: #94a3b8; margin: 0 0 16px;">Your support ticket has been closed. If you need further assistance, feel free to create a new ticket.</p>
            
            <div style="background: #0f172a; border-radius: 12px; padding: 16px; margin-bottom: 16px; border: 1px solid #334155;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #64748b; font-size: 12px;">Ticket Number:</span>
                    <span style="color: #818cf8; font-weight: 600; font-size: 13px; font-family: monospace;">{{ $ticket->ticket_number }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 12px;">Subject:</span>
                    <span style="color: #e2e8f0; font-size: 13px; font-weight: 600;">{{ $ticket->subject }}</span>
                </div>
            </div>
            
            <p style="font-size: 13px; color: #64748b; margin: 16px 0 0; text-align: center;">Thank you for using our support service.</p>
        </div>
    </div>
</body>
</html>
