<?php

use App\Models\Booking;
use App\Models\Notifikasi;
use App\Models\Tempat;
use App\Models\User;

test('owners and tenants can only process their own pending bookings', function () {
    $this->seed();
    $owner = User::where('peran', 'PEMILIK')->firstOrFail();
    $tenant = User::where('peran', 'PENYEWA')->firstOrFail();
    $item = Tempat::query()->first();
    $otherOwner = User::factory()->create(['peran' => 'PEMILIK'])->refresh();
    $dates = ['tanggal_mulai' => now()->addDay()->toDateString(), 'tanggal_selesai' => now()->addDays(3)->toDateString()];
    $this->actingAs($tenant)->post('/tempat/'.$item->id_tempat.'/booking', $dates)->assertSessionHasNoErrors();
    $bid = Booking::query()->value('id_booking');
    $this->actingAs($otherOwner)->post("/pemilik/booking/$bid/keputusan", ['status' => 'DIKONFIRMASI'])->assertForbidden();
    $this->get('/pemilik/tempat/'.$item->id_tempat.'/edit')->assertNotFound();
    $this->actingAs($owner)->get('/pemilik/booking/'.$bid)->assertOk();
    $this->post("/pemilik/booking/$bid/keputusan", ['status' => 'DITOLAK'])->assertSessionHasErrors('alasan');
    $this->post("/pemilik/booking/$bid/keputusan", ['status' => 'DITOLAK', 'alasan' => 'Renovasi'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('notifikasi', ['id_penerima' => $tenant->id, 'judul' => 'Booking ditolak']);
    $this->assertDatabaseHas('tempat', ['id_tempat' => $item->id_tempat, 'status_tempat' => 'TERSEDIA']);
    $this->actingAs($tenant)->post('/tempat/'.$item->id_tempat.'/booking', $dates)->assertSessionHasNoErrors();
    $next = Booking::query()->max('id_booking');
    $this->post("/booking/$next/batal")->assertSessionHasNoErrors();
    $this->assertDatabaseHas('booking', ['id_booking' => $next, 'status_booking' => 'DIBATALKAN']);
    $this->post('/notifikasi/baca')->assertSessionHasNoErrors();
    expect(Notifikasi::query()->where('id_penerima', $tenant->id)->whereNull('dibaca_pada')->count())->toBe(0);
    $this->actingAs($owner)->post("/pemilik/booking/$next/keputusan", ['status' => 'DIKONFIRMASI'])->assertSessionHasErrors('booking');
});
