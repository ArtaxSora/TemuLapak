<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $fillable = [
        'id_penerima', 'tipe', 'judul', 'isi', 'dibaca_pada',
    ];

    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_penerima');
    }
}
