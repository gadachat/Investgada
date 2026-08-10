<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') — {{ config('app.name', 'Investment Platform') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --border: #334155;
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 480px;
        }

        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-header h3 {
            font-weight: 700;
            margin-bottom: 4px;
            color: #fff;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--text-muted);
        }

        .form-control {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .form-control:focus {
            background: var(--bg);
            border-color: var(--primary);
            color: var(--text);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-row .col {
            flex: 1;
        }

        .form-row-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-check a, .form-row-between a {
            color: var(--primary);
            text-decoration: none;
        }

        .password-wrap {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-block {
            width: 100%;
        }

        .alert {
            border-radius: 8px;
            font-size: 13px;
            padding: 10px 14px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        ul {
            margin: 0;
            padding-left: 18px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 576px) {
            body { padding: 12px; }
            .auth-card { padding: 28px 20px; border-radius: 12px; }
            .auth-header h3 { font-size: 20px; }
            .auth-header p { font-size: 12px; }
            .form-row { flex-direction: column; gap: 0; }
            .form-row .col { flex: 1 1 100%; }
            .form-control { font-size: 13px; padding: 10px 12px; }
            .btn-primary { padding: 11px; font-size: 13px; }
            .auth-footer { font-size: 13px; }
        }

        @media (max-width: 380px) {
            .auth-card { padding: 20px 14px; }
            .form-control { font-size: 12px; }
        }
    </style>
@include('partials._pwa')
@include('partials._mobile-styles')
</head>
<body>
    @yield('content')
</body>
</html>
