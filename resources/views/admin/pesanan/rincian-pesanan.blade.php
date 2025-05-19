@extends('layouts.master')

@section('title', 'Rincian Pesanan')

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/rincian-pesanan.css') }}">
    <script src="{{ asset('js/rincian-pesanan.js') }}"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<div class="rincian-container">

    <!-- Status Pesanan -->
    <!--<div class="status-bar">-->
    <!--    <div class="status-group">-->
    <!--        <span class="status-label">Status Pesanan:</span>-->
    <!--        <span class="status-value">{{ $pesanan->status_pesanan }}</span>-->
    <!--    </div>-->
    <!--    <span class="tanggal-pesan">Dipesan pada: {{ \Carbon\Carbon::parse($pesanan->created_at)->format('d M Y, H:i') }}</span>-->
    <!--</div>-->

    <!-- Box Produk -->
    <div class="produk-box">
        <div class="nama-toko">
            <h5><strong>{{ $pesanan->toko->nama_toko ?? 'Second Peace' }}</strong></h5>
            <span class="tanggal-pesan">Dipesan pada: {{ \Carbon\Carbon::parse($pesanan->created_at)->format('d M Y, H:i') }}</span>
        </div>
        <div class="produk-list">
            @php $total = 0; @endphp
            @foreach($pesanan->detailPesanan as $item)
                @php
                    $subtotal = $item->jumlah * $item->produk->harga;
                    $total += $subtotal;
                @endphp
                <div class="produk-item">
                    <img src="{{ asset('uploads/' . $item->produk->gambar) }}" alt="{{ $item->produk->nama_produk }}">
                    <div class="produk-info">
                        <div class="produk-nama">{{ $item->produk->nama_produk }}</div>
                        <div class="produk-variasi">Jumlah: {{ $item->jumlah }}</div>
                        <div class="produk-harga">Rp {{ number_format($item->produk->harga, 0, ',', '.') }}</div>
                    </div>
                    <div class="produk-subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Alamat Pengiriman -->
    <div class="produk-box">
        <h5><strong>Alamat Pengiriman</strong></h5>
        @if($pesanan->alamat)
            <p>
                <strong>Nama:</strong> {{ $pesanan->alamat->nama }} <br>
                <strong>Alamat:</strong> {{ $pesanan->alamat->alamat }} <br>
                <strong>WhatsApp:</strong> {{ $pesanan->alamat->no_whatsapp }} <br>
            </p>
        @else
            <p class="text-danger">Alamat tidak tersedia.</p>
        @endif
    </div>

    <!-- Metode Pembayaran -->
    <div class="produk-box">
        <h5><strong>Metode Pembayaran</strong></h5>
        <p>
            <strong>ID Pembayaran:</strong> {{ $pesanan->id_pembayaran }} <br>
            <strong>Metode:</strong> {{ $pesanan->pembayaran->metode_pembayaran }} <br>
            <strong>Status:</strong> {{ $pesanan->status_pesanan }} <br>
        </p>
    </div>

    <!-- Ringkasan Pembayaran -->
    <div class="produk-box">
        <h5><strong>Pembayaran</strong></h5>
        <table class="table table-borderless mb-0">
            <tr>
                <td>Total Harga</td>
                <td class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ongkos Kirim</td>
                <td class="text-end">Rp {{ number_format($pesanan->ongkir ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Total Bayar</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($total + ($pesanan->ongkir ?? 0), 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Tombol Kembali -->
    <div class="text-end mt-4">
        <a href="{{ route('manajemen.pesanan') }}" class="btn btn-secondary">⬅ Kembali</a>
    </div>
</div>
@endsection
