<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — APTrades</title>
    <meta name="theme-color" content="#6366f1">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            text-align: center;
            max-width: 400px;
        }
        .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: white;
            margin: 0 auto 24px;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.3);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .retry-btn {
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .retry-btn:active {
            transform: scale(0.95);
        }
        .wifi-off {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">CC</div>
        <div class="wifi-off"><i class="fas fa-wifi"></i>?</div>
        <h1>You're Offline</h1>
        <p>It looks like you've lost your internet connection. Check your network and try again.</p>
        <button class="retry-btn" onclick="window.location.reload()">Try Again</button>
    </style>
    </div>
    <script>
        // Auto-retry when connection returns
        window.addEventListener('online', () => {
            window.location.reload();
        });
    </script>
</body>
</html>
