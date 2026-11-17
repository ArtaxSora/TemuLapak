<?php

use App\Models\Fasilitas;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['Listrik', 'Air', 'Parkir motor', 'Parkir mobil', 'Toilet', 'Rolling door', 'Etalase', 'Meja & kursi'] as $name) {
            Fasilitas::firstOrCreate(['nama_fasilitas' => $name]);
        }
    }

    public function down(): void
    {
        // Existing listings may use these shared facility records.
    }
};
