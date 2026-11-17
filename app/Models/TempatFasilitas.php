<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TempatFasilitas extends Pivot
{
    protected $table = 'tempat_fasilitas';

    protected $foreignKey = 'id_tempat';

    protected $relatedKey = 'id_fasilitas';

    public $timestamps = false;
}
