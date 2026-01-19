@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="card">
        <div class="header">
            <div class="logo">📰</div>
            <h1>Sign in</h1>
            <p class="subtitle">Connect to your server to start reviewing feeds.</p>
        </div>

        {{-- You can show errors here later (session('error') / $errors) --}}
        @if (session('error'))
            <div class="alert">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="form">
            @csrf

            <label class="field">
                <span>Server URL</span>
                <input
                    name="server_url"
                    type="url"
                    inputmode="url"
                    placeholder="https://api.example.com"
                    autocomplete="url"
                    autocapitalize="none"
                    autocorrect="off"
                    spellcheck="false"
                    required
                    value="http://localhost"
                >
            </label>

            <label class="field">
                <span>Email</span>
                <input
                    name="email"
                    type="email"
                    placeholder="your@email.com"
                    autocomplete="email"
                    autocapitalize="none"
                    autocorrect="off"
                    spellcheck="false"
                    required
                    value="foo@bar.com"
                >
            </label>

            <label class="field">
                <span>Password</span>
                <input
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </label>

            <button class="btn" type="submit">
                Sign in
            </button>
        </form>
    </div>

    <style>
        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            box-sizing: border-box;
        }

        .header { text-align: center; margin-bottom: 14px; }
        .logo { font-size: 34px; margin-bottom: 8px; }
        h1 { margin: 0; font-size: 22px; }
        .subtitle { margin: 8px 0 0; color: #666; font-size: 14px; line-height: 1.4; }

        .alert {
            margin: 14px 0;
            padding: 10px 12px;
            border-radius: 12px;
            background: #fff1f2;
            color: #9f1239;
            font-size: 14px;
        }

        .form { margin-top: 18px; display: grid; gap: 14px; }

        .field { display: grid; gap: 6px; }
        .field span { font-size: 13px; color: #333; font-weight: 600; }

        input {
            width: 100%;
            border: 1px solid #d7dbe0;
            border-radius: 14px;
            padding: 14px 14px;
            font-size: 16px; /* 16px prevents iOS zoom */
            outline: none;
            box-sizing: border-box;
            background: #fff;
        }
        input:focus {
            border-color: #111;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.08);
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 16px;
            font-weight: 700;
            background: #111;
            color: #fff;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .btn:active { transform: scale(0.99); }

        .hint {
            margin: 4px 0 0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 8px;
        }
    </style>
@endsection

