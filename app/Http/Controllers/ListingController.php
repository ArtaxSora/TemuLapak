<?php

namespace App\Http\Controllers;

use App\Models\AturanSewa;
use App\Models\BiayaTambahan;
use App\Models\Fasilitas;
use App\Models\FotoTempat;
use App\Models\Tempat;
use App\Models\TempatFasilitas;
use App\Models\Ulasan;
use App\Support\Marketplace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    private function facilities()
    {
        return Fasilitas::query()->orderBy('nama_fasilitas')->get();
    }

    public function home()
    {
        Marketplace::expire();

        $items = Marketplace::cards()->where('status_verifikasi', 'DISETUJUI')->orderByDesc('id_tempat')->with(['fees', 'facilities'])->limit(3)->get();

        return view('market.home', compact('items'));
    }

    public function search(Request $r)
    {
        $placeTypes = ['BOOTH' => 'Booth Event', 'KIOS' => 'Kios', 'RUKO' => 'Ruko'];
        $periods = ['hari' => 'Per hari', 'minggu' => 'Per minggu', 'bulan' => 'Per bulan'];
        if (Tempat::where('status_verifikasi', 'DISETUJUI')->where('satuan_harga', 'tahun')->exists() || in_array('tahun', (array) $r->input('periode'), true)) {
            $periods['tahun'] = 'Per tahun';
        }
        foreach (['jenis', 'periode'] as $key) {
            if (is_string($r->input($key))) {
                $r->merge([$key => [$r->input($key)]]);
            }
        }
        foreach (['min', 'max'] as $key) {
            $amount = $r->input($key);
            if (is_string($amount) && preg_match('/^(?:Rp\s*)?(?:\d{1,3}(?:\.\d{3})+|\d+)$/i', $amount)) {
                $r->merge([$key => preg_replace('/[^0-9]/', '', $amount)]);
            }
        }
        $r->validate([
            'lokasi' => 'nullable|string|max:255',
            'min' => 'nullable|numeric|min:0',
            'max' => ['nullable', 'numeric', 'min:0', ...($r->filled('min') ? ['gte:min'] : [])],
            'jenis' => 'nullable|array', 'jenis.*' => Rule::in(array_keys($placeTypes)),
            'periode' => 'nullable|array', 'periode.*' => Rule::in(array_keys($periods)),
            'fasilitas' => 'nullable|array', 'fasilitas.*' => 'integer',
        ]);
        Marketplace::expire();
        $query = Marketplace::cards()->where('status_verifikasi', 'DISETUJUI');
        if ($r->filled('lokasi')) {
            $query->whereLike('lokasi', '%'.$r->lokasi.'%');
        }
        if ($r->filled('jenis')) {
            $query->whereIn('jenis_tempat', $r->jenis);
        }
        if ($r->filled('periode')) {
            $query->whereIn('satuan_harga', $r->periode);
        }
        if ($r->filled('min')) {
            $query->where('harga_sewa', '>=', $r->min);
        }
        if ($r->filled('max')) {
            $query->where('harga_sewa', '<=', $r->max);
        }
        foreach ($r->input('fasilitas', []) as $fid) {
            $query->whereIn('id_tempat', TempatFasilitas::query()->select('id_tempat')->where('id_fasilitas', $fid));
        }
        if ($r->sort === 'harga') {
            $query->orderBy('harga_sewa');
        }

        $items = $query->orderByDesc('id_tempat')->with(['fees', 'facilities'])->paginate(12)->withQueryString();

        return view('market.search', compact('items', 'placeTypes', 'periods') + ['filterFacilities' => $this->searchFacilities($r)]);
    }

    private function searchFacilities(Request $request)
    {
        $order = array_flip(['Listrik', 'Air', 'Parkir motor', 'Parkir mobil', 'Toilet', 'Rolling door', 'Etalase', 'Meja & kursi']);
        return Fasilitas::whereIn('nama_fasilitas', array_keys($order))
            ->orWhereIn('id_fasilitas', $request->input('fasilitas', []))
            ->get()->sortBy(fn ($facility) => $order[$facility->nama_fasilitas] ?? PHP_INT_MAX);
    }

    private function parts(Tempat $item): array
    {
        return [
            'photos' => $item->photos,
            'fees' => $item->fees,
            'rules' => $item->rules,
            'facilities' => $item->facilities,
        ];
    }

    public function detail(Request $r, int $id)
    {
        Marketplace::expire();
        $item = Tempat::query()->where('id_tempat', $id)->firstOrFail();
        abort_unless($item->status_verifikasi === 'DISETUJUI' || $r->user()?->peran === 'ADMIN' || $r->user()?->id === $item->id_pemilik, 404);
        $reviews = Ulasan::with('booking.penyewa')->whereHas('booking', fn ($q) => $q->where('id_tempat', $id))->orderByDesc('id_ulasan')->get();

        return view('market.detail', $this->parts($item) + ['item' => $item, 'owner' => $item->pemilik, 'reviews' => $reviews]);
    }

    public function bookingForm(Request $r, int $id)
    {
        Marketplace::expire();
        $item = Tempat::query()->where('status_verifikasi', 'DISETUJUI')->where('id_tempat', $id)->firstOrFail();
        if ($item->status_tempat !== 'TERSEDIA') {
            return redirect('/tempat/'.$id)->withErrors(['booking' => 'Tempat sudah tidak tersedia.']);
        }

        return view('market.booking-form', $this->parts($item) + ['item' => $item]);
    }

    public function mine(Request $r)
    {
        return view('market.listings', ['title' => 'Tempat Saya', 'items' => Marketplace::cards()->where('id_pemilik', $r->user()->id)->orderByDesc('id_tempat')->paginate(15)]);
    }

    public function queue(Request $r)
    {
        return view('market.listings', ['title' => 'Antrean Verifikasi', 'items' => Marketplace::cards()->with('pemilik')->where('status_verifikasi', $r->input('status', 'MENUNGGU'))->orderBy('updated_at')->paginate(15)->withQueryString()]);
    }

    public function form(Request $r, ?int $id = null)
    {
        $item = $id ? Tempat::query()->where('id_pemilik', $r->user()->id)->where('id_tempat', $id)->firstOrFail() : null;

        return view('market.edit', ['item' => $item, 'master' => $this->facilities()] + ($id ? $this->parts($item) : ['photos' => collect(), 'fees' => collect(), 'rules' => null, 'facilities' => collect()]));
    }

    public function save(Request $r, ?int $id = null)
    {
        $listing = $r->validate(['alamat_lengkap' => 'nullable|string|max:255', 'ukuran' => 'nullable|string|max:80', 'nama_tempat' => 'required|string|max:255', 'jenis_tempat' => 'required|in:BOOTH,KIOS,RUKO', 'lokasi' => 'required|string|max:255', 'harga_sewa' => 'required|numeric|min:1|max:99999999999999', 'satuan_harga' => 'required|in:hari,minggu,bulan,tahun', 'kondisi_tempat' => 'required|string|max:3000', 'deskripsi' => 'nullable|string|max:6000']);
        $rules = $r->validate(['durasi_minimal' => 'required|string|max:255', 'perpanjangan' => 'required|string|max:2000', 'sistem_pembayaran' => 'required|string|max:2000', 'ketentuan_pembatalan' => 'required|string|max:2000', 'denda_keterlambatan' => 'required|string|max:2000']);
        $r->validate(['fasilitas' => 'nullable|array', 'fasilitas.*' => ['distinct', Rule::exists(Fasilitas::class, 'id_fasilitas')], 'foto' => 'nullable|array|max:8', 'foto.*' => 'file|mimes:jpg,jpeg,png|max:2048', 'hapus' => 'nullable|array', 'hapus.*' => 'integer', 'biaya' => 'nullable|array|max:50', 'biaya.*.nama' => 'nullable|string|max:255', 'biaya.*.nominal' => 'nullable|numeric|min:0|max:99999999999999']);
        $fees = [];
        foreach ($r->input('biaya', []) as $row) {
            if (blank($row['nama'] ?? null) && blank($row['nominal'] ?? null)) {
                continue;
            }
            if (blank($row['nama'] ?? null) || blank($row['nominal'] ?? null)) {
                throw ValidationException::withMessages(['biaya' => 'Isi nama dan nominal untuk setiap biaya.']);
            }
            $fees[] = ['nama_biaya' => $row['nama'], 'nominal' => $row['nominal']];
        }
        $written = [];
        try {
            Tempat::resolveConnection()->transaction(function () use ($r, &$id, $listing, $rules, $fees, &$written) {
                $values = $listing + ['status_verifikasi' => 'MENUNGGU', 'catatan_verifikasi' => null, 'id_admin' => null, 'diverifikasi_pada' => null];
                if ($id) {
                    $owned = Tempat::query()->where('id_tempat', $id)->where('id_pemilik', $r->user()->id);
                    $owned->lockForUpdate()->firstOrFail();
                    $owned->update($values);
                } else {
                    $id = Tempat::create($values + ['id_pemilik' => $r->user()->id])->getKey();
                }
                $old = FotoTempat::query()->where('id_tempat', $id)->whereIn('id_foto', $r->input('hapus', []))->get();
                FotoTempat::query()->whereIn('id_foto', $old->pluck('id_foto'))->delete();
                $photos = FotoTempat::query()->where('id_tempat', $id);
                $uploads = $r->file('foto', []);
                $total = $photos->count() + count($uploads);
                if ($total < 1 || $total > 8) {
                    throw ValidationException::withMessages(['foto' => 'Simpan 1 sampai 8 foto tempat.']);
                }
                $order = $photos->max('urutan') ?? 0;
                foreach ($uploads as $file) {
                    $name = $file->hashName();
                    $file->move(public_path('uploads/lapak'), $name);
                    $written[] = public_path('uploads/lapak/'.$name);
                    FotoTempat::create(['id_tempat' => $id, 'url_foto' => '/uploads/lapak/'.$name, 'urutan' => ++$order]);
                }
                AturanSewa::updateOrCreate(['id_tempat' => $id], $rules);
                Tempat::findOrFail($id)->facilities()->sync($r->input('fasilitas', []));
                BiayaTambahan::query()->where('id_tempat', $id)->delete();
                foreach ($fees as $fee) {
                    BiayaTambahan::create($fee + ['id_tempat' => $id]);
                }
                Tempat::resolveConnection()->afterCommit(function () use ($old) {
                    foreach ($old as $photo) {
                        if (str_starts_with($photo->url_foto, '/uploads/lapak/')) {
                            @unlink(public_path('uploads/lapak/'.basename($photo->url_foto)));
                        }
                    }
                });
            });
        } catch (\Throwable $e) {
            foreach ($written as $path) {
                @unlink($path);
            }
            throw $e;
        }

        return redirect('/pemilik/tempat')->with('ok', 'Tempat tersimpan dan menunggu verifikasi admin.');
    }

    public function verifyListing(Request $r, int $id)
    {
        $data = $r->validate(['status' => 'required|in:DISETUJUI,DITOLAK', 'catatan' => 'required_if:status,DITOLAK|nullable|string|max:2000']);
        Tempat::resolveConnection()->transaction(function () use ($r, $id, $data) {
            $query = Tempat::query()->where('id_tempat', $id);
            $item = $query->lockForUpdate()->firstOrFail();
            if ($item->status_verifikasi !== 'MENUNGGU') {
                throw ValidationException::withMessages(['status' => 'Listing ini sudah diproses.']);
            }
            if ($data['status'] === 'DISETUJUI' && (! FotoTempat::query()->where('id_tempat', $id)->exists() || ! AturanSewa::query()->where('id_tempat', $id)->exists())) {
                throw ValidationException::withMessages(['status' => 'Foto dan aturan sewa wajib lengkap sebelum persetujuan.']);
            }
            $query->update(['status_verifikasi' => $data['status'], 'id_admin' => $r->user()->id, 'catatan_verifikasi' => $data['catatan'] ?? null, 'diverifikasi_pada' => now(), 'updated_at' => now()]);
            Marketplace::notify($item->id_pemilik, 'Hasil verifikasi tempat', $item->nama_tempat.' '.strtolower($data['status']).'. '.($data['catatan'] ?? ''), 'VERIFIKASI');
        });

        return redirect('/admin/verifikasi')->with('ok', 'Keputusan verifikasi tersimpan.');
    }
}
