<?php

use App\Models\Fasilitas;
use App\Models\Tempat;
use App\Models\TempatFasilitas;

test('removing a facility link only affects that listing and facility', function () {
    $this->seed();
    $listing = Tempat::firstOrFail();
    $other = Tempat::where('id_tempat', '!=', $listing->getKey())->firstOrFail();
    $facility = Fasilitas::firstOrFail();

    $listing->facilities()->syncWithoutDetaching([$facility->getKey()]);
    $other->facilities()->syncWithoutDetaching([$facility->getKey()]);
    $link = TempatFasilitas::where('id_tempat', $listing->getKey())
        ->where('id_fasilitas', $facility->getKey())->firstOrFail();
    $link->delete();

    expect($listing->facilities()->get()->modelKeys())->not->toContain($facility->getKey())
        ->and($other->facilities()->get()->modelKeys())->toContain($facility->getKey());
});
