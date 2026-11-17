<?php

use App\Models\Booking;
use App\Models\FotoTempat;
use App\Models\Tempat;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('a listing goes from owner submission to verification, booking and review', function () {
    $this->seed();
    $owner = User::where('peran', 'PEMILIK')->firstOrFail();
    $admin = User::where('peran', 'ADMIN')->firstOrFail();
    $tenant = User::where('peran', 'PENYEWA')->firstOrFail();
    $other = User::factory()->create()->refresh();
    $photo = UploadedFile::fake()->createWithContent('lapak.png', file_get_contents(public_path('media/imgFoto.png')));
    $this->actingAs($owner)->get('/pemilik/tempat/baru')->assertOk();
    $this->post('/pemilik/tempat', ['nama_tempat' => 'Kios pengujian', 'jenis_tempat' => 'KIOS', 'lokasi' => 'Kemang', 'harga_sewa' => 350000, 'satuan_harga' => 'hari', 'kondisi_tempat' => 'Baik', 'durasi_minimal' => '1 hari', 'perpanjangan' => 'Hubungi pemilik', 'sistem_pembayaran' => 'Tunai', 'ketentuan_pembatalan' => 'Sebelum konfirmasi', 'denda_keterlambatan' => 'Tidak ada', 'foto' => [$photo], 'biaya' => [['nama' => 'Air', 'nominal' => 10000]]])->assertSessionHasNoErrors()->assertRedirect('/pemilik/tempat');
    $id = Tempat::query()->where('nama_tempat', 'Kios pengujian')->value('id_tempat');
    $path = public_path(FotoTempat::query()->where('id_tempat', $id)->value('url_foto'));
    try {
        $this->actingAs($tenant)->get('/tempat/'.$id)->assertNotFound();
        $this->get('/admin/verifikasi')->assertForbidden();
        $this->actingAs($admin)->get('/admin/verifikasi')->assertOk()->assertSee('Kios pengujian');
        $this->get('/tempat/'.$id)->assertOk();
        $this->post('/admin/verifikasi/'.$id, ['status' => 'DISETUJUI'])->assertSessionHasNoErrors();
        $this->actingAs($tenant)->get('/cari?lokasi=Kemang')->assertOk()->assertSee('Kios pengujian');
        $this->get("/tempat/$id/booking")->assertOk();
        $dates = ['tanggal_mulai' => now()->addDays(2)->toDateString(), 'tanggal_selesai' => now()->addDays(5)->toDateString()];
        $this->post("/tempat/$id/booking", $dates)->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($other)->post("/tempat/$id/booking", $dates)->assertSessionHasErrors('booking');
        $bid = Booking::query()->where('id_tempat', $id)->value('id_booking');
        $this->get("/booking/$bid")->assertNotFound();
        $this->post("/booking/$bid/batal")->assertForbidden();
        $this->actingAs($owner)->get('/pemilik/booking')->assertOk()->assertSee('Kios pengujian');
        $this->post("/pemilik/booking/$bid/keputusan", ['status' => 'DIKONFIRMASI'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tempat', ['id_tempat' => $id, 'status_tempat' => 'TERISI']);
        $this->actingAs($tenant)->get('/booking/'.$bid)->assertOk()->assertSee('Ringkasan biaya');
        $this->get('/booking')->assertOk()->assertSee('Dikonfirmasi');
        $this->post("/booking/$bid/batal")->assertSessionHasErrors('booking');
        $this->travel(7)->days();
        $this->get('/cari')->assertOk();
        $this->assertDatabaseHas('tempat', ['id_tempat' => $id, 'status_tempat' => 'TERSEDIA']);
        $this->post("/booking/$bid/ulasan", ['rating' => 5, 'komentar' => 'Lokasi sesuai.'])->assertSessionHasNoErrors();
        $this->post("/booking/$bid/ulasan", ['rating' => 4])->assertSessionHasErrors('rating');
        $this->get('/tempat/'.$id)->assertSee('Lokasi sesuai.');
        $this->get('/notifikasi')->assertOk();
        $this->actingAs($owner)->get('/pemilik/ulasan')->assertOk()->assertSee('Lokasi sesuai.');
        $this->get('/pemilik/laporan')->assertOk();
        $this->actingAs($admin)->get('/admin/pengguna')->assertOk();
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

test('registration restricts privileged roles and deactivated accounts lose access', function () {
    $data = ['name' => 'Pengguna', 'no_telepon' => '08123456789', 'email' => 'tester@example.test', 'password' => 'testSecret2026', 'password_confirmation' => 'testSecret2026'];
    $this->post('/daftar', $data + ['peran' => 'ADMIN'])->assertSessionHasErrors('peran');
    $this->post('/daftar', $data + ['peran' => 'PEMILIK'])->assertRedirect('/dashboard');
    $this->get('/profil')->assertOk();
    $this->post('/keluar')->assertRedirect('/');
    $this->post('/masuk', ['email' => $data['email'], 'password' => $data['password']])->assertRedirect('/dashboard');
    User::query()->where('email', $data['email'])->update(['aktif' => false]);
    auth()->forgetUser();
    $this->get('/pemilik/tempat')->assertRedirect('/masuk');
});
