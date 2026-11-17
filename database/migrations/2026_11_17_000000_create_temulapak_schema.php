<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('peran', ['PENYEWA', 'PEMILIK', 'ADMIN'])->default('PENYEWA')->index();
            $table->string('no_telepon', 32)->nullable();
            $table->boolean('aktif')->default(true);
        });

        Schema::create('tempat', function (Blueprint $table) {
            $table->id('id_tempat');
            $table->foreignId('id_pemilik')->constrained('users')->restrictOnDelete();
            $table->foreignId('id_admin')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('nama_tempat');
            $table->enum('jenis_tempat', ['BOOTH', 'KIOS', 'RUKO']);
            $table->string('lokasi');
            $table->decimal('harga_sewa', 16, 2);
            $table->string('satuan_harga', 30);
            $table->text('kondisi_tempat');
            $table->text('deskripsi')->nullable();
            $table->enum('status_verifikasi', ['MENUNGGU', 'DISETUJUI', 'DITOLAK'])->default('MENUNGGU');
            $table->enum('status_tempat', ['TERSEDIA', 'MENUNGGU_KONFIRMASI', 'TERISI'])->default('TERSEDIA');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestampTz('diverifikasi_pada')->nullable();
            $table->timestampsTz();
            $table->index(['status_verifikasi', 'jenis_tempat', 'harga_sewa']);
            $table->index(['id_pemilik', 'status_verifikasi']);
        });

        Schema::create('foto_tempat', function (Blueprint $table) {
            $table->id('id_foto');
            $table->foreignId('id_tempat')->constrained('tempat', 'id_tempat')->cascadeOnDelete();
            $table->text('url_foto');
            $table->integer('urutan')->default(0);
            $table->index(['id_tempat', 'urutan']);
        });

        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id('id_fasilitas');
            $table->string('nama_fasilitas')->unique();
        });

        Schema::create('tempat_fasilitas', function (Blueprint $table) {
            $table->foreignId('id_tempat')->constrained('tempat', 'id_tempat')->cascadeOnDelete();
            $table->foreignId('id_fasilitas')->constrained('fasilitas', 'id_fasilitas')->cascadeOnDelete();
            $table->primary(['id_tempat', 'id_fasilitas']);
            $table->index(['id_fasilitas', 'id_tempat']);
        });

        Schema::create('biaya_tambahan', function (Blueprint $table) {
            $table->id('id_biaya');
            $table->foreignId('id_tempat')->index()->constrained('tempat', 'id_tempat')->cascadeOnDelete();
            $table->string('nama_biaya');
            $table->decimal('nominal', 16, 2);
        });

        Schema::create('aturan_sewa', function (Blueprint $table) {
            $table->foreignId('id_tempat')->primary()->constrained('tempat', 'id_tempat')->cascadeOnDelete();
            $table->string('durasi_minimal');
            $table->text('perpanjangan');
            $table->text('sistem_pembayaran');
            $table->text('ketentuan_pembatalan');
            $table->text('denda_keterlambatan');
        });

        Schema::create('booking', function (Blueprint $table) {
            $table->id('id_booking');
            $table->foreignId('id_tempat')->constrained('tempat', 'id_tempat')->restrictOnDelete();
            $table->foreignId('id_penyewa')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status_booking', ['MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'DITOLAK', 'DIBATALKAN'])->default('MENUNGGU_KONFIRMASI');
            $table->text('catatan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestampsTz();
            $table->index(['id_tempat', 'status_booking', 'tanggal_mulai']);
            $table->index(['id_penyewa', 'created_at']);
        });

        Schema::create('ulasan', function (Blueprint $table) {
            $table->id('id_ulasan');
            $table->foreignId('id_booking')->unique()->constrained('booking', 'id_booking')->restrictOnDelete();
            $table->smallInteger('rating');
            $table->text('komentar')->nullable();
            $table->timestampsTz();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_penerima')->constrained('users')->cascadeOnDelete();
            $table->string('tipe');
            $table->string('judul');
            $table->text('isi');
            $table->timestampTz('dibaca_pada')->nullable();
            $table->timestampsTz();
            $table->index(['id_penerima', 'dibaca_pada', 'created_at']);
        });

        Schema::create('log_status_tempat', function (Blueprint $table) {
            $table->id('id_log');
            $table->foreignId('id_tempat')->constrained('tempat', 'id_tempat')->restrictOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status_lama');
            $table->string('status_baru');
            $table->timestampTz('waktu')->useCurrent();
            $table->index(['id_tempat', 'waktu']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tempat ADD CHECK (harga_sewa >= 0)');
            DB::statement('ALTER TABLE biaya_tambahan ADD CHECK (nominal >= 0)');
            DB::statement('ALTER TABLE ulasan ADD CHECK (rating BETWEEN 1 AND 5)');
            DB::statement('ALTER TABLE booking ADD CHECK (tanggal_selesai > tanggal_mulai)');
            // A singleton range identifies each listing without requiring btree_gist.
            DB::statement(<<<'SQL'
                ALTER TABLE booking ADD CONSTRAINT booking_aktif_tidak_bentrok
                EXCLUDE USING gist (
                    int8range(id_tempat, id_tempat, '[]') WITH &&,
                    daterange(tanggal_mulai, tanggal_selesai, '[]') WITH &&
                ) WHERE (status_booking IN ('MENUNGGU_KONFIRMASI', 'DIKONFIRMASI'))
                SQL);
        }
    }

    public function down(): void
    {
        foreach (['log_status_tempat', 'notifikasi', 'ulasan', 'booking', 'aturan_sewa',
            'biaya_tambahan', 'tempat_fasilitas', 'fasilitas', 'foto_tempat', 'tempat'] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['peran']);
            $table->dropColumn(['peran', 'no_telepon', 'aktif']);
        });
    }
};
