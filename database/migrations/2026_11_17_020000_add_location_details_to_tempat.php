<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempat', function (Blueprint $table) {
            $table->string('alamat_lengkap')->nullable();
            $table->string('ukuran', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tempat', fn (Blueprint $table) => $table->dropColumn(['alamat_lengkap', 'ukuran']));
    }
};
