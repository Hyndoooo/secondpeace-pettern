<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alamat extends Model
{
    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';

    protected $fillable = [
    'id_user',
    'nama',
    'no_whatsapp',
    'alamat',
    'provinsi_id',
    'provinsi_nama',
    'kota_id',
    'kota_nama',
    'utama',
];



    /**
     * Relasi ke model User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
