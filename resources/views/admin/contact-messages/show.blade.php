@extends('layouts.admin')

@section('title', 'Detail Pesan')

@section('content')
<div class="page-head">
    <div>
        <h1>Detail Pesan</h1>
        <p>Pesan dari {{ $message->name }} · {{ $message->created_at?->locale('id')->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" size="14px" /> Kembali</a>
        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="inline-form" onsubmit="return confirm('Hapus pesan ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash" size="13px" /> Hapus</button>
        </form>
    </div>
</div>

<div class="section-split">
    <div class="panel">
        <div class="panel-head">
            <h2>{{ $message->subject }}</h2>
            <span class="badge {{ $message->is_read ? 'badge-neutral' : 'badge-announcement' }}">{{ $message->is_read ? 'Sudah dibaca' : 'Belum dibaca' }}</span>
        </div>
        <div class="panel-body">
            <div class="article-body" style="white-space:normal">
                {!! nl2br(e($message->message)) !!}
            </div>
        </div>
    </div>

    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Pengirim</h2></div>
            <div class="panel-body">
                <div class="contact-item">
                    <div class="contact-icon"><x-icon name="user" size="18px" /></div>
                    <div>
                        <h4>Nama</h4>
                        <p>{{ $message->name }}</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><x-icon name="mail" size="18px" /></div>
                    <div>
                        <h4>Email</h4>
                        <p><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><x-icon name="phone" size="18px" /></div>
                    <div>
                        <h4>Telepon / WhatsApp</h4>
                        <p>{{ $message->phone ?: '-' }}</p>
                    </div>
                </div>
                <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}" class="btn btn-primary btn-block mt-2">
                    <x-icon name="mail" size="15px" /> Balas via Email
                </a>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <span class="muted" style="font-size:.8rem">Diterima {{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} @if($message->ip_address) · IP {{ $message->ip_address }} @endif</span>
            </div>
        </div>
    </div>
</div>
@endsection
