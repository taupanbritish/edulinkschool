@extends('guru.reports.pdf')

@section('report')
    <table class="data">
        <thead>
            <tr>
                <th style="width:22px">No</th>
                <th style="width:52px">Tanggal</th>
                <th style="width:60px">Waktu</th>
                <th style="width:54px">Kelas</th>
                <th style="width:80px">Mata Pelajaran</th>
                <th style="width:90px">Guru</th>
                <th>Materi &amp; Kegiatan</th>
                <th style="width:90px">Tugas</th>
                <th style="width:48px">H / S / I / A</th>
                <th style="width:38px">Lampiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="center">{{ $row['no'] }}</td>
                    <td class="center">{{ $row['date'] }}<br><span class="muted">{{ $row['day'] }}</span></td>
                    <td class="center">{{ $row['time'] }}</td>
                    <td>{{ $row['classroom'] }}</td>
                    <td>{{ $row['subject'] }}</td>
                    <td>{{ $row['teacher'] }}</td>
                    <td>
                        <strong>{{ $row['topic'] }}</strong><br>
                        <span class="muted">{{ \Illuminate\Support\Str::limit($row['activity'], 160) }}</span>
                        @if($row['notes'] !== '-')
                            <br><span class="muted">Catatan: {{ \Illuminate\Support\Str::limit($row['notes'], 80) }}</span>
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($row['assignment'], 90) }}</td>
                    <td class="center">{{ $row['hadir'] }} / {{ $row['sakit'] }} / {{ $row['izin'] }} / {{ $row['alpa'] }}</td>
                    <td class="center">{{ $row['attachments'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="center muted">Tidak ada jurnal pada periode dan filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
