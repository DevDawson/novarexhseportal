<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — PortalHSE</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 36px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .brand-name {
            font-size: 18px;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.02em;
        }

        .brand-name span {
            color: #3b82f6;
        }

        .error-code {
            font-size: 80px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            margin-bottom: 12px;
        }

        .error-title {
            font-size: 22px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 12px;
        }

        .error-message {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .divider {
            height: 1px;
            background: #334155;
            margin: 28px 0;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: opacity 0.15s;
            cursor: pointer;
            border: none;
        }

        .btn:hover { opacity: 0.85; }

        .btn-primary {
            background: #3b82f6;
            color: #fff;
        }

        .btn-secondary {
            background: #1e293b;
            color: #94a3b8;
            border: 1px solid #334155;
        }

        .footer {
            margin-top: 32px;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="brand-icon">🛡</div>
            <div class="brand-name">Portal<span>HSE</span></div>
        </div>

        <div class="error-code" style="color: @yield('code-color', '#3b82f6')">
            @yield('code')
        </div>

        <div class="error-title">@yield('title')</div>
        <div class="error-message">@yield('message')</div>

        <div class="divider"></div>

        <div class="actions">
            @yield('actions')
        </div>
    </div>

    <div class="footer">
        Novarex — HSE & Sustainability Consultancy · PortalHSE v2.1
    </div>
</body>
</html>
