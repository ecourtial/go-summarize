<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
        <title>App Name - @yield('title')</title>
    </head>
    <body style="margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;">
        {{-- Safe area wrapper --}}
        <div class="safe-area">
            <main class="page">
                @yield('content')
            </main>
        </div>
        <style>
            html, body {
                height: 100%;
                margin: 0;
            }

            body {
                font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
                background: #f5f6f8;
                overflow: hidden; /* <- critical: body no longer scrolls */
            }
            /* iOS notch safe areas */
            .safe-area {
                padding-top: env(safe-area-inset-top);
                padding-right: env(safe-area-inset-right);
                padding-bottom: env(safe-area-inset-bottom);
                padding-left: env(safe-area-inset-left);
                min-height: 100vh;
                background: #f5f6f8;
            }

            /* Page container */
            .page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                box-sizing: border-box;
            }
        </style>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
