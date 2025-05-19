<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    protected $primaryKey = 'id_pesanan';
    protected $table = 'pesanan';

    protected $fillable = [
        'id_user',
        'id_alamat',
        'id_pembayaran',
        'status_pesanan',
        'nomor_resi',
        'ekspedisi',
        'tanggal_diterima',
        'expired_at',
        'snap_token',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'status_pesanan' => 'string',
    ];

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke Alamat
    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class, 'id_alamat');
    }

    // Relasi ke Detail Pesanan (banyak item)
    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan');
    }

    // Relasi opsional ke tabel Pembayaran (jika ada)
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan')->withDefault();
    }

    // Menambahkan status pesanan sebagai konstanta atau enum
    const STATUS_MENUNGGU_PEMBAYARAN = 'Menunggu Pembayaran';
    const STATUS_PESANAN_DIBATALKAN = 'Pesanan Dibatalkan';
    const STATUS_PEMBAYARAN_DITERIMA = 'Pembayaran Diterima';
    const STATUS_PESANAN_DIKIRIM = 'Pesanan Dikirim';
    const STATUS_PESANAN_DITERIMA = 'Pesanan Diterima';

    // Validasi status_pesanan
    public static function getValidStatuses()
    {
        return [
            self::STATUS_MENUNGGU_PEMBAYARAN,
            self::STATUS_PESANAN_DIBATALKAN,
            self::STATUS_PEMBAYARAN_DITERIMA,
            self::STATUS_PESANAN_DIKIRIM,
            self::STATUS_PESANAN_DITERIMA,
        ];
    }

    // Pastikan status_pesanan hanya bernilai salah satu dari status yang valid
    public function setStatusPesananAttribute($value)
    {
        if (!in_array($value, self::getValidStatuses())) {
            throw new \InvalidArgumentException("Status pesanan tidak valid.");
        }
        $this->attributes['status_pesanan'] = $value;
    }

    // Memeriksa apakah status pesanan valid
    public function isStatusValid($status)
    {
        return in_array($status, self::getValidStatuses());
    }
}
