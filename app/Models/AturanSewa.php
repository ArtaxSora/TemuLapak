<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AturanSewa extends Model
{
    protected $fillable = [
        'id_tempat', 'durasi_minimal', 'perpanjangan', 'sistem_pembayaran', 'ketentuan_pembatalan', 'denda_keterlambatan',
    ];

    protected $table = 'aturan_sewa';

    protected $primaryKey = 'id_tempat';

    public $timestamps = false;

    public $incrementing = false;

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
