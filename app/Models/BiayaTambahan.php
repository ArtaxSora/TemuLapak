<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaTambahan extends Model
{
    protected $fillable = [
        'id_tempat', 'nama_biaya', 'nominal',
    ];

    protected $table = 'biaya_tambahan';

    protected $primaryKey = 'id_biaya';

    public $timestamps = false;

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
