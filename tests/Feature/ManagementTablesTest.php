<?php

use App\Models\Booking;
use App\Models\Tempat;
use App\Models\User;

test('rental estimates use inclusive dates and saved rates', function ($start, $end, $unit, $count) {
    $booking = new Booking(['tanggal_mulai' => $start, 'tanggal_selesai' => $end, 'ringkasan_sewa' => ['harga' => 100000, 'satuan' => $unit]]);
    expect($booking->rentalEstimate())->toBe(['units' => $count, 'unit' => $unit, 'total' => (float) (100000 * $count)]);
})->with([
    ['2026-11-12', '2026-11-14', 'hari', 3],
    ['2026-11-12', '2026-11-12', 'hari', 1],
    ['2026-11-12', '2026-11-18', 'minggu', 1],
    ['2026-11-12', '2026-11-19', 'minggu', 2],
    ['2026-12-01', '2027-02-28', 'bulan', 3],
    ['2026-12-15', '2027-03-14', 'bulan', 3],
    ['2026-12-15', '2027-03-15', 'bulan', 4],
    ['2026-01-31', '2026-02-27', 'bulan', 1],
    ['2026-01-01', '2026-12-31', 'tahun', 1],
]);

test('owner table filters statuses and excludes other owners bookings', function () {
    $this->seed();
    $place = Tempat::firstOrFail();
    $tenant = User::where('peran', 'PENYEWA')->firstOrFail();
    Booking::create(['id_tempat' => $place->getKey(), 'id_penyewa' => $tenant->id, 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2027-02-28', 'status_booking' => 'MENUNGGU_KONFIRMASI', 'ringkasan_sewa' => ['harga' => 2000000, 'satuan' => 'bulan']]);
    $this->actingAs($place->pemilik)->get('/pemilik/booking?status=MENUNGGU_KONFIRMASI')->assertOk()->assertSee('Rp 6.000.000')->assertSee('3 bulan')->assertSee('1 Des 2026')->assertSee('Lihat Detail');
    $this->get('/pemilik/booking?status=DITOLAK')->assertOk()->assertDontSee('Rp 6.000.000');
    $other = User::factory()->create(['peran' => 'PEMILIK'])->refresh();
    $this->actingAs($other)->get('/pemilik/booking')->assertOk()->assertDontSee('Rp 6.000.000');
});

test('verification table reports broken photos and prevents their approval', function () {
    $this->seed();
    $place = Tempat::firstOrFail();
    $place->update(['status_verifikasi' => 'MENUNGGU']);
    $admin = User::where('peran', 'ADMIN')->firstOrFail();
    $this->actingAs($admin)->get('/admin/verifikasi')->assertOk()->assertSee('Data lengkap')->assertSee('Menunggu verifikasi');
    $place->photos()->update(['url_foto' => '/uploads/lapak/missing-test-image.png']);
    $this->get('/admin/verifikasi')->assertOk()->assertSee('Data rusak: foto tidak bisa dibuka');
    $this->post('/admin/verifikasi/'.$place->getKey(), ['status' => 'DISETUJUI'])->assertSessionHasErrors('status');
    expect($place->fresh()->status_verifikasi)->toBe('MENUNGGU');
    $this->post('/admin/verifikasi/'.$place->getKey(), ['status' => 'DITOLAK', 'catatan' => 'Foto rusak'])->assertSessionHasNoErrors();
});
