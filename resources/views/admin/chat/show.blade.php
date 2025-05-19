@extends('layouts.master')

@section('content')
    <h2 style="margin-bottom: 16px;">💬 Chat dengan {{ $room->user->nama ?? 'User' }}</h2>

    <div class="chat-box">
        @forelse($room->messages as $msg)
            @php
                $isAdmin = $msg->sender_id == auth()->id();
            @endphp
            <div class="chat-message {{ $isAdmin ? 'admin' : 'user' }}">
                <div class="bubble">
    @if ($msg->media_path)
        @if ($msg->media_type === 'image')
            <img src="{{ asset('storage/' . $msg->media_path) }}" alt="Image" style="max-width: 200px; border-radius: 10px;">
        @elseif ($msg->media_type === 'video')
            <video controls style="max-width: 240px; border-radius: 10px;">
                <source src="{{ asset('storage/' . $msg->media_path) }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        @else
            <a href="{{ asset('storage/' . $msg->media_path) }}" target="_blank">Lihat File</a>
        @endif
    @elseif($msg->message)
        {{ $msg->message }}
    @endif
</div>

                <div class="timestamp">
                    {{ $msg->created_at->format('d M Y H:i') }}
                </div>
            </div>
        @empty
            <p>Belum ada pesan.</p>
        @endforelse
    </div>

    <form action="{{ route('admin.chat.send', $room->id) }}" method="POST" class="chat-form">
        @csrf
        <textarea name="message" rows="3" class="chat-textarea" placeholder="Tulis pesan ke pengguna..."></textarea>
        <button type="submit" class="chat-button">Kirim</button>
    </form>
@endsection

@push('styles')
<style>
    .chat-box {
        max-height: 450px;
        overflow-y: auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .chat-message {
        margin-bottom: 12px;
    }

    .chat-message.user {
        text-align: left;
    }

    .chat-message.admin {
        text-align: right;
    }

    .chat-message .bubble {
        display: inline-block;
        padding: 12px 16px;
        border-radius: 12px;
        max-width: 70%;
        word-wrap: break-word;
    }

    .chat-message.user .bubble {
        background-color: #e0e0e0;
        color: #000;
    }

    .chat-message.admin .bubble {
        background-color: #2c2f38;
        color: #fff;
    }

    .timestamp {
        font-size: 12px;
        color: #888;
        margin-top: 4px;
    }

    .chat-form {
        margin-top: 20px;
    }

    .chat-textarea {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        resize: none;
    }

    .chat-button {
        background-color: #2c2f38;
        color: #fff;
        padding: 10px 24px;
        border: none;
        border-radius: 6px;
        margin-top: 10px;
        cursor: pointer;
    }

    .chat-button:hover {
        background-color: #444;
    }
</style>
@endpush
