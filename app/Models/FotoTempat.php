<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoTempat extends Model
{
    protected $fillable = [
        'id_tempat', 'url_foto', 'urutan',
    ];

    protected $table = 'foto_tempat';

    protected $primaryKey = 'id_foto';

    public $timestamps = false;

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
