<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPesanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id_detail_pesanan';

    protected $fillable = [
        'id_pesanan',
        'id_produk',
        'jumlah',
        'total_harga',
    ];

    /**
     * Relasi ke model Pesanan
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    /**
     * Relasi ke model Produk (termasuk produk yang sudah dihapus)
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk')->withTrashed();
    }
}
