<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id_pembayaran
 * @property int $id_pesanan
 * @property string $metode_pembayaran
 * @property string $status_pembayaran
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pesanan $pesanan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran whereIdPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran whereIdPesanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran whereMetodePembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran whereStatusPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembayaran whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Pembayaran extends Model
{
    protected $table = 'pembayaran'; // ✅ tambahkan ini
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_pesanan',
        'metode_pembayaran',
        'status_pembayaran',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}
