@extends('layouts.master')

@section('content')
    <h2 style="margin-bottom: 20px;">📨 Chat Rooms</h2>

    @if ($rooms->isEmpty())
        <p>Tidak ada chat room saat ini.</p>
    @else
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            @foreach ($rooms as $room)
                <div style="
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    width: 300px;
                ">
                    <h4 style="margin-bottom: 10px;">
                        <i class="fas fa-user"></i> {{ $room->user->nama ?? 'User' }}
                    </h4>
                    <p style="font-size: 14px; color: #555;">
                        ID Room: <strong>#{{ $room->id }}</strong><br>
                        Dibuat: {{ $room->created_at->format('d M Y H:i') }}
                    </p>
                    <a href="{{ route('admin.chat.show', $room->id) }}"
                       style="display: inline-block; margin-top: 10px; padding: 8px 16px;
                              background-color: #2c2f38; color: white; border-radius: 5px;
                              text-decoration: none;">
                        Buka Chat
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
