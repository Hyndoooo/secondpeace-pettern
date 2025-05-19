<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProdukController extends Controller
{

    public function index(Request $request)
    {
        $query = Produk::query();

        // Jika ada input pencarian
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('id_produk', 'like', '%' . $search . '%');
        }

        $produk = $query->get();

        return view('admin.produk.manajemen-produk', compact('produk'));
    }

    public function create()
    {
        return view('admin.produk.tambah-produk');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_produk' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'kualitas' => 'required|in:tinggi,sedang,rendah',
            'size' => 'required|in:S,M,L,XL',
            'stok' => 'required|integer|min:0',
            'gambar' => 'required|image|mimes:jpg,jpeg,png|max:10240',    
        ]);

        // Simpan gambar
        $imageName = time() . '.' . $request->file('gambar')->extension();
        $request->file('gambar')->move(public_path('uploads'), $imageName);

        // Simpan data ke database
        Produk::create([
            'id_user' => Auth::id(),
            'nama_produk' => $request->input('nama_produk'),
            'kategori_produk' => $request->input('kategori_produk'),
            'deskripsi' => $request->input('deskripsi'),
            'harga' => $request->input('harga'),
            'kualitas' => $request->input('kualitas'),
            'size' => $request->input('size'),
            'stok' => $request->input('stok'),
            'gambar' => $imageName,
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
        
    }

    public function edit($id)
{
    $produk = Produk::findOrFail($id);
    return view('admin.produk.edit-produk', compact('produk'));
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required',
            'deskripsi' => 'required',
            'harga' => 'required|numeric',
            'kualitas' => 'required',
            'size' => 'required',
            'stok' => 'required|integer',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $produk = Produk::findOrFail($id);

        // Menyimpan data yang diperbarui, kecuali gambar
        $produk->nama_produk = $request->nama_produk;
        $produk->deskripsi = $request->deskripsi;
        $produk->harga = $request->harga;
        $produk->kualitas = $request->kualitas;
        $produk->size = $request->size;
        $produk->stok = $request->stok;

        // Cek apakah ada file gambar yang diupload
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($produk->gambar && file_exists(public_path('uploads/' . $produk->gambar))) {
                unlink(public_path('uploads/' . $produk->gambar));
            }

            // Simpan gambar baru
            $gambar = $request->file('gambar');
            $namaGambar = time() . '_' . $gambar->getClientOriginalName();
            $gambar->move(public_path('uploads'), $namaGambar);

            $produk->gambar = $namaGambar;
        }

        $produk->save();

        return redirect()->route('manajemen.produk')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy($id)    
    {
        $produk = Produk::findOrFail($id);

        // Hapus gambar jika ada
        if ($produk->gambar && file_exists(public_path('uploads/' . $produk->gambar))) {
            unlink(public_path('uploads/' . $produk->gambar));
        }

        $produk->delete();

        return redirect()->route('manajemen.produk')->with('success', 'Produk berhasil dihapus');
    }

}
