<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id_keranjang
 * @property int $id_user
 * @property int $id_produk
 * @property int $jumlah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Produk $produk
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereIdKeranjang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereIdProduk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Keranjang extends Model
{
    protected $table = 'keranjang';
    protected $primaryKey = 'id_keranjang';

    protected $fillable = [
        'id_user',
        'id_produk',
        'jumlah',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
