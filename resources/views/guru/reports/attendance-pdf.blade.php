@extends('guru.reports.pdf')

@section('report')
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>Nama Siswa</th>
                <th style="width:58px">NIS</th>
                @foreach($statuses as $label)
                    <th style="width:40px">{{ $label }}</th>
                @endforeach
                <th style="width:42px">Total</th>
                <th style="width:52px">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="center">{{ $row['no'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="center">{{ $row['nis'] }}</td>
                    @foreach($row['counts'] as $count)
                        <td class="center">{{ $count }}</td>
                    @endforeach
                    <td class="center">{{ $row['total'] }}</td>
                    <td class="center">{{ $row['percentage'] === null ? '-' : number_format($row['percentage'], 1, ',', '.').'%' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center muted">Belum ada siswa aktif pada kelas ini.</td></tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="center">Jumlah</td>
                    @foreach($totals as $count)
                        <td class="center">{{ $count }}</td>
                    @endforeach
                    <td class="center">{{ array_sum($totals) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
