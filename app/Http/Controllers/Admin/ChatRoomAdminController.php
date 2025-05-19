<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatRoomAdminController extends Controller
{
    public function index()
    {
        $rooms = ChatRoom::with('user')->latest()->get();
        return view('admin.chat.index', compact('rooms'));
    }

    public function show($id)
{
    $room = ChatRoom::with(['user', 'messages.sender'])->findOrFail($id);

    // Tandai semua pesan dari pelanggan sebagai sudah dibaca oleh admin
    foreach ($room->messages as $message) {
        if ($message->sender_id !== Auth::id() && !$message->is_read) {
            $message->update(['is_read' => true]);
        }
    }

    return view('admin.chat.show', compact('room'));
}


    public function send(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $room = ChatRoom::findOrFail($id);
        $room->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return redirect()->route('admin.chat.show', $id);
    }
}

