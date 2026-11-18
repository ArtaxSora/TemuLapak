<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tempat extends Model
{
    protected $fillable = [
        'id_pemilik', 'id_admin', 'alamat_lengkap', 'ukuran', 'nama_tempat', 'jenis_tempat', 'lokasi', 'harga_sewa', 'satuan_harga', 'kondisi_tempat', 'deskripsi', 'status_verifikasi', 'status_tempat', 'catatan_verifikasi', 'diverifikasi_pada',
    ];

    protected $table = 'tempat';

    protected $primaryKey = 'id_tempat';

    public function bookingTerms(): array
    {
        return [
            'harga' => $this->harga_sewa, 'satuan' => $this->satuan_harga,
            'biaya' => $this->fees->map(fn ($fee) => $fee->only(['nama_biaya', 'nominal', 'periode', 'metode', 'satuan_pemakaian', 'perkiraan_pemakaian']))->all(),
            'durasi_minimal' => $this->rules?->durasi_minimal,
            'pembayaran' => $this->rules?->sistem_pembayaran,
            'pembatalan' => $this->rules?->ketentuan_pembatalan,
        ];
    }

    public function verificationIssue(): ?string
    {
        if ($this->photos->isEmpty()) {
            return 'Belum ada foto';
        }
        foreach ($this->photos as $photo) {
            $root = realpath(public_path());
            $path = realpath(public_path(ltrim($photo->url_foto, '/')));
            if (! $path || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR) || ! is_file($path) || ! @getimagesize($path)) {
                return 'Data rusak: foto tidak bisa dibuka';
            }
        }
        if (! $this->rules) {
            return 'Aturan sewa belum lengkap';
        }

        return null;
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pemilik');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(FotoTempat::class, 'id_tempat')->orderBy('urutan')->orderBy('id_foto');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(BiayaTambahan::class, 'id_tempat')->orderBy('id_biaya');
    }

    public function rules(): HasOne
    {
        return $this->hasOne(AturanSewa::class, 'id_tempat');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_tempat');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Fasilitas::class, 'tempat_fasilitas', 'id_tempat', 'id_fasilitas')->using(TempatFasilitas::class)->orderBy('nama_fasilitas');
    }
}
