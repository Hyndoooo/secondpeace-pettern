<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
use App\Models\Alamat;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
{
    $request->validate([
        'produk' => 'required|array',
        'produk.*.id_produk' => 'required|integer|exists:produk,id_produk',
        'produk.*.jumlah' => 'required|integer|min:1',
        'ekspedisi' => 'required|string',
        'ongkir' => 'required|numeric|min:0', // ✅ tambahkan ini
    ]);

    $user = Auth::user();
    $alamat = Alamat::where('id_user', $user->id)->where('utama', true)->first();

    if (!$alamat) {
        return response()->json([
            'success' => false,
            'message' => 'Alamat utama tidak ditemukan',
        ], 400);
    }

    $produkData = collect($request->produk);
    $total = 0;
    $items = [];

    foreach ($produkData as $item) {
        $produk = Produk::find($item['id_produk']);

        if (!$produk || $produk->stok < $item['jumlah']) {
            return response()->json([
                'success' => false,
                'message' => "Stok tidak cukup atau produk tidak ditemukan: {$item['id_produk']}",
            ], 400);
        }

        $subtotal = $produk->harga * $item['jumlah'];
        $total += $subtotal;

        $items[] = [
            'id' => $produk->id_produk,
            'price' => $produk->harga,
            'quantity' => $item['jumlah'],
            'name' => $produk->nama_produk,
        ];
    }

    // ✅ Tambahkan biaya ongkir sebagai item
    $items[] = [
        'id' => 'ONGKIR',
        'price' => (int) $request->ongkir,
        'quantity' => 1,
        'name' => 'Ongkos Kirim - ' . strtoupper($request->ekspedisi),
    ];

    $total += (int) $request->ongkir;

    if ($total <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Total pembelian tidak boleh 0',
        ], 400);
    }

    DB::beginTransaction();

    try {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);

        $order_id = 'ORDER-' . strtoupper(Str::random(10));

        $pesanan = Pesanan::create([
            'id_user' => $user->id,
            'id_alamat' => $alamat->id_alamat,
            'status_pesanan' => 'Menunggu Pembayaran',
            'id_pembayaran' => $order_id,
            'ekspedisi' => $request->ekspedisi,
            'ongkir' => (int) $request->ongkir,
            'total_harga' => $total,
            'expired_at' => null,
        ]);

        foreach ($produkData as $item) {
            $produk = Produk::find($item['id_produk']);

            DetailPesanan::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'id_produk' => $produk->id_produk,
                'jumlah' => $item['jumlah'],
                'total_harga' => $produk->harga * $item['jumlah'],
            ]);

            $produk->decrement('stok', $item['jumlah']);
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $user->nama,
                'email' => $user->email,
                'phone' => $alamat->no_whatsapp,
            ],
            'shipping_address' => [
                'first_name' => $user->nama,
                'address' => $alamat->alamat,
                'phone' => $alamat->no_whatsapp,
            ],
            'item_details' => $items,
        ];

        $transaction = Snap::createTransaction($params);
        $snapToken = $transaction->token ?? null;

        if (!$snapToken) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Snap token tidak tersedia',
                'transaction' => $transaction,
            ], 500);
        }

        $pesanan->update([
            'snap_token' => $snapToken,
            'expired_at' => $transaction->expiry_time ?? now()->addMinutes(15),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat',
            'snap_token' => $snapToken,
            'order_id' => $order_id,
            'expired_at' => $pesanan->expired_at,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Midtrans SnapToken Error', [
            'error' => $e->getMessage(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat transaksi',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
