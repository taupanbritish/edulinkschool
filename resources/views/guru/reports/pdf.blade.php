<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 26px 28px 34px; }
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1b2a3a; }
        .header { margin-bottom: 12px; }
        table.kop { width: 100%; border-collapse: collapse; }
        table.kop td { padding: 0; vertical-align: middle; }
        td.kop-logo { width: 64px; }
        td.kop-logo img { width: 56px; height: 56px; display: block; }
        .kop-logo-fallback { width: 56px; height: 56px; line-height: 56px; text-align: center; background: #17496e; color: #ffffff; font-size: 24px; font-weight: bold; }
        td.kop-identity { padding-left: 12px; }
        .kop-school { font-size: 16px; font-weight: bold; color: #17496e; letter-spacing: .3px; line-height: 1.25; }
        .kop-line { margin-top: 3px; font-size: 8.5px; color: #5a6b7c; line-height: 1.35; }
        .kop-rule { margin-top: 8px; border-top: 3px solid #17496e; border-bottom: 1px solid #9fb4c7; height: 2px; }
        .report-title { margin: 10px 0 14px; padding: 5px 8px; background: #e8eff5; border-left: 4px solid #17496e; text-align: center; font-size: 10.5px; font-weight: bold; letter-spacing: .9px; color: #123c5a; text-transform: uppercase; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.meta th { text-align: left; width: 95px; font-weight: bold; color: #5a6b7c; padding: 1px 4px 1px 0; font-size: 9px; }
        table.meta td { padding: 1px 4px 1px 0; font-size: 9px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #17496e; color: #fff; font-size: 8.5px; padding: 5px 4px; border: 1px solid #17496e; }
        table.data td { border: 1px solid #c9d6e2; padding: 4px; font-size: 9px; vertical-align: middle; }
        table.data tbody tr:nth-child(even) td { background: #f4f8fb; }
        table.data tfoot td { font-weight: bold; background: #e8eff5; }
        .num { text-align: right; }
        .center { text-align: center; }
        .muted { color: #5a6b7c; }
        .signature { margin-top: 26px; width: 100%; }
        .signature .box { float: right; width: 220px; text-align: center; }
        .signature .space { height: 48px; }
        .signature .role { font-size: 9px; color: #5a6b7c; }
        .signature .name { font-weight: bold; border-top: 1px solid #1b2a3a; margin-top: 2px; padding-top: 3px; }
        .clear { clear: both; }
    </style>
</head>
<body>
@php
    $logo = \App\Support\School::logoDataUri();
    $contactParts = array_filter([
        filled($school['npsn'] ?? null) ? 'NPSN '.$school['npsn'] : null,
        filled($school['email'] ?? null) ? $school['email'] : null,
        filled($school['phone'] ?? null) ? 'Telp. '.$school['phone'] : null,
    ]);
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($school['school_short'] ?? $school['school_name'] ?? 'Sekolah', 0, 1));
@endphp
<div class="header">
    <table class="kop">
        <tr>
            <td class="kop-logo">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo sekolah">
                @else
                    <div class="kop-logo-fallback">{{ $initial }}</div>
                @endif
            </td>
            <td class="kop-identity">
                <div class="kop-school">{{ $school['school_name'] ?? 'Sekolah' }}</div>
                @if(filled($school['address'] ?? null))
                    <div class="kop-line">{{ $school['address'] }}</div>
                @endif
                @if($contactParts !== [])
                    <div class="kop-line">{{ implode(' • ', $contactParts) }}</div>
                @endif
            </td>
        </tr>
    </table>
    <div class="kop-rule"></div>
    <div class="report-title">{{ $title }}</div>
</div>

<table class="meta">
    @foreach($meta as $label => $value)
        <tr><th>{{ $label }}</th><td>{{ $value }}</td></tr>
    @endforeach
</table>

@yield('report')

<div class="signature">
    <div class="box">
        <div class="muted" style="font-size:9px">{{ now()->locale('id')->translatedFormat('d F Y') }}</div>
        <div class="space"></div>
        <div class="role">{{ $signatureRole }}</div>
        <div class="name">{{ $signatureName }}</div>
    </div>
    <div class="clear"></div>
</div>
</body>
</html>
