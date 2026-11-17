<?php

namespace App\Http\Controllers;

use App\Models\AturanSewa;
use App\Models\BiayaTambahan;
use App\Models\Booking;
use App\Models\Tempat;
use App\Models\Ulasan;
use App\Support\Marketplace;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function requestBooking(Request $r, int $id)
    {
        $dates = $r->validate(['tanggal_mulai' => 'required|date_format:Y-m-d|after_or_equal:'.now('Asia/Jakarta')->toDateString(), 'tanggal_selesai' => 'required|date_format:Y-m-d|after:tanggal_mulai', 'catatan' => 'nullable|string|max:2000']);
        Marketplace::expire();
        $bookingId = Tempat::resolveConnection()->transaction(function () use ($r, $id, $dates) {
            $item = Tempat::query()->where('id_tempat', $id)->lockForUpdate()->firstOrFail();
            $overlap = Booking::query()->where('id_tempat', $id)->whereIn('status_booking', ['MENUNGGU_KONFIRMASI', 'DIKONFIRMASI'])->where('tanggal_mulai', '<=', $dates['tanggal_selesai'])->where('tanggal_selesai', '>=', $dates['tanggal_mulai'])->exists();
            if ($item->status_verifikasi !== 'DISETUJUI' || $item->status_tempat !== 'TERSEDIA' || $overlap) {
                throw ValidationException::withMessages(['booking' => 'Tempat sudah tidak tersedia.']);
            }
            $rules = AturanSewa::query()->where('id_tempat', $id)->first();
            $terms = ['harga' => $item->harga_sewa, 'satuan' => $item->satuan_harga, 'biaya' => BiayaTambahan::query()->where('id_tempat', $id)->get(['nama_biaya', 'nominal']), 'pembayaran' => $rules?->sistem_pembayaran, 'pembatalan' => $rules?->ketentuan_pembatalan];
            $bid = Booking::create($dates + ['ringkasan_sewa' => $terms] + ['id_tempat' => $id, 'id_penyewa' => $r->user()->id, 'status_booking' => 'MENUNGGU_KONFIRMASI'])->getKey();
            Marketplace::status($item, 'MENUNGGU_KONFIRMASI', $r->user()->id);
            Marketplace::notify($item->id_pemilik, 'Booking baru', $r->user()->name.' mengajukan booking untuk '.$item->nama_tempat.'.', 'BOOKING');

            return $bid;
        });

        return redirect('/booking/'.$bookingId)->with('submitted', true);
    }

    public function detail(Request $r, int $id)
    {
        $owner = $r->user()->peran === 'PEMILIK';
        $b = Booking::forUser($r->user())->with(['tempat', 'penyewa'])->findOrFail($id);

        return view('market.booking-detail', ['b' => $b, 'owner' => $owner, 'terms' => $b->ringkasan_sewa]);
    }

    public function history(Request $r)
    {
        $owner = $r->user()->peran === 'PEMILIK';
        $q = Booking::forUser($r->user())->with(['tempat.rules', 'tempat.photos', 'penyewa', 'ulasan']);
        $r->validate(['dari' => 'nullable|date_format:Y-m-d', 'sampai' => 'nullable|date_format:Y-m-d']);
        if ($r->filled('status')) {
            $q->where('status_booking', $r->status);
        }
        if ($r->filled('dari')) {
            $q->where('tanggal_selesai', '>=', $r->dari);
        }
        if ($r->filled('sampai')) {
            $q->where('tanggal_mulai', '<=', $r->sampai);
        }
        if ($r->filled('tempat')) {
            $q->whereHas('tempat', fn ($q) => $q->whereLike('nama_tempat', '%'.$r->tempat.'%'));
        }

        return view('market.bookings', ['items' => $q->orderByDesc('id_booking')->paginate(20)->withQueryString(), 'owner' => $owner, 'report' => $r->is('pemilik/laporan')]);
    }

    public function decide(Request $r, int $id)
    {
        $data = $r->validate(['status' => 'required|in:DIKONFIRMASI,DITOLAK', 'alasan' => 'required_if:status,DITOLAK|nullable|string|max:2000']);

        return $this->change($r, $id, $data['status'], $data['alasan'] ?? null);
    }

    public function cancel(Request $r, int $id)
    {
        return $this->change($r, $id, 'DIBATALKAN', null);
    }

    private function change(Request $r, int $id, string $status, ?string $reason)
    {
        Tempat::resolveConnection()->transaction(function () use ($r, $id, $status, $reason) {
            $placeId = Booking::query()->where('id_booking', $id)->firstOrFail()->id_tempat;
            $item = Tempat::query()->where('id_tempat', $placeId)->lockForUpdate()->firstOrFail();
            $q = Booking::query()->where('id_booking', $id);
            $booking = $q->lockForUpdate()->firstOrFail();
            $cancel = $status === 'DIBATALKAN';
            abort_unless(($cancel ? $booking->id_penyewa : $item->id_pemilik) === $r->user()->id, 403);
            if ($booking->status_booking !== 'MENUNGGU_KONFIRMASI') {
                throw ValidationException::withMessages(['booking' => 'Status booking telah berubah.']);
            }
            $q->update(['status_booking' => $status, 'alasan_penolakan' => $reason, 'updated_at' => now()]);
            Marketplace::status($item, $status === 'DIKONFIRMASI' ? 'TERISI' : 'TERSEDIA', $r->user()->id);
            Marketplace::notify($cancel ? $item->id_pemilik : $booking->id_penyewa, 'Booking '.strtolower($status), $item->nama_tempat.'. '.$reason, 'BOOKING');
        });

        return back()->with('ok', 'Booking '.strtolower($status).'.');
    }

    public function review(Request $r, int $id)
    {
        $data = $r->validate(['rating' => 'required|integer|between:1,5', 'komentar' => 'nullable|string|max:2000']);
        Tempat::resolveConnection()->transaction(function () use ($r, $id, $data) {
            $b = Booking::query()->where('id_booking', $id)->where('id_penyewa', $r->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($b->status_booking === 'DIKONFIRMASI' && $b->tanggal_selesai < now('Asia/Jakarta')->toDateString(), 403);
            if (Ulasan::query()->where('id_booking', $id)->exists()) {
                throw ValidationException::withMessages(['rating' => 'Booking ini sudah diulas.']);
            }
            $b->ulasan()->create($data);
        });

        return back()->with('ok', 'Terima kasih. Ulasan Anda sudah tampil.');
    }

    public function reviews(Request $r)
    {
        $items = Ulasan::with(['booking.tempat', 'booking.penyewa'])->whereHas('booking.tempat', fn ($q) => $q->where('id_pemilik', $r->user()->id))->orderByDesc('id_ulasan')->get();

        return view('market.reviews', compact('items'));
    }
}
