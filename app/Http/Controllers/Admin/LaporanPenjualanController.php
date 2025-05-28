<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use App\Http\Controllers\Controller;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('pesanan')
            ->join('users', 'pesanan.id_user', '=', 'users.id')
            ->join('detail_pesanan', 'pesanan.id_pesanan', '=', 'detail_pesanan.id_pesanan')
            ->join('produk', 'detail_pesanan.id_produk', '=', 'produk.id_produk')
            ->select(
                'pesanan.created_at',
                'users.nama as nama_pelanggan',
                'produk.nama_produk',
                'detail_pesanan.jumlah',
                'detail_pesanan.total_harga',
                'pesanan.status_pesanan'
            )
            ->whereIn('pesanan.status_pesanan', [
                'Pembayaran Diterima',
                'Sedang Diproses',
                'Pesanan Dikirim',
                'Pesanan Diterima'
            ]);
    
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('pesanan.created_at', [$request->start_date, $request->end_date]);
        }
    
        $laporan = $query->orderBy('pesanan.created_at', 'desc')->get();
    
        return view('admin.laporan-penjualan.laporan-penjualan', compact('laporan'));
    }

    public function downloadPDF(Request $request)
    {
        $query = DB::table('pesanan')
            ->join('users', 'pesanan.id_user', '=', 'users.id')
            ->join('detail_pesanan', 'pesanan.id_pesanan', '=', 'detail_pesanan.id_pesanan')
            ->join('produk', 'detail_pesanan.id_produk', '=', 'produk.id_produk')
            ->select(
                'pesanan.created_at',
                'users.nama as nama_pelanggan',
                'produk.nama_produk',
                'detail_pesanan.jumlah',
                'detail_pesanan.total_harga',
                'pesanan.status_pesanan'
            )
            ->whereIn('pesanan.status_pesanan', [
                'Pembayaran Diterima',
                'Sedang Diproses',
                'Pesanan Dikirim',
                'Pesanan Diterima'
            ]);
    
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('pesanan.created_at', [$request->start_date, $request->end_date]);
        }
    
        $laporan = $query->orderBy('pesanan.created_at', 'desc')->get();
        $totalSemua = $laporan->sum('total_harga');
    
        $startDate = \Carbon\Carbon::parse($request->start_date)->format('d-m-Y');
        $endDate = \Carbon\Carbon::parse($request->end_date)->format('d-m-Y');
        $fileName = "Laporan Penjualan-{$startDate}-{$endDate}.pdf";
    
        $pdf = FacadePdf::loadView('admin.laporan-penjualan.penjualan-pdf', compact('laporan', 'totalSemua'));
    
        return $pdf->download($fileName);
    }

}
