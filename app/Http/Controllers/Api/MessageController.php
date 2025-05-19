<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    // ✅ Ambil semua pesan dari chat room tertentu & tandai sebagai 'read'
    public function index($chatRoomId)
    {
        $user = Auth::user();

        // Tandai pesan lawan bicara sebagai sudah dibaca
        Message::where('chat_room_id', $chatRoomId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $chatRoom = ChatRoom::with('messages.sender')->findOrFail($chatRoomId);

        return response()->json([
            'success' => true,
            'messages' => $chatRoom->messages,
        ]);
    }

    // ✅ Kirim pesan teks
    public function store(Request $request, $chatRoomId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        $message = Message::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->fresh(), // supaya relasi sender ikut
        ], 201);
    }

    // ✅ Upload media (gambar/video/file)
    public function upload(Request $request, $chatRoomId)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        $file = $request->file('file');
        $type = $file->getMimeType();
        $path = $file->store('chat_media', 'public');

        $mediaType = str_contains($type, 'image') ? 'image' :
                    (str_contains($type, 'video') ? 'video' : 'file');

        $message = Message::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => Auth::id(),
            'media_path' => $path,
            'media_type' => $mediaType,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->fresh(),
        ], 201);
    }

    public function markAsRead($chatRoomId)
{
    $chatRoom = ChatRoom::with('messages')->findOrFail($chatRoomId);

    foreach ($chatRoom->messages as $message) {
        if ($message->sender_id !== auth()->id() && !$message->is_read) {
            $message->update(['is_read' => true]);
        }
    }

    return response()->json(['success' => true]);
}

public function hasUnread()
{
    $user = Auth::user();

    $hasUnread = Message::whereHas('chatRoom', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->where('sender_id', '!=', $user->id)
    ->where('is_read', false)
    ->exists();

    return response()->json([
        'success' => true,
        'has_unread' => $hasUnread,
    ]);
}



}
