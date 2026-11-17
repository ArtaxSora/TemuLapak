<?php

namespace App\Support;

use App\Models\FotoTempat;
use App\Models\LogStatusTempat;
use App\Models\Notifikasi;
use App\Models\Tempat;
use Throwable;

class Marketplace
{
    public static function notify(int $user, string $title, string $message, string $type): void
    {
        Tempat::resolveConnection()->afterCommit(function () use ($user, $title, $message, $type) {
            try {
                Notifikasi::create(['id_penerima' => $user, 'tipe' => $type, 'judul' => $title, 'isi' => $message]);
            } catch (Throwable $e) {
                report($e);
            }
        });
    }

    public static function status(Tempat $listing, string $new, ?int $actor): void
    {
        Tempat::query()->where('id_tempat', $listing->id_tempat)->update(['status_tempat' => $new, 'updated_at' => now()]);
        LogStatusTempat::create(['id_tempat' => $listing->id_tempat, 'status_lama' => $listing->status_tempat, 'status_baru' => $new, 'dibuat_oleh' => $actor]);
    }

    public static function expire(): void
    {
        $today = now('Asia/Jakarta')->toDateString();
        Tempat::resolveConnection()->transaction(function () use ($today) {
            $occupied = Tempat::query()->where('status_tempat', 'TERISI')
                ->whereDoesntHave('bookings', fn ($q) => $q->where('status_booking', 'DIKONFIRMASI')->where('tanggal_selesai', '>=', $today))
                ->orderBy('id_tempat')->lockForUpdate()->get();
            foreach ($occupied as $listing) {
                self::status($listing, 'TERSEDIA', null);
            }
        });
    }

    public static function cards()
    {
        return Tempat::query()->select('tempat.*')->addSelect(['foto' => FotoTempat::query()->select('url_foto')->whereColumn('foto_tempat.id_tempat', 'tempat.id_tempat')->orderBy('urutan')->orderBy('id_foto')->limit(1)]);
    }
}
