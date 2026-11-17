<?php

use App\Models\Fasilitas;

test('search combines chip selections with rupiah prices and required facilities', function () {
    $this->seed();
    $filters = [
        'jenis' => ['KIOS', 'RUKO'],
        'periode' => ['bulan'],
        'min' => 'Rp 1.000.000',
        'max' => 'Rp 7.000.000',
        'fasilitas' => Fasilitas::whereIn('nama_fasilitas', ['Listrik', 'Air'])->pluck('id_fasilitas')->all(),
    ];

    $this->get('/cari?'.http_build_query($filters))
        ->assertOk()
        ->assertSee('Kios Depan Pasar Pondok Labu')
        ->assertSee('Ruko 2 Lantai Tebet Barat')
        ->assertDontSee('Booth Bazar Kuliner Kemang');
    $this->get('/cari?jenis=BOOTH&periode=hari')
        ->assertOk()->assertSee('Booth Bazar Kuliner Kemang')->assertDontSee('Ruko 2 Lantai Tebet Barat');
    $this->get('/cari?periode[]=minggu')->assertOk()->assertSee('Menampilkan 0 lapak');
    $this->get('/cari')->assertOk()->assertSee('Menampilkan 3 lapak');
});
