@extends('layouts.master')

@section('title', 'Manajemen Pesanan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/manajemen-pesanan.css') }}">

<div class="halaman-pesanan">
    <div class="container">
        <h2>📦 Manajemen Pesanan</h2>

        @if (session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter -->
        <form method="GET" action="{{ route('manajemen.pesanan') }}" class="d-flex align-items-center gap-2 mb-3">
            <select name="status" class="form-select w-auto">
                <option value="">Semua Status</option>
                @foreach(['Menunggu Pembayaran', 'Pembayaran Diterima', 'Sedang Diproses', 'Pesanan Dibatalkan', 'Pesanan Dikirim', 'Pesanan Diterima'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <button type="submit" name="reset" value="1" class="btn btn-secondary">Reset</button>
        </form>        

        <!-- Table -->
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID Pesanan</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>No Resi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($pesanan as $p)
                    @php
                        $subtotal = $p->detailPesanan->sum(fn($dp) => $dp->jumlah * $dp->produk->harga);
                        $total += $subtotal;
                    @endphp
                    <form method="POST" action="{{ route('pesanan.update', $p->id_pesanan) }}">
                        @csrf
                        <tr>
                            <td>{{ $p->id_pesanan }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y') }}</td>
                            <td>{{ $p->user->nama ?? '-' }}</td>
                            <td>
                                @foreach ($p->detailPesanan as $dp)
                                    {{ $dp->produk->nama_produk }}<br>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($p->detailPesanan as $dp)
                                    x{{ $dp->jumlah }}<br>
                                @endforeach
                            </td>
                            <td>Rp {{ $subtotal }}</td>
                            <td>
                                <select name="status_pesanan" class="form-select">
                                    @foreach(['Menunggu Pembayaran', 'Pembayaran Diterima', 'Sedang Diproses', 'Pesanan Dibatalkan', 'Pesanan Dikirim', 'Pesanan Diterima'] as $status)
                                        <option value="{{ $status }}" {{ $p->status_pesanan === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="nomor_resi" value="{{ $p->nomor_resi ?? '' }}" class="form-control">
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2>
                                    {{-- Tombol Update --}}
                                    <form action="{{ route('pesanan.update', ['id' => $p->id_pesanan]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success d-flex align-items-center gap-1">
                                            <span>Update</span>
                                        </button>
                                    </form>
                            
                                    {{-- Tombol Rincian --}}
                                    <form action="{{ route('rincian.pesanan', ['id' => $p->id_pesanan]) }}" method="GET">
                                        <button type="submit" class="btn btn-info d-flex align-items-center gap-1">
                                            <span>Rincian</span>
                                        </button>
                                    </form>
                                </div>
                            </td>                                                                       
                        </tr>
                    </form>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
