<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Alamat;

class AlamatController extends Controller
{
    // ✅ Ambil semua alamat user
    public function index()
    {
        $user = Auth::user();
        $alamat = $user->alamat()->get();

        return response()->json([
            'success' => true,
            'alamat' => $alamat,
        ]);
    }

    // ✅ Tambah alamat
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
    'nama' => 'required|string|max:255',
    'no_whatsapp' => 'required|string|max:20',
    'alamat' => 'required|string',
    'provinsi_id' => 'required|integer',
    'provinsi_nama' => 'required|string',
    'kota_id' => 'required|integer',
    'kota_nama' => 'required|string',
    'utama' => 'boolean',
]);

if ($request->boolean('utama')) {
    $user->alamat()->update(['utama' => 0]);
}

$alamat = $user->alamat()->create([
    'nama' => $validated['nama'],
    'no_whatsapp' => $validated['no_whatsapp'],
    'alamat' => $validated['alamat'],
    'provinsi_id' => $validated['provinsi_id'],
    'provinsi_nama' => $validated['provinsi_nama'],
    'kota_id' => $validated['kota_id'],
    'kota_nama' => $validated['kota_nama'],
    'utama' => $request->boolean('utama'),
]);
return response()->json([
    'success' => true,
    'message' => 'Alamat berhasil ditambahkan.',
    'alamat' => $alamat,
]);

    }

    // ✅ Update alamat
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
    'nama' => 'required|string|max:255',
    'no_whatsapp' => 'required|string|max:20',
    'alamat' => 'required|string',
    'provinsi_id' => 'required|integer',
    'provinsi_nama' => 'required|string',
    'kota_id' => 'required|integer',
    'kota_nama' => 'required|string',
    'utama' => 'boolean',
]);

$alamat = $user->alamat()->where('id_alamat', $id)->firstOrFail();

if ($request->boolean('utama')) {
    $user->alamat()->update(['utama' => 0]);
}

$alamat->update([
    'nama' => $validated['nama'],
    'no_whatsapp' => $validated['no_whatsapp'],
    'alamat' => $validated['alamat'],
    'provinsi_id' => $validated['provinsi_id'],
    'provinsi_nama' => $validated['provinsi_nama'],
    'kota_id' => $validated['kota_id'],
    'kota_nama' => $validated['kota_nama'],
    'utama' => $request->boolean('utama'),
]);
return response()->json([
    'success' => true,
    'message' => 'Alamat berhasil diperbarui.',
    'alamat' => $alamat,
]);

    }

    // ✅ Hapus alamat
    public function destroy($id)
    {
        $user = Auth::user();

        $alamat = $user->alamat()->where('id_alamat', $id)->firstOrFail();
        $alamat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    // ✅ Set alamat utama
    public function setPrimary($id)
    {
        $user = Auth::user();

        $user->alamat()->update(['utama' => 0]);

        $target = $user->alamat()->where('id_alamat', $id)->firstOrFail();
        $target->update(['utama' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil disetel.',
            'alamat' => $target,
        ]);
    }
}
