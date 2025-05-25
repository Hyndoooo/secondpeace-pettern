<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Pesanan;
use App\Models\Notification;


class PesananController extends Controller
{
    // Ambil semua pesanan user (dengan pengecekan status otomatis)
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil semua pesanan berdasarkan user
        $pesananQuery = Pesanan::with([
    'detailPesanan' => function ($query) {
        $query->select('id_detail_pesanan', 'id_pesanan', 'id_produk', 'jumlah', 'total_harga');
    },
    'detailPesanan.produk' => function ($query) {
        $query->select('id_produk', 'nama_produk', 'harga', 'gambar', 'size');
    },
    'alamat' => function ($query) {
        $query->select('id_alamat', 'nama', 'alamat', 'no_whatsapp', 'kota_nama', 'provinsi_nama');
    },
])
->where('id_user', $user->id)
->orderBy('created_at', 'desc')
->paginate(10);
Log::info('Total JSON response bytes: ' . strlen(json_encode($pesananQuery->items())));



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
            $produkTotal = $pesanan->detailPesanan->sum(function ($d) {
    return $d->total_harga;
});
$pesanan->grand_total = $produkTotal + ($pesanan->ongkir ?? 0);


            return $pesanan;
        });

        // Ganti isi collection dari paginator
        $pesananQuery->setCollection($items);
        Log::info(json_last_error_msg());

        

        return response()->json([
    'success' => true,
    'message' => 'Data pesanan berhasil diambil.',
    'pesanan' => array_values($pesananQuery->items()), // 🧼 array numerik rapi
    'current_page' => $pesananQuery->currentPage(),
    'last_page' => $pesananQuery->lastPage(),
    'total' => $pesananQuery->total(),
])->header('Content-Type', 'application/json');




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

    // Siapkan struktur detail_pesanan
    $detailPesanan = $pesanan->detailPesanan->map(function ($d) {
        return [
            'id_detail_pesanan' => $d->id_detail_pesanan,
            'jumlah' => $d->jumlah,
            'total_harga' => $d->total_harga,
            'produk' => [
                'id_produk' => $d->produk->id_produk ?? null,
                'nama_produk' => $d->produk->nama_produk ?? 'Produk tidak tersedia',
                'harga' => $d->produk->harga ?? 0,
                'gambar' => $d->produk->gambar ?? '',
                'size' => $d->produk->size ?? '-',
            ],
        ];
    });

    // Hitung total
    $produkTotal = $pesanan->detailPesanan->sum('total_harga');
    $grandTotal = $produkTotal + ($pesanan->ongkir ?? 0);

    return response()->json([
        'success' => true,
        'message' => 'Detail pesanan ditemukan',
        'pesanan' => [
            'id_pesanan' => $pesanan->id_pesanan,
            'status_pesanan' => $pesanan->status_pesanan,
            'tanggal' => $pesanan->created_at->format('d M Y H:i'),
            'tanggal_pesan' => $pesanan->created_at->toDateTimeString(),
            'estimasi_tiba' => $pesanan->created_at->copy()->addDays(3)->toDateTimeString(),
            'ongkir' => $pesanan->ongkir,
            'grand_total' => $grandTotal,
            'nomor_resi' => $pesanan->nomor_resi,
            'ekspedisi' => $pesanan->ekspedisi,
            'alamat' => $pesanan->alamat ?? null,
            'detail_pesanan' => $detailPesanan,
        ],
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
    Log::warning("Gagal markAsReceived. ID: $id, User ID: {$user->id}");
    return response()->json([
        'success' => false,
        'message' => 'Pesanan tidak dapat diperbarui. Mungkin status bukan Dikirim.',
    ], 404);
}


        $pesanan->status_pesanan = 'Pesanan Diterima';
$pesanan->tanggal_diterima = now();
$pesanan->save();

// 🔔 Tambahkan notifikasi
Notification::create([
    'id_user' => $user->id,
    'title' => 'Pesanan Diterima',
    'message' => 'Terima kasih! Pesanan #' . $pesanan->id_pesanan . ' telah kamu konfirmasi sebagai diterima.',
    'type' => 'pesanan',
    'id_ref' => $pesanan->id_pesanan,
]);


        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui.',
        ]);
    }
}
