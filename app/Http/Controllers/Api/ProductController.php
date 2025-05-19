<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::query()->where('stok', '>', 0);

        // 🔍 Filter berdasarkan kategori jika dikirim
        if ($request->filled('kategori')) {
            $query->where('kategori_produk', $request->kategori);
        }

        $products = $query->get()->map(function ($product) {
            return [
                'id_produk' => $product->id_produk,
                'nama_produk' => $product->nama_produk,
                'deskripsi' => $product->deskripsi,
                'harga' => $product->harga,
                'stok' => $product->stok,
                'size' => $product->size,
                'kategori_produk' => $product->kategori_produk,
                'gambar' => $product->gambar
                    ? url('uploads/' . $product->gambar)
                    : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }
}
