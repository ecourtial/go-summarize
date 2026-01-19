<?php
declare(strict_types=1);
?>

@extends('layouts.app')

@section('title', 'Review')

@section('content')
    <div class="shell">

        {{-- Top bar --}}
        <div class="topbar">
            <div class="title">Review zone</div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link">Log out</button>
            </form>
        </div>

        {{-- Errors --}}
        @if (session('error'))
            <div class="alert">{{ session('error') }}</div>
        @endif

        @if (!empty($error))
            <div class="alert">{{ $error }}</div>
        @endif

        {{-- Empty state --}}
        @if (!$item)
            <div class="empty">
                <div class="empty-title">No pending pages</div>
                <div class="empty-sub">
                    There is nothing to review right now.
                </div>

                <a href="{{ route('review') }}" class="btn secondary">
                    Refresh
                </a>
            </div>

            {{-- Review card --}}
        @else
            <div class="card">
                <div class="meta">
                    <div class="pill">{{ $item->feedName }}</div>
                    <div class="id">{{ $item->publishedAt->format('Y-m-d') }} (#{{ $item->id }})</div>
                </div>

                <div class="label">
                    {!! $item->title !!}
                </div>

                <div class="url">
                    {!! $item->description !!}
                </div>

                <a
                    href="{{ $item->url }}"
                    target="_blank"
                    rel="noreferrer"
                    class="open"
                >
                    Open link →
                </a>
            </div>

            {{-- Actions --}}
            <div class="actions">
                <form onsubmit="hideForm()" method="POST" action="{{ route('review.discard') }}" class="action">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <button type="submit" class="btn secondary btn-compact" id="discardButton">
                        ❌ Discard
                    </button>
                </form>

                <form onsubmit="hideForm()" method="POST" action="{{ route('review.manual') }}" class="action">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <button type="submit" class="btn secondary btn-compact" id="toReadButton">
                        👀 To read
                    </button>
                </form>

                <form onsubmit="hideForm()" method="POST" action="{{ route('review.to-summarize') }}" class="action">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <button type="submit" class="btn secondary btn-compact" id="aiButton">
                        ✅️ Well...
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- JS --}}
    <script>
        function hideForm() {
            // @TODO to refacto using a class selector
            document.getElementById("discardButton").textContent = "⏳";
            document.getElementById("discardButton").disabled = true;
            document.getElementById("toReadButton").textContent = "⏳";
            document.getElementById("toReadButton").disabled = true;
            document.getElementById("aiButton").textContent = "⏳";
            document.getElementById("aiButton").disabled = true;
        }
    </script>

    {{-- Styles --}}
    <style>
        .pleaseWait {
            justify-content: center;
            gap: 12px;
            display: none;
        }

        .shell {
            width: 100%;
            max-width: 520px;
            display: grid;
            gap: 16px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 2px;
        }

        .title {
            font-size: 18px;
            font-weight: 800;
        }

        .link {
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 700;
            padding: 10px 8px;
            cursor: pointer;
            color: #111;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 12px;
            background: #fff1f2;
            color: #9f1239;
            font-size: 14px;
        }

        .card {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            display: grid;
            gap: 12px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pill {
            font-size: 12px;
            font-weight: 800;
            background: #f1f5f9;
            padding: 6px 10px;
            border-radius: 999px;
        }

        .id {
            font-size: 12px;
            color: #6b7280;
        }

        .label {
            font-size: 12px;
            font-weight: 800;
            color: #6b7280;
            text-transform: uppercase;
        }

        .url {
            font-size: 14px;
            color: #111;
            word-break: break-all;
            line-height: 1.4;
        }

        .open {
            font-weight: 800;
            text-decoration: none;
            color: #111;
            padding-top: 6px;
        }

        /* Actions row: centered, compact buttons */
        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .action { margin: 0; }

        .btn {
            border: 0;
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        .btn:active { transform: scale(0.99); }

        .btn-compact {
            width: 110px;     /* reduced width */
            max-width: 38vw;  /* prevents overflow on small screens */
        }

        .secondary { background: #fff; color: #111; border: 1px solid #d7dbe0; }

        .empty {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
            display: grid;
            gap: 10px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 800;
        }

        .empty-sub {
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }
    </style>
@endsection
