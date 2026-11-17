<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogStatusTempat extends Model
{
    protected $fillable = [
        'id_tempat', 'status_lama', 'status_baru', 'dibuat_oleh', 'waktu',
    ];

    protected $table = 'log_status_tempat';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
