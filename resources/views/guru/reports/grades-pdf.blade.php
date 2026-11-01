@extends('guru.reports.pdf')

@section('report')
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>Nama Siswa</th>
                <th style="width:58px">NIS</th>
                @foreach($types as $label)
                    <th style="width:42px">{{ $label }}</th>
                @endforeach
                <th style="width:50px">Rata-rata</th>
                <th style="width:120px">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="center">{{ $row['no'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="center">{{ $row['nis'] }}</td>
                    @foreach($types as $type => $label)
                        <td class="center">{{ $row['scores'][$type] === null ? '-' : number_format($row['scores'][$type], 1, ',', '.') }}</td>
                    @endforeach
                    <td class="center"><strong>{{ $row['average'] === null ? '-' : number_format($row['average'], 1, ',', '.') }}</strong></td>
                    <td>{{ $row['notes'] ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center muted">Belum ada nilai untuk penugasan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
