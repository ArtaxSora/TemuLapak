<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ulasan extends Model
{
    protected $fillable = [
        'id_booking', 'rating', 'komentar',
    ];

    protected $table = 'ulasan';

    protected $primaryKey = 'id_ulasan';

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking');
    }
}
