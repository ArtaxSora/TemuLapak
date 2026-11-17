@extends('market.layout')
@section('title','Form pengajuan booking')
@section('content')
@include('market.pagehead',['heading'=>'Form Pengajuan Booking','parent'=>$item->nama_tempat,'parentUrl'=>'/tempat/'.$item->id_tempat,'description'=>'Tentukan periode sewa dan periksa kembali biaya serta aturan tempat sebelum mengirim pengajuan.'])
<form method="post" class="frame detail workarea">@csrf
<div class="flow"><section class="panel place-summary">@if($photos->first())<img class="thumb" src="{{$photos->first()->url_foto}}" alt="">@endif<div><small>TEMPAT YANG DIAJUKAN</small><h3>{{$item->nama_tempat}}</h3><p>{{$item->jenis_tempat}} · {{$item->lokasi}}</p></div></section>
<section class="panel stack"><h2>Periode sewa</h2><div class="pair"><label>Tanggal mulai *<input name="tanggal_mulai" required type="date" min="{{now('Asia/Jakarta')->toDateString()}}" value="{{old('tanggal_mulai')}}"></label><label>Tanggal selesai *<input name="tanggal_selesai" required type="date" value="{{old('tanggal_selesai')}}"></label></div><label>Catatan untuk pemilik<textarea name="catatan" placeholder="Tuliskan kebutuhan usaha Anda">{{old('catatan')}}</textarea></label></section>
@include('market.rental-rules')</div><aside class="panel stack"><h2>Rincian biaya</h2>@include('market.price-lines')<p class="infostrip">Pembayaran dilakukan langsung kepada pemilik setelah booking dikonfirmasi.</p><button>Kirim pengajuan</button><a class="btn secondary" href="/tempat/{{$item->id_tempat}}">Kembali ke detail</a></aside></form>@endsection
