@extends('market.layout')
@section('title','Ajukan Booking')
@push('styles')
<link rel="stylesheet" href="/booking.css">
<script defer src="/booking-estimate.js"></script>
@endpush
@section('content')
<div class="booking-ui">
<section class="pagehead"><div class="frame">
    <nav aria-label="Lokasi halaman"><a href="/">Beranda</a><span>›</span><a href="/cari">Cari Lapak</a><span>›</span><a href="/tempat/{{$item->id_tempat}}">{{$item->nama_tempat}}</a><span>›</span><span>Ajukan Booking</span></nav>
    <h1>Ajukan Booking</h1><p>Isi tanggal sewa. Pengajuan akan dikirim ke pemilik untuk dikonfirmasi.</p>
</div></section>
<form method="post" action="/tempat/{{$item->id_tempat}}/booking" class="frame workarea booking-grid" data-booking-form data-estimate-url="/tempat/{{$item->id_tempat}}/estimasi" data-unit="{{$item->satuan_harga}}">
@csrf
<div class="booking-sections">
    <section class="panel">
        <h2>Tempat yang diajukan</h2>
        <div class="booking-place">
            <img src="{{$photos->first()?->url_foto ?: '/media/imgPlaceholderFotoPemilikTempat.png'}}" alt="{{$item->nama_tempat}}">
            <div><h3>{{$item->nama_tempat}}</h3><p class="location"><img src="/media/imgIkonPin1.svg" alt="">{{$item->lokasi}}</p>@include('market.status-badge',['status'=>$item->status_tempat])</div>
        </div>
    </section>
    <section class="panel booking-period">
        <h2>Periode sewa</h2>
        <div class="pair">
            <label>Tanggal mulai *<input name="tanggal_mulai" required type="date" min="{{now('Asia/Jakarta')->toDateString()}}" value="{{old('tanggal_mulai')}}" aria-describedby="start-help"><small id="start-help">Tidak boleh sebelum tanggal pengajuan</small></label>
            <label>Tanggal selesai *<input name="tanggal_selesai" required type="date" min="{{now('Asia/Jakarta')->toDateString()}}" value="{{old('tanggal_selesai')}}" aria-describedby="end-help"><small id="end-help">Tanggal terakhir termasuk dalam masa sewa</small></label>
        </div>
        <div class="booking-duration" role="status" aria-live="polite"><img src="/media/imgIkonCalendar.svg" class="table-icon" alt=""><span data-duration>Pilih tanggal mulai dan selesai untuk menghitung durasi sewa.</span></div>
        <label>Catatan untuk pemilik (opsional)<textarea name="catatan" maxlength="2000" placeholder="Contoh: Saya berjualan minuman dan ingin mulai di awal bulan." aria-describedby="note-help">{{old('catatan')}}</textarea><small id="note-help">Catatan dibaca pemilik sebelum memutuskan.</small></label>
    </section>
    <section class="panel booking-rules">
        <h2>Ringkasan aturan sewa</h2>
        <ul>
            @foreach(['Durasi minimal '.($rules?->durasi_minimal ?: 'belum dicantumkan').'.', $rules?->perpanjangan, $rules?->sistem_pembayaran, $rules?->ketentuan_pembatalan] as $rule)
                @if($rule)<li>@include('market.table-icon',['icon'=>'check'])<span>{{$rule}}</span></li>@endif
            @endforeach
        </ul>
        <details><summary>Lihat aturan lengkap</summary>@include('market.rental-rules')</details>
    </section>
</div>
<aside class="panel booking-cost" aria-labelledby="cost-title">
    <h2 id="cost-title">Rincian biaya sesuai periode</h2>
    <dl class="booking-cost-lines" data-cost-lines>
        <div><dt>Harga sewa pokok<small>Rp {{number_format($item->harga_sewa,0,',','.')}} / {{$item->satuan_harga}}</small></dt><dd>—</dd></div>
        @foreach($fees as $fee)<div><dt>{{$fee->nama_biaya}}<small>{{$fee->rateDescription($item->satuan_harga)}}</small></dt><dd>—</dd></div>@endforeach
    </dl>
    <div class="booking-total"><span>Estimasi total</span><strong data-estimate-total>—</strong></div>
    <p class="booking-cost-note" data-estimate-note>Setiap biaya mengikuti periode masing-masing. Sisa periode dibulatkan ke atas. Tidak ada pembayaran di langkah ini.</p>
    <button type="submit" data-booking-submit disabled>Kirim Pengajuan</button>
    <button type="button" class="booking-retry" data-estimate-retry hidden>Coba hitung lagi</button>
    <a class="booking-cancel" href="/tempat/{{$item->id_tempat}}">Batal</a>
</aside>
</form>
</div>
@endsection
