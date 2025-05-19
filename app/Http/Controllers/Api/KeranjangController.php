<?php

namespace App\Http\Controllers\Api;

use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class KeranjangController extends Controller
{
    // ✅ Ambil keranjang milik user login
    public function index()
    {
        $user = Auth::user();
        $items = Keranjang::with('produk')->where('id_user', $user->id)->get();

        foreach ($items as $item) {
            $produk = $item->produk;
            $item->is_sold = false;

            if ($produk) {
                $produk->gambar = $produk->gambar
                    ? url('uploads/' . $produk->gambar)
                    : null;

                // Cek apakah produk sudah dibeli user lain
                $sudahDibeli = DetailPesanan::where('id_produk', $produk->id_produk)
                    ->whereHas('pesanan', function ($q) use ($user) {
                        $q->where('id_user', '!=', $user->id)
                          ->whereNotIn('status_pesanan', ['Pesanan Dibatalkan', 'Menunggu Pembayaran']);
                    })->exists();

                if ($sudahDibeli) {
                    $item->is_sold = true;
                }
            } else {
                $item->is_sold = true; // Produk sudah dihapus
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data keranjang berhasil diambil',
            'keranjang' => $items,
        ]);
    }

    // ✅ Tambah produk ke keranjang
    public function store(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|integer',
            'jumlah'    => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $produk = Produk::find($request->id_produk);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        if ($produk->stok < $request->jumlah) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah melebihi stok tersedia',
            ], 400);
        }

        $existing = Keranjang::where('id_user', $user->id)
            ->where('id_produk', $request->id_produk)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Produk sudah ada di keranjang',
            ], 409);
        }

        $item = Keranjang::create([
            'id_user'   => $user->id,
            'id_produk' => $request->id_produk,
            'jumlah'    => $request->jumlah,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'data' => $item,
        ]);
    }

    // ✅ Update jumlah produk
    public function update(Request $request, $id)
{
    $request->validate([
        'jumlah' => 'required|integer|min:1',
    ]);

    $user = Auth::user();
    $item = Keranjang::where('id_keranjang', $id)->where('id_user', $user->id)->firstOrFail();
    $item->jumlah = $request->jumlah;
    $item->save();

    return response()->json([
        'success' => true,
        'message' => 'Jumlah item berhasil diperbarui',
        'data' => $item,
    ]);
}


    // ✅ Hapus item dari keranjang
    public function destroy($id)
{
    $user = Auth::user();
    $item = Keranjang::where('id_keranjang', $id)->where('id_user', $user->id)->firstOrFail();
    $item->delete();

    return response()->json([
        'success' => true,
        'message' => 'Item berhasil dihapus dari keranjang',
    ]);
}

}
