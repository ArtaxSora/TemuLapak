<?php

use App\Models\Booking;
use App\Models\Tempat;
use App\Models\User;
use App\Support\BookingEstimate;

test('calendar periods calculate each cost and enforce the actual minimum duration', function () {
    $terms = ['harga' => 1800000, 'satuan' => 'bulan', 'durasi_minimal' => '3 bulan', 'biaya' => [
        ['nama_biaya' => 'Listrik (estimasi)', 'nominal' => 150000],
        ['nama_biaya' => 'Air', 'nominal' => 75000],
        ['nama_biaya' => 'Iuran kebersihan', 'nominal' => 50000],
    ]];
    $quote = BookingEstimate::calculate('2026-12-01', '2027-02-28', $terms);
    expect($quote['units'])->toBe(3)->and($quote['total'])->toBe(6225000.0)->and($quote['eligible'])->toBeTrue()->and($quote['rounded'])->toBeFalse();
    $short = BookingEstimate::calculate('2026-12-01', '2027-02-27', $terms);
    expect($short['units'])->toBe(3)->and($short['eligible'])->toBeFalse()->and($short['minimum_end'])->toBe('2027-02-28');
    $leap = BookingEstimate::calculate('2028-01-31', '2028-02-28', array_replace($terms, ['durasi_minimal' => '1 bulan']));
    expect($leap['units'])->toBe(1)->and($leap['eligible'])->toBeTrue();
    $extra = BookingEstimate::calculate('2026-12-01', '2027-03-01', $terms);
    expect($extra['units'])->toBe(4)->and($extra['rounded'])->toBeTrue();
});

test('live estimate matches saved booking and remains fixed after listing prices change', function () {
    $this->travelTo(now()->setDate(2026, 11, 18));
    $this->seed();
    $place = Tempat::where('jenis_tempat', 'KIOS')->firstOrFail();
    $place->rules()->update(['durasi_minimal' => '3 bulan']);
    $tenant = User::where('peran', 'PENYEWA')->firstOrFail();
    $dates = ['tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2027-02-28'];
    $this->actingAs($tenant)->get('/tempat/'.$place->getKey().'/booking')->assertOk()->assertSee('Ajukan Booking');
    $url = '/tempat/'.$place->getKey().'/estimasi?';
    $quote = $this->getJson($url.http_build_query($dates))->assertOk()->assertJsonPath('units', 3)->assertJsonPath('eligible', true)->json();
    $short = array_replace($dates, ['tanggal_selesai' => '2027-02-27']);
    $this->getJson($url.http_build_query($short))->assertOk()->assertJsonPath('eligible', false);
    $this->post('/tempat/'.$place->getKey().'/booking', $short)->assertSessionHasErrors('tanggal_selesai');
    expect(Booking::count())->toBe(0);
    $this->post('/tempat/'.$place->getKey().'/booking', $dates + ['total' => 1, 'harga' => 1])->assertSessionHasNoErrors()->assertRedirect();
    $booking = Booking::firstOrFail();
    expect((float) $booking->costEstimate()['total'])->toBe((float) $quote['total']);
    $place->update(['harga_sewa' => 9000000]);
    $place->fees()->update(['nominal' => 800000]);
    expect((float) $booking->fresh()->costEstimate()['total'])->toBe((float) $quote['total']);
    $this->get('/booking/'.$booking->getKey())->assertOk()->assertSee('Ringkasan pengajuan')->assertSee('Ringkasan biaya')->assertSee('Rp '.number_format($quote['total'], 0, ',', '.'));
    $this->getJson($url.http_build_query($dates))->assertStatus(409);
    $other = User::factory()->create(['peran' => 'PENYEWA'])->refresh();
    $this->actingAs($other)->get('/booking/'.$booking->getKey())->assertNotFound();
});

test('one day bookings cost one day and invalid dates are rejected', function () {
    $this->travelTo(now()->setDate(2026, 11, 18));
    $this->seed();
    $place = Tempat::where('jenis_tempat', 'BOOTH')->firstOrFail();
    $dates = ['tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-01'];
    $this->actingAs($place->pemilik)->getJson('/tempat/'.$place->getKey().'/estimasi?'.http_build_query($dates))->assertForbidden();
    $this->actingAs(User::where('peran', 'PENYEWA')->firstOrFail());
    $this->getJson('/tempat/'.$place->getKey().'/estimasi?'.http_build_query($dates))->assertOk()->assertJsonPath('units', 1)->assertJsonPath('total', 775000);
    $this->getJson('/tempat/'.$place->getKey().'/estimasi?tanggal_mulai=2026-12-02&tanggal_selesai=2026-12-01')->assertUnprocessable()->assertJsonValidationErrors('tanggal_selesai');
    $this->getJson('/tempat/'.$place->getKey().'/estimasi?tanggal_mulai=2026-01-01&tanggal_selesai=2026-12-01')->assertUnprocessable()->assertJsonValidationErrors('tanggal_mulai');
    $this->post('/tempat/'.$place->getKey().'/booking', $dates)->assertSessionHasNoErrors();
    expect(Booking::count())->toBe(1);
});
