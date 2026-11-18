<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaTambahan extends Model
{
    protected $fillable = [
        'id_tempat', 'nama_biaya', 'nominal', 'periode', 'metode', 'satuan_pemakaian', 'perkiraan_pemakaian',
    ];

    protected $table = 'biaya_tambahan';

    protected $primaryKey = 'id_biaya';

    public $timestamps = false;

    public function rateDescription(string $fallbackPeriod): string
    {
        if ($this->metode === 'termasuk') {
            return 'Termasuk harga sewa';
        }
        $rate = 'Rp '.number_format($this->nominal, 0, ',', '.');
        if ($this->metode === 'pemakaian') {
            return $rate.' / '.$this->satuan_pemakaian.' · sesuai pemakaian per '.($this->periode ?: $fallbackPeriod);
        }

        return $rate.' / '.($this->periode ?: $fallbackPeriod);
    }

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
