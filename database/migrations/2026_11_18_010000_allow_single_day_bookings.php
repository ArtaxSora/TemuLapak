<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Booking::resolveConnection();
        if ($connection->getDriverName() === 'pgsql') {
            $connection->statement('ALTER TABLE booking DROP CONSTRAINT IF EXISTS booking_check');
            $connection->statement('ALTER TABLE booking ADD CONSTRAINT booking_tanggal_valid CHECK (tanggal_selesai >= tanggal_mulai)');
        }
    }

    public function down(): void
    {
        $connection = Booking::resolveConnection();
        if ($connection->getDriverName() === 'pgsql') {
            $connection->statement('ALTER TABLE booking ADD CONSTRAINT booking_check CHECK (tanggal_selesai > tanggal_mulai)');
            $connection->statement('ALTER TABLE booking DROP CONSTRAINT booking_tanggal_valid');
        }
    }
};
