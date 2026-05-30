@extends('layouts.print')
@section('title', __('Reports'))

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;border-bottom:2px solid #0f766e;padding-bottom:.75rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;margin:0;">{{ config('app.name') }}</h1>
        <div style="color:#64748b;font-size:.85rem;">{{ __('Doctor') }} — {{ __('Reports') }}</div>
    </div>
    <div style="text-align:end;font-size:.85rem;color:#475569;">
        <div><strong>{{ __('Period') }}:</strong> <span dir="ltr">{{ $from->format('Y-m-d') }} → {{ $to->format('Y-m-d') }}</span></div>
        <div><strong>{{ __('Generated') }}:</strong> <span dir="ltr">{{ now()->format('Y-m-d H:i') }}</span></div>
        <div><strong>{{ __('By') }}:</strong> {{ auth()->user()->name }}</div>
    </div>
</div>

<h2 style="font-size:1.05rem;margin:1rem 0 .5rem;">{{ __('Summary') }}</h2>
<table>
    <tbody>
        <tr><th style="width:50%">{{ __('Incoming files') }}</th><td>{{ $totalIncoming }}</td></tr>
        <tr><th>{{ __('Explained in period') }}</th><td>{{ $totalExplained }}</td></tr>
        <tr><th>{{ __('Avg. turnaround') }}</th><td>{{ $avgTurnaroundHours !== null ? $avgTurnaroundHours.' '.__('h') : '—' }}</td></tr>
    </tbody>
</table>

<h2 style="font-size:1.05rem;margin:1.25rem 0 .5rem;">{{ __('Files by status') }}</h2>
<table>
    <thead><tr><th>{{ __('Status') }}</th><th style="text-align:end;">{{ __('Files') }}</th></tr></thead>
    <tbody>
    @foreach ($statusCounts as $row)
        <tr><td>{{ $row['status']->label() }}</td><td style="text-align:end;">{{ $row['count'] }}</td></tr>
    @endforeach
    </tbody>
</table>

@if ($files->isNotEmpty())
    <h2 style="font-size:1.05rem;margin:1.25rem 0 .5rem;">{{ __('Files in this period') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('Patient') }}</th>
                <th>{{ __('Patient Ref.') }}</th>
                <th>{{ __('Test') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Explained on') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($files as $f)
            <tr>
                <td>{{ $f->patient->name ?? '—' }}</td>
                <td><span dir="ltr">{{ $f->patient_ref_no ?? '—' }}</span></td>
                <td>{{ $f->test_name ?? '—' }}</td>
                <td>{{ $f->status->label() }}</td>
                <td><span dir="ltr">{{ $f->created_at->format('Y-m-d H:i') }}</span></td>
                <td><span dir="ltr">{{ $f->explained_at?->format('Y-m-d H:i') ?? '—' }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<div style="margin-top:2rem;font-size:.75rem;color:#94a3b8;text-align:center;">
    {{ config('app.name') }} — {{ __('Lab–insurance integration system') }}
</div>
@endsection
