<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 26px 28px 34px; }
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1b2a3a; }
        .header { border-bottom: 2px solid #17496e; padding-bottom: 8px; margin-bottom: 10px; }
        .header h1 { font-size: 15px; margin: 0 0 2px; color: #17496e; }
        .header p { margin: 0; font-size: 9px; color: #5a6b7c; }
        .header h2 { font-size: 12px; margin: 8px 0 0; text-align: center; letter-spacing: .4px; }
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
<div class="header">
    <h1>{{ $school['school_name'] ?? 'Sekolah' }}</h1>
    @if(! empty($school['school_address']))
        <p>{{ $school['school_address'] }}</p>
    @endif
    <h2>{{ $title }}</h2>
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
