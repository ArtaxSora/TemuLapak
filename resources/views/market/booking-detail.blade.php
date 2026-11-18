@extends('market.layout')
@section('title',$owner?'Detail Pengajuan Booking':'Ringkasan pengajuan')
@push('styles')
<link rel="stylesheet" href="/booking.css">
@endpush
@section('content')
<div class="booking-ui">
@if($owner)
@include('market.pagehead',['heading'=>'Detail Pengajuan Booking','parent'=>'Booking Masuk','parentUrl'=>'/pemilik/booking','description'=>$b->tempat->nama_tempat])
<div class="frame detail workarea"><div class="flow"><section class="panel"><h2>Penyewa</h2><b>{{$b->penyewa->name}}</b><p>{{$b->penyewa->email}} · {{$b->penyewa->no_telepon}}</p></section>
<section class="panel"><h2>Tempat yang diajukan</h2><h3>{{$b->tempat->nama_tempat}}</h3><p>{{$b->tempat->lokasi}}</p></section><section class="panel"><h2>Periode sewa</h2><dl class="spec-list"><dt>Tanggal mulai</dt><dd>{{$b->tanggal_mulai}}</dd><dt>Tanggal selesai</dt><dd>{{$b->tanggal_selesai}}</dd></dl></section><section class="panel"><h2>Catatan penyewa</h2><p>{{$b->catatan ?: 'Tidak ada catatan.'}}</p></section>
@else
<div class="frame booking-confirmation workarea">
    <div class="booking-confirmation-heading">
        <span class="booking-success-icon">@include('market.table-icon',['icon'=>'check'])</span>
        <h1>{{session('submitted') ? 'Pengajuan Anda tersimpan' : 'Ringkasan pengajuan'}}</h1>
        @if(session('submitted'))<p>Pemilik tempat sudah diberi tahu dan akan mengonfirmasi pengajuan Anda.<br>Kami akan memberi tahu Anda setelah ada keputusan.</p>@else<p>Pantau status pengajuan dan rincian biaya sewa Anda.</p>@endif
    </div>
    <section class="panel booking-application-summary">
        <h2>Ringkasan pengajuan</h2>
        <dl class="booking-summary-lines">
            <div><dt>Tempat</dt><dd>{{$b->tempat->nama_tempat}}</dd></div>
            <div><dt>Periode</dt><dd>{{\Carbon\Carbon::parse($b->tanggal_mulai)->locale('id')->translatedFormat('j F Y')}} – {{\Carbon\Carbon::parse($b->tanggal_selesai)->locale('id')->translatedFormat('j F Y')}}</dd></div>
            <div><dt>Estimasi total</dt><dd>Rp {{number_format($estimate['total'],0,',','.')}}@if($estimate['unknown_usage'] ?? false)<small class="estimate-unknown">Belum termasuk pemakaian yang belum diperkirakan</small>@endif</dd></div>
            <div class="booking-status-row"><dt>Status booking</dt><dd>@include('market.status-badge',['status'=>$b->status_booking])</dd></div>
            <div><dt>Status tempat</dt><dd>@include('market.status-badge',['status'=>$b->tempat->status_tempat])</dd></div>
        </dl>
        <p class="booking-duration">@include('market.table-icon',['icon'=>'info'])<span>Pembayaran tidak diproses di TemuLapak.@if($b->status_booking==='MENUNGGU_KONFIRMASI') Anda masih bisa membatalkan selama menunggu konfirmasi.@endif</span></p>
    </section>
@endif
@include('market.booking-cost-summary')
@if($owner)</div><aside class="panel stack"><h2>Keputusan</h2><p class="infostrip">Status booking · {{ucfirst(strtolower(str_replace('_',' ',$b->status_booking)))}}</p>
@if($b->status_booking==='MENUNGGU_KONFIRMASI')<form class="stack" method="post" action="/pemilik/booking/{{$b->id_booking}}/keputusan">@csrf<label>Alasan jika ditolak<textarea name="alasan">{{old('alasan')}}</textarea></label><button name="status" value="DIKONFIRMASI">Konfirmasi Booking</button><button class="secondary" name="status" value="DITOLAK" onclick="return confirm('Tolak booking ini?')">Tolak Booking</button></form>@endif @if($b->alasan_penolakan)<p>{{$b->alasan_penolakan}}</p>@endif<a href="/pemilik/booking">Kembali ke Booking Masuk</a></aside>
@else @if($b->alasan_penolakan)<p>{{$b->alasan_penolakan}}</p>@endif <div class="actions"><a class="btn" href="/booking">Lihat Booking Saya</a><a class="btn secondary" href="/cari">Cari Lapak Lain</a></div>@endif</div>
</div>
@endsection
