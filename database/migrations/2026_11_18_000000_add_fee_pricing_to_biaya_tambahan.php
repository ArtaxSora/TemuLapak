<?php

use App\Models\BiayaTambahan;
use App\Models\Fasilitas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biaya_tambahan', function (Blueprint $table) {
            $table->string('periode', 10)->nullable();
            $table->string('metode', 15)->default('tetap');
            $table->string('satuan_pemakaian', 30)->nullable();
            $table->decimal('perkiraan_pemakaian', 12, 2)->nullable();
        });
        BiayaTambahan::with('tempat')->chunkById(100, function ($fees) {
            foreach ($fees as $fee) {
                $fee->update(['periode' => $fee->tempat->satuan_harga]);
            }
        }, 'id_biaya');
        Fasilitas::firstOrCreate(['nama_fasilitas' => 'Tenda']);
    }

    public function down(): void
    {
        Schema::table('biaya_tambahan', function (Blueprint $table) {
            $table->dropColumn(['periode', 'metode', 'satuan_pemakaian', 'perkiraan_pemakaian']);
        });
    }
};
