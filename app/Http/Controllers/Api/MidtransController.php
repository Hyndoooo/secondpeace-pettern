<?php

namespace App\Http\Controllers\Api;

use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Alamat;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Midtrans\Snap;
use Midtrans\Config;

class MidtransController extends Controller
{
    public function getSnapToken(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $userId = Auth::id();
        $keranjang = Keranjang::where('id_user', $userId)->with('produk')->get();

        if ($keranjang->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }

        $total = 0;
        foreach ($keranjang as $item) {
            if (!$item->produk || $item->produk->stok < $item->jumlah) {
                return response()->json([
                    'message' => "Produk '{$item->produk->nama_produk}' tidak tersedia atau stok kurang"
                ], 400);
            }
            $total += $item->produk->harga * $item->jumlah;
        }

        $order_id = 'ORDER-' . strtoupper(uniqid());

        $alamat = Alamat::where('id_user', $userId)->where('utama', true)->first();
        if (!$alamat) {
            return response()->json(['message' => 'Alamat utama tidak ditemukan'], 400);
        }

        $pesanan = Pesanan::create([
            'id_user'        => $userId,
            'id_alamat'      => $alamat->id_alamat,
            'status_pesanan' => 'Menunggu Pembayaran',
            'id_pembayaran'  => $order_id,
            'grand_total'    => $total,
        ]);

        foreach ($keranjang as $item) {
            DetailPesanan::create([
                'id_pesanan'  => $pesanan->id_pesanan,
                'id_produk'   => $item->produk->id_produk,
                'jumlah'      => $item->jumlah,
                'total_harga' => $item->produk->harga * $item->jumlah,
            ]);
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $order_id,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->nama,
                'email'      => Auth::user()->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $pesanan->snap_token = $snapToken;
            $pesanan->save();

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $order_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mendapatkan Snap Token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyPaymentStatus($order_id)
    {
        try {
            Log::info("Verifying payment status for order_id: $order_id");

            $serverKey = config('midtrans.server_key');
            $isProduction = config('midtrans.is_production', false);
            $baseUrl = $isProduction
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com';

            $response = Http::withBasicAuth($serverKey, '')
                ->get("$baseUrl/v2/$order_id/status");

            if ($response->failed()) {
                // Log error if the response failed
                Log::error("Failed to fetch payment status. Response body: " . $response->body());
                return response()->json([
                    'message' => 'Gagal ambil status dari Midtrans',
                    'midtrans_response' => $response->body(), // Log the response body
                ], $response->status());
            }

            // Check if response is in JSON format
            try {
                $data = $response->json();
            } catch (\Exception $e) {
                Log::error("Failed to parse JSON response: " . $response->body());
                return response()->json([
                    'message' => 'Gagal mengolah data pembayaran.',
                    'error'   => $e->getMessage(),
                ], 500);
            }

            Log::info("Midtrans Response: ", $data);

            $status = $data['transaction_status'] ?? 'unknown';

            $pesanan = Pesanan::where('id_pembayaran', $order_id)->first();
            if ($pesanan) {
                switch ($status) {
                    case 'settlement':
                        $pesanan->status_pesanan = 'Pembayaran Diterima';
                        break;
                    case 'cancel':
                    case 'expire':
                        $pesanan->status_pesanan = 'Pesanan Dibatalkan';
                        break;
                    case 'pending':
                        $pesanan->status_pesanan = 'Menunggu Pembayaran';
                        break;
                    default:
                        $pesanan->status_pesanan = 'Status Tidak Dikenal';
                        break;
                }
                $pesanan->save();
            }

            return response()->json([
                'status' => $status,
                'order_id' => $order_id,
                'pesanan_status' => $pesanan->status_pesanan ?? null,
                'raw' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error("Error verifying payment status for order_id: $order_id. Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal verifikasi status',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $receivedSignature = $request->input('signature_key');
        $transactionStatus = $request->input('transaction_status');

        // Validasi signature
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($receivedSignature !== $expectedSignature) {
            return response()->json(['message' => 'Signature tidak valid'], 403);
        }

        // Ambil pesanan dari DB
        $pesanan = Pesanan::where('id_pembayaran', $orderId)->first();
        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Update status berdasarkan status Midtrans
        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $pesanan->status_pesanan = 'Pembayaran Diterima';
                break;
            case 'cancel':
            case 'deny':
            case 'expire':
                $pesanan->status_pesanan = 'Pesanan Dibatalkan';
                break;
            case 'pending':
                $pesanan->status_pesanan = 'Menunggu Pembayaran';
                break;
            default:
                $pesanan->status_pesanan = 'Status Tidak Dikenal';
                break;
        }

        $pesanan->save();

        Log::info("Callback processed", [
            'order_id' => $orderId,
            'status' => $transactionStatus,
        ]);

        return response()->json([
            'message' => 'Status pesanan diperbarui',
            'status' => $transactionStatus,
        ]);
    }
}
