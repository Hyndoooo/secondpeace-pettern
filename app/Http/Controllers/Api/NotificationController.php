<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Ambil semua notifikasi user
    public function index()
    {
        $user = Auth::user();

        $notifications = Notification::with('pesanan') // pastikan eager loading pesanan
            ->where('id_user', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                $data = null;

                // pastikan status_pesanan diakses dengan benar
                if ($n->type === 'pesanan' && $n->id_ref) {
                    $data = [
                        'id_pesanan' => $n->id_ref,
                        'status_pesanan' => optional($n->pesanan)->status_pesanan ?? 'Pesanan Diproses',
                    ];
                }

                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'type' => $n->type,
                    'created_at' => $n->created_at->diffForHumans(),
                    'is_read' => $n->is_read,
                    'data' => $data,
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    // Tandai notifikasi sebagai dibaca
    public function markAsRead($id)
    {
        $notif = Notification::where('id', $id)
            ->where('id_user', Auth::id())
            ->first();

        if (!$notif) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.'
            ], 404);
        }

        $notif->is_read = true;
        $notif->save();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai dibaca.',
        ]);
    }
}
