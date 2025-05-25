<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
    'id_user',
    'title',
    'message',
    'type',
    'is_read',
    'id_ref', // ✅ tambahkan ini!
];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function pesanan()
{
    return $this->belongsTo(Pesanan::class, 'id_ref', 'id_pesanan');
}


}

