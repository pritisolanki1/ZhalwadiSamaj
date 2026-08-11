<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>@yield('title') - Zalawadi Sai Suthar</title>
    <style>
        :root {
            --primary: #1a3a5f;
            --accent: #b8860b;
            --text: #333333;
            --muted: #666666;
            --bg: #f7f7f5;
            --card: #ffffff;
            --border: #e5e2da;
            --danger-bg: #f9eaea;
            --danger-border: #e5c4c4;
            --danger-text: #7a1f1a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.65;
        }

        .header {
            background: var(--primary);
            color: #ffffff;
            padding: 24px 20px;
        }

        .header-inner {
            max-width: 880px;
            margin: 0 auto;
        }

        .header .app {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 6px;
        }

        .header h1 {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .container {
            max-width: 880px;
            margin: 0 auto;
            padding: 28px 20px 48px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 26px 20px;
            margin-bottom: 24px;
        }

        h2 {
            font-size: 1.2rem;
            color: var(--primary);
            margin: 30px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border);
        }

        h3 {
            font-size: 1.05rem;
            color: var(--primary);
            margin: 22px 0 8px;
        }

        p {
            margin-bottom: 12px;
        }

        ul,
        ol {
            margin: 0 0 14px 22px;
        }

        li {
            margin-bottom: 6px;
        }

        .lead {
            font-size: 1.05rem;
        }

        .meta {
            color: var(--muted);
            font-size: 0.92rem;
            margin-bottom: 16px;
        }

        .notice {
            background: #fdf6e9;
            border: 1px solid #e8d9b5;
            border-left: 5px solid var(--accent);
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .notice p {
            margin: 0;
        }

        .prohibit {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-left: 5px solid #b3261e;
            padding: 16px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .prohibit p {
            margin: 0;
            font-weight: 700;
            color: var(--danger-text);
            font-size: 1.05rem;
        }

        .contact-box {
            background: #eef4fa;
            border: 1px solid #cfe0ef;
            border-radius: 6px;
            padding: 16px 18px;
            margin-bottom: 20px;
        }

        .contact-box p {
            margin: 0;
        }

        .contact-box .label {
            display: block;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .footer {
            border-top: 1px solid var(--border);
            padding: 20px;
            text-align: center;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .footer p {
            margin: 0;
        }

        a {
            color: var(--primary);
        }

        @media (min-width: 640px) {
            .header {
                padding: 36px 24px;
            }

            .header h1 {
                font-size: 1.6rem;
            }

            .card {
                padding: 36px 44px;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="header-inner">
        <span class="app">Zalawadi Sai Suthar</span>
        <h1>@yield('title')</h1>
    </div>
</header>
<main class="container">
    @yield('content')
</main>
<footer class="footer">
    <p>&copy; {{ date('Y') }} Zalawadi Sai Suthar. All rights reserved.</p>
</footer>
</body>
</html>
