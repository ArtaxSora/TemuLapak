<?php

namespace Database\Seeders;

use App\Models\AturanSewa;
use App\Models\BiayaTambahan;
use App\Models\Fasilitas;
use App\Models\FotoTempat;
use App\Models\Tempat;
use App\Models\TempatFasilitas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Listrik', 'Air', 'Toilet', 'Parkir', 'Rolling door', 'Keamanan'] as $name) {
            Fasilitas::query()->insertOrIgnore(['nama_fasilitas' => $name]);
        }
        if (! app()->environment('local', 'testing')) {
            return;
        }

        Tempat::resolveConnection()->transaction(function () {
            $admin = $this->demoUser('Admin TemuLapak', 'admin@temulapak.test', 'ADMIN');
            $owner = $this->demoUser('Pemilik Demo', 'pemilik@temulapak.test', 'PEMILIK');
            $this->demoUser('Penyewa Demo', 'penyewa@temulapak.test', 'PENYEWA');
            foreach ([
                ['Booth Bazar Kuliner Kemang', 'BOOTH', 'Kemang, Jakarta Selatan', 750000, 'hari', 'imgFoto.png', ['Listrik', 'Air'], 'Kebersihan', 25000],
                ['Kios Depan Pasar Pondok Labu', 'KIOS', 'Pondok Labu, Jakarta Selatan', 1800000, 'bulan', 'imgFoto1.png', ['Air', 'Listrik', 'Rolling door'], 'Air', 75000],
                ['Ruko 2 Lantai Tebet Barat', 'RUKO', 'Tebet, Jakarta Selatan', 6500000, 'bulan', 'imgFoto2.png', ['Listrik', 'Air', 'Toilet', 'Parkir'], 'Keamanan', 150000],
            ] as [$name,$type,$location,$price,$unit,$photo,$facilities,$fee,$amount]) {
                if (Tempat::query()->where('id_pemilik', $owner->id)->where('nama_tempat', $name)->exists()) {
                    continue;
                }
                $id = Tempat::create(['id_pemilik' => $owner->id, 'id_admin' => $admin->id, 'nama_tempat' => $name, 'jenis_tempat' => $type, 'lokasi' => $location, 'harga_sewa' => $price, 'satuan_harga' => $unit, 'kondisi_tempat' => 'Siap digunakan. Kondisi dapat diperiksa saat survei.', 'deskripsi' => 'Listing contoh untuk mencoba alur sewa TemuLapak.', 'status_verifikasi' => 'DISETUJUI', 'diverifikasi_pada' => now()])->getKey();
                FotoTempat::create(['id_tempat' => $id, 'url_foto' => '/media/'.$photo]);
                BiayaTambahan::create(['id_tempat' => $id, 'nama_biaya' => $fee, 'nominal' => $amount]);
                AturanSewa::create(['id_tempat' => $id, 'durasi_minimal' => '1 '.$unit, 'perpanjangan' => 'Hubungi pemilik sebelum masa sewa berakhir.', 'sistem_pembayaran' => 'Pembayaran langsung kepada pemilik setelah konfirmasi.', 'ketentuan_pembatalan' => 'Pengajuan dapat dibatalkan sebelum dikonfirmasi.', 'denda_keterlambatan' => 'Sesuai kesepakatan tertulis dengan pemilik.']);
                foreach (Fasilitas::query()->whereIn('nama_fasilitas', $facilities)->pluck('id_fasilitas') as $fid) {
                    TempatFasilitas::query()->insert(['id_tempat' => $id, 'id_fasilitas' => $fid]);
                }
            }
        });
    }

    private function demoUser(string $name, string $email, string $role): User
    {
        $existing = User::where('email', $email)->first();
        if ($existing) {
            throw_unless($existing->peran === $role, \RuntimeException::class, 'Email demo sudah digunakan oleh peran lain.');

            return $existing;
        }
        $password = Str::password(18, symbols: false);
        $user = new User;
        $user->forceFill(['name' => $name, 'email' => $email, 'peran' => $role, 'no_telepon' => '0000000000', 'password' => $password])->save();
        $this->command?->line($email.'   '.$password);

        return $user;
    }
}
