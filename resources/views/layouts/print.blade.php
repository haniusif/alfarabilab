@php($loc = app()->getLocale())
<!DOCTYPE html>
<html dir="{{ $loc === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $loc }}">
<head>
    @include('partials.head', ['title' => trim($__env->yieldContent('title'))])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .page-break { page-break-after: always; }
        }
        @media screen {
            body { background: #f1f5f9; }
        }
        .sheet {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            color: #0f172a;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        @media print {
            .sheet { box-shadow: none; margin: 0; max-width: 100%; padding: 1rem; border-radius: 0; }
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .55rem .7rem; border-bottom: 1px solid #e2e8f0; text-align: start; font-size: .9rem; }
        th { background: #f8fafc; font-weight: 700; }
        h1, h2 { color: #0f172a; }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:900px;margin:1rem auto;display:flex;justify-content:flex-end;gap:.5rem;padding:0 1rem;">
        <button onclick="window.print()" style="background:#0d9488;color:white;border:0;border-radius:8px;padding:.5rem 1rem;font-weight:700;cursor:pointer;">
            🖨️ {{ __('Print') }}
        </button>
        <button onclick="history.back()" style="background:#e2e8f0;color:#0f172a;border:0;border-radius:8px;padding:.5rem 1rem;font-weight:700;cursor:pointer;">
            {{ __('Back') }}
        </button>
    </div>

    <div class="sheet">
        @yield('content')
    </div>
</body>
</html>
