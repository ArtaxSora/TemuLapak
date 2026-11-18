<?php

use App\Models\Booking;
use App\Models\Tempat;
use App\Support\BookingEstimate;

test('mixed fee periods and estimated usage are calculated independently', function () {
    $terms = ['harga' => 1800000, 'satuan' => 'bulan', 'biaya' => [
        ['nama_biaya' => 'Kebersihan', 'nominal' => 10000, 'periode' => 'hari', 'metode' => 'tetap'],
        ['nama_biaya' => 'Keamanan', 'nominal' => 50000, 'periode' => 'minggu', 'metode' => 'tetap'],
        ['nama_biaya' => 'Air', 'nominal' => 75000, 'periode' => 'bulan', 'metode' => 'tetap'],
        ['nama_biaya' => 'Listrik', 'nominal' => 1500, 'periode' => 'bulan', 'metode' => 'pemakaian', 'satuan_pemakaian' => 'kWh', 'perkiraan_pemakaian' => 100],
        ['nama_biaya' => 'Parkir', 'nominal' => 9999, 'periode' => 'hari', 'metode' => 'termasuk'],
    ]];
    $estimate = BookingEstimate::calculate('2026-12-01', '2027-02-28', $terms);
    expect($estimate['units'])->toBe(3)->and($estimate['lines'][1]['units'])->toBe(90)->and($estimate['lines'][2]['units'])->toBe(13)->and($estimate['lines'][3]['total'])->toBe(225000.0)->and($estimate['lines'][4]['total'])->toBe(450000.0)->and($estimate['lines'][5]['total'])->toBe(0)->and($estimate['total'])->toBe(7625000.0);
    $terms['biaya'][3]['perkiraan_pemakaian'] = null;
    $unknown = BookingEstimate::calculate('2026-12-01', '2027-02-28', $terms);
    expect($unknown['unknown_usage'])->toBeTrue()->and($unknown['lines'][4]['total'])->toBeNull()->and($unknown['total'])->toBe(7175000.0);
});

test('owner saves custom fee periods and removes them without automatic utilities', function () {
    $this->seed();
    $place = Tempat::with('rules')->firstOrFail();
    $this->actingAs($place->pemilik)->get('/pemilik/tempat/baru')->assertOk()->assertDontSee('data-utility', false)->assertDontSee('biaya[listrik]', false)->assertDontSee('biaya[air]', false);
    $data = $place->only(['nama_tempat', 'jenis_tempat', 'lokasi', 'satuan_harga', 'kondisi_tempat', 'harga_sewa']);
    $data['harga_sewa'] = '1.800.000';
    $data += $place->rules->only(['durasi_minimal', 'perpanjangan', 'sistem_pembayaran', 'ketentuan_pembatalan', 'denda_keterlambatan']);
    $data['biaya'] = [
        'a' => ['nama' => 'Air', 'nominal' => '75.000', 'periode' => 'bulan', 'metode' => 'tetap'],
        'b' => ['nama' => 'Keamanan', 'nominal' => '50.000', 'periode' => 'minggu', 'metode' => 'tetap'],
        'c' => ['nama' => 'Listrik', 'nominal' => '1.500', 'periode' => 'hari', 'metode' => 'pemakaian', 'satuan_pemakaian' => 'kWh', 'perkiraan_pemakaian' => '10.5'],
        'd' => ['nama' => 'Parkir', 'metode' => 'termasuk'],
    ];
    $url = '/pemilik/tempat/'.$place->getKey();
    $this->post($url, $data)->assertSessionHasNoErrors();
    expect($place->fees()->where('nama_biaya', 'Keamanan')->first()->periode)->toBe('minggu');
    $electric = $place->fees()->where('nama_biaya', 'Listrik')->first();
    expect($electric->metode)->toBe('pemakaian')->and((float) $electric->perkiraan_pemakaian)->toBe(10.5);
    expect((float) $place->fees()->where('nama_biaya', 'Parkir')->value('nominal'))->toBe(0.0);
    $this->get($url.'/edit')->assertOk()->assertSee('50.000');
    $bad = $data;
    $bad['biaya']['a']['periode'] = 'jam';
    $this->post($url, $bad)->assertSessionHasErrors('biaya.a.periode');
    $bad = $data;
    unset($bad['biaya']['c']['satuan_pemakaian']);
    $this->post($url, $bad)->assertSessionHasErrors('biaya.c.nominal');
    $data['biaya'] = [];
    $this->post($url, $data)->assertSessionHasNoErrors();
    expect($place->fees()->count())->toBe(0);
});

test('saved booking estimates remain unchanged when fee periods change', function () {
    $terms = ['harga' => 100000, 'satuan' => 'hari', 'biaya' => [['nama_biaya' => 'Air', 'nominal' => 7000, 'periode' => 'minggu']]];
    $terms['estimate'] = BookingEstimate::calculate('2026-12-01', '2026-12-08', $terms);
    $booking = new Booking(['tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-08', 'ringkasan_sewa' => $terms]);
    $changed = $terms;
    $changed['biaya'][0]['periode'] = 'hari';
    $booking->ringkasan_sewa = $changed;
    expect((float) $booking->costEstimate()['total'])->toBe(814000.0)->and($booking->costEstimate()['lines'][1]['unit'])->toBe('minggu');
});
