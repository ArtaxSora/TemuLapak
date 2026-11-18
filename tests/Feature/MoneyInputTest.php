<?php

use App\Models\Tempat;
use App\Models\User;

test('formatted rental and additional fees save as numeric rupiah', function () {
    $this->seed();
    $place = Tempat::with('rules')->firstOrFail();
    $data = $place->only(['nama_tempat', 'jenis_tempat', 'lokasi', 'satuan_harga', 'kondisi_tempat']);
    $data += $place->rules->only(['durasi_minimal', 'perpanjangan', 'sistem_pembayaran', 'ketentuan_pembatalan', 'denda_keterlambatan']);
    $data += ['harga_sewa' => '1.250.000', 'biaya' => [['nama' => 'Air', 'nominal' => '25.000'], ['nama' => 'Parkir', 'nominal' => '0']]];
    $this->actingAs($place->pemilik)->post('/pemilik/tempat/'.$place->getKey(), $data)->assertSessionHasNoErrors();
    expect((float) $place->fresh()->harga_sewa)->toBe(1250000.0);
    expect((float) $place->fees()->where('nama_biaya', 'Air')->value('nominal'))->toBe(25000.0);
    $this->get('/pemilik/tempat/'.$place->getKey().'/edit')->assertOk()->assertSee('1.250.000')->assertSee('25.000');
    $data['biaya'][0]['nominal'] = '25rb';
    $this->post('/pemilik/tempat/'.$place->getKey(), $data)->assertSessionHasErrors('biaya.0.nominal');
    expect((float) $place->fees()->where('nama_biaya', 'Air')->value('nominal'))->toBe(25000.0);
});

test('money fields reject letters negative values exponents and invalid grouping', function ($value) {
    $this->seed();
    $owner = User::where('peran', 'PEMILIK')->firstOrFail();
    $this->actingAs($owner)->post('/pemilik/tempat', ['harga_sewa' => $value])->assertSessionHasErrors('harga_sewa');
    $this->get('/cari?'.http_build_query(['min' => $value]))->assertSessionHasErrors('min');
})->with(['abc', '10rb', '1e6', '-1000', '1.25.000', '1,5', '100000000000000']);
