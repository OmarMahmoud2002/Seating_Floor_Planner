<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Page Expired') }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7f7f8;
            color: #1f2933;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            width: min(92vw, 420px);
            padding: 32px;
            text-align: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
        }

        p {
            margin: 0 0 24px;
            line-height: 1.6;
            color: #52606d;
        }

        a {
            display: inline-block;
            padding: 10px 18px;
            color: #fff;
            background: #1f2933;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="page">
        <h1>{{ __('Page Expired') }}</h1>
        <p>{{ __('Please refresh the page and try again.') }}</p>
        <a href="{{ url()->previous() ?: url('/') }}">{{ __('Refresh') }}</a>
    </main>
</body>
</html>
