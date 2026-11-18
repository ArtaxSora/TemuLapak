<?php

namespace App\Models;

use App\Support\BookingEstimate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'id_tempat', 'id_penyewa', 'tanggal_mulai', 'tanggal_selesai', 'status_booking', 'catatan', 'alasan_penolakan', 'ringkasan_sewa',
    ];

    protected $table = 'booking';

    protected $primaryKey = 'id_booking';

    protected function casts(): array
    {
        return ['ringkasan_sewa' => 'array'];
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->peran === 'PEMILIK') {
            return $query->whereHas('tempat', fn ($q) => $q->where('id_pemilik', $user->id));
        }

        return $query->where('id_penyewa', $user->id);
    }

    public function rentalEstimate(): array
    {
        $estimate = $this->costEstimate();

        return ['units' => $estimate['units'], 'unit' => $estimate['unit'], 'total' => $estimate['rental_total']];
    }

    public function costEstimate(): array
    {
        $terms = $this->ringkasan_sewa ?? $this->tempat->bookingTerms();

        return $terms['estimate'] ?? BookingEstimate::calculate($this->tanggal_mulai, $this->tanggal_selesai, $terms);
    }

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }

    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_penyewa');
    }

    public function ulasan(): HasOne
    {
        return $this->hasOne(Ulasan::class, 'id_booking');
    }
}
