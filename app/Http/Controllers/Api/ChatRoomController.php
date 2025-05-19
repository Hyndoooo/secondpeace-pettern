<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;

class ChatRoomController extends Controller
{
    // ✅ Ambil semua chat room milik user (pelanggan) atau admin
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $rooms = ChatRoom::with('user')
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $rooms = ChatRoom::with('user')
                ->where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return response()->json(['success' => true, 'rooms' => $rooms]);
    }

    // ✅ Buat chat room baru (kalau belum ada)
    public function store()
    {
        $user = Auth::user();

        $existingRoom = ChatRoom::where('user_id', $user->id)->first();
        if ($existingRoom) {
            return response()->json([
                'success' => true,
                'room' => $existingRoom,
                'message' => 'Chat room sudah ada',
            ]);
        }

        $room = ChatRoom::create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'room' => $room,
            'message' => 'Chat room berhasil dibuat',
        ]);
    }
} 
