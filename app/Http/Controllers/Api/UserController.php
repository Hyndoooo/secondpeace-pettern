<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user(); // lebih ringkas

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->nama = $request->nama;
        $user->email = $request->email;

        if ($request->hasFile('foto_profil')) {
            // Hapus file lama jika ada
            if ($user->foto_profil && Storage::exists('public/foto_profil/' . $user->foto_profil)) {
                Storage::delete('public/foto_profil/' . $user->foto_profil);
            }

            $path = $request->file('foto_profil')->store('public/foto_profil');
            $user->foto_profil = basename($path);
        }

        $user->save();

        // Sertakan URL foto jika ada
        $user->foto_profil_url = $user->foto_profil
            ? asset('storage/foto_profil/' . $user->foto_profil)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => $user,
        ]);
    }
}
