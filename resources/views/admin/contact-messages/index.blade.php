@extends('layouts.admin')

@section('title', 'Pesan & Saran')

@section('content')
<div class="page-head">
    <div>
        <h1>Pesan &amp; Saran</h1>
        <p>Pesan yang dikirim pengunjung melalui formulir kontak website.</p>
    </div>
    <div class="page-actions">
        @if($unreadCount > 0)
            <span class="badge badge-announcement">{{ $unreadCount }} belum dibaca</span>
        @endif
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="filter-bar">
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama, email, atau subjek…">
            <select name="status" class="select">
                <option value="">Semua pesan</option>
                <option value="unread" @selected(request('status') === 'unread')>Belum dibaca</option>
                <option value="read" @selected(request('status') === 'read')>Sudah dibaca</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm"><x-icon name="search" size="14px" /> Cari</button>
        </form>
        <span class="muted" style="font-size:.8rem">{{ $messages->total() }} pesan</span>
    </div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Subjek</th><th>Pengirim</th><th>Tanggal</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $message->subject }}</span>
                                <span class="cell-sub">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</span>
                            </td>
                            <td>
                                <span class="cell-main">{{ $message->name }}</span>
                                <span class="cell-sub">{{ $message->email }}</span>
                            </td>
                            <td>{{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                            <td><span class="badge {{ $message->is_read ? 'badge-neutral' : 'badge-announcement' }}">{{ $message->is_read ? 'Sudah dibaca' : 'Belum dibaca' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="btn btn-sm btn-outline"><x-icon name="arrow-right" size="13px" /> Lihat</a>
                                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="inline-form" onsubmit="return confirm('Hapus pesan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="13px" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">Belum ada pesan masuk.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination', ['paginator' => $messages])
    </div>
</div>
@endsection
