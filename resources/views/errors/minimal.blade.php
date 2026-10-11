<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') - Sistem Pengurusan Yuran Pelajar</title>

        <style>
            body {
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background-color: #dcfce7;
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }
            .card {
                background: #fff;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
                max-width: 28rem;
                width: 100%;
                padding: 2rem;
                text-align: center;
            }
            .logo {
                height: 5rem;
                width: 5rem;
                object-fit: contain;
                margin: 0 auto 1rem;
                display: block;
            }
            .code {
                font-size: 2.5rem;
                font-weight: 700;
                color: #15803d;
                margin: 0;
            }
            .message {
                font-size: 1.125rem;
                color: #374151;
                margin-top: 0.5rem;
                margin-bottom: 1.5rem;
            }
            .btn {
                display: inline-block;
                background-color: #16a34a;
                color: #fff;
                text-decoration: none;
                font-size: 0.875rem;
                font-weight: 500;
                padding: 0.625rem 1.25rem;
                border-radius: 0.375rem;
            }
            .btn:hover {
                background-color: #15803d;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="logo">
            <p class="code">@yield('code')</p>
            <p class="message">@yield('message')</p>
            <a href="{{ url('/') }}" class="btn">Kembali ke Laman Utama</a>
        </div>
    </body>
</html>
