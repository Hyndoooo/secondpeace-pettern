<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Pesanan;

class PesananController extends Controller
{
    // Ambil semua pesanan user (dengan pengecekan status otomatis)
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil semua pesanan berdasarkan user
        $pesananQuery = Pesanan::with(['detailPesanan.produk', 'alamat'])
            ->where('id_user', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Cek dan update status untuk setiap item
        $items = $pesananQuery->getCollection()->map(function ($pesanan) {
            // Batalkan jika expired
            if ($pesanan->status_pesanan === 'Menunggu Pembayaran' && $pesanan->expired_at && now()->greaterThan($pesanan->expired_at)) {
                $pesanan->status_pesanan = 'Pesanan Dibatalkan';
                $pesanan->save();
            }

            // Cek status pembayaran melalui Midtrans
            if ($pesanan->status_pesanan === 'Menunggu Pembayaran') {
                try {
                    $serverKey = config('midtrans.server_key');
                    $isProduction = config('midtrans.is_production', false);
                    $baseUrl = $isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

                    $response = Http::withBasicAuth($serverKey, '')->get("$baseUrl/v2/{$pesanan->id_pembayaran}/status");

                    if ($response->successful()) {
                        $status = $response->json()['transaction_status'] ?? null;

                        if ($status === 'settlement') {
                            $pesanan->status_pesanan = 'Pembayaran Diterima';
                        } elseif ($status === 'cancel' || $status === 'expire') {
                            $pesanan->status_pesanan = 'Pesanan Dibatalkan';
                        }
                        $pesanan->save();
                    } else {
                        Log::error('Midtrans error: ' . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal cek status Midtrans: ' . $e->getMessage());
                }
            }

            // Validasi nilai status_pesanan sebelum disimpan
            $validStatuses = ['Menunggu Pembayaran', 'Pembayaran Diterima', 'Pesanan Dibatalkan', 'Pesanan Dikirim', 'Pesanan Diterima'];
            if (!in_array($pesanan->status_pesanan, $validStatuses)) {
                $pesanan->status_pesanan = 'Pesanan Dibatalkan';  // Set default jika status tidak valid
                $pesanan->save();
            }

            // Tambahan data untuk frontend
            $pesanan->tanggal = $pesanan->created_at->format('d M Y H:i');
            $pesanan->tanggal_pesan = $pesanan->created_at->toDateTimeString();
            $pesanan->estimasi_tiba = $pesanan->created_at->copy()->addDays(3)->toDateTimeString();
            $pesanan->grand_total = $pesanan->detailPesanan->sum(function ($d) {
                return $d->total_harga;
            });

            return $pesanan;
        });

        // Ganti isi collection dari paginator
        $pesananQuery->setCollection($items);

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil diambil.',
            'pesanan' => $pesananQuery->items(),
            'current_page' => $pesananQuery->currentPage(),
            'last_page' => $pesananQuery->lastPage(),
            'total' => $pesananQuery->total(),
        ]);
    }

    // Ambil detail satu pesanan
    public function show($id)
    {
        $user = Auth::user();

        $pesanan = Pesanan::with(['detailPesanan.produk', 'alamat'])
            ->where('id_user', $user->id)
            ->where('id_pesanan', $id)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        $pesanan->tanggal = $pesanan->created_at->format('d M Y H:i');
        $pesanan->tanggal_pesan = $pesanan->created_at->toDateTimeString();
        $pesanan->estimasi_tiba = $pesanan->created_at->copy()->addDays(3)->toDateTimeString();
        $pesanan->grand_total = $pesanan->detailPesanan->sum(function ($d) {
            return $d->total_harga;
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan ditemukan',
            'pesanan' => $pesanan,
        ]);
    }

    // Batalkan pesanan
    public function cancel($id)
    {
        $user = Auth::user();

        $pesanan = Pesanan::where('id_user', $user->id)
            ->where('id_pesanan', $id)
            ->where('status_pesanan', 'Menunggu Pembayaran')
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan.',
            ], 404);
        }

        $pesanan->status_pesanan = 'Pesanan Dibatalkan';
        $pesanan->save();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
        ]);
    }

    // Tandai pesanan sebagai diterima
    public function markAsReceived($id)
    {
        $user = Auth::user();

        $pesanan = Pesanan::where('id_user', $user->id)
            ->where('id_pesanan', $id)
            ->where('status_pesanan', 'Pesanan Dikirim')
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum dikirim atau tidak ditemukan.',
            ], 404);
        }

        $pesanan->status_pesanan = 'Pesanan Diterima';
        $pesanan->tanggal_diterima = now();
        $pesanan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui.',
        ]);
    }
}
