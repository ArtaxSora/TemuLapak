<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fasilitas extends Model
{
    protected $fillable = [
        'nama_fasilitas',
    ];

    protected $table = 'fasilitas';

    protected $primaryKey = 'id_fasilitas';

    public $timestamps = false;

    public function tempat(): BelongsToMany
    {
        return $this->belongsToMany(Tempat::class, 'tempat_fasilitas', 'id_fasilitas', 'id_tempat')->using(TempatFasilitas::class);
    }
}
