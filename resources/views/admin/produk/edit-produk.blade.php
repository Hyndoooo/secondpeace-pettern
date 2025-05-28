@extends('layouts.master')

@section('title', 'Edit Produk')

@section('content')
<header class="mb-4">
    <link rel="stylesheet" href="{{ asset('css/tambah-produk.css') }}">
</header>

<div class="admin-container">
    <div class="container py-4">
        <h1 class="mb-4">Edit Produk</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('produk.update', $produk->id_produk) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="nama_produk">Nama Produk</label>
                <input type="text" id="nama_produk" name="nama_produk" class="form-control" value="{{ $produk->nama_produk }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="kategori_produk">Kategori Produk</label>
                <select id="kategori_produk" name="kategori_produk" class="form-control" required>
                    <option value="" disabled {{ $produk->kategori_produk == '' ? 'selected' : '' }}>Pilih kategori produk</option>
                    <option value="Pria" {{ $produk->kategori_produk == 'Pria' ? 'selected' : '' }}>Pria</option>
                    <option value="Wanita" {{ $produk->kategori_produk == 'Wanita' ? 'selected' : '' }}>Wanita</option>
                    <option value="Unisex" {{ $produk->kategori_produk == 'Unisex' ? 'selected' : '' }}>Unisex</option>
                    <option value="Sepatu" {{ $produk->kategori_produk == 'Sepatu' ? 'selected' : '' }}>Sepatu</option>
                    <option value="Aksesoris" {{ $produk->kategori_produk == 'Aksesoris' ? 'selected' : '' }}>Aksesoris</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" required>{{ $produk->deskripsi }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label for="harga">Harga Produk</label>
                <input type="number" id="harga" name="harga" class="form-control" step="0.01" value="{{ $produk->harga }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="stok">Stok Produk</label>
                <input type="number" id="stok" name="stok" class="form-control" value="{{ $produk->stok }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="gambar">Gambar Produk</label>
                <input type="file" id="gambar" name="gambar" class="form-control">
                @if($produk->gambar)
                    <img src="{{ asset('uploads/' . $produk->gambar) }}" alt="gambar produk" class="img-thumbnail mt-2" width="150">
                @endif
            </div>

            <div class="form-group mb-3">
                <label for="kualitas">Kualitas Produk</label>
                <select id="kualitas" name="kualitas" class="form-control" required>
                    <option value="tinggi" {{ $produk->kualitas == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="sedang" {{ $produk->kualitas == 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="rendah" {{ $produk->kualitas == 'rendah' ? 'selected' : '' }}>Rendah</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="size">Size</label>
                <select id="size" name="size" class="form-control" required>
                    <option value="S" {{ $produk->size == 'S' ? 'selected' : '' }}>S</option>
                    <option value="M" {{ $produk->size == 'M' ? 'selected' : '' }}>M</option>
                    <option value="L" {{ $produk->size == 'L' ? 'selected' : '' }}>L</option>
                    <option value="XL" {{ $produk->size == 'XL' ? 'selected' : '' }}>XL</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
