@extends('market.layout')
@section('title','Hasil pencarian lapak')
@push('styles')<link rel="stylesheet" href="/lapak-filter.css">@endpush
@section('content')
<form action="/cari">
<section class="pagehead"><div class="frame"><nav><a href="/">Beranda</a> › Cari Lapak › Hasil Pencarian</nav><h1>Hasil pencarian lapak</h1><div class="searchbar search-summary"><label>Lokasi<input name="lokasi" value="{{request('lokasi')}}" placeholder="Ketik kawasan atau kota"></label><label>Jenis tempat<select data-quick-type><option value="multi" hidden @selected(count((array)request('jenis',[]))>1)>Beberapa jenis</option><option value="">Semua jenis</option>@foreach(['BOOTH'=>'Booth Event','KIOS'=>'Kios','RUKO'=>'Ruko'] as $v=>$label)<option value="{{$v}}" @selected((array)request('jenis',[])===[$v])>{{$label}}</option>@endforeach</select></label><label>Anggaran sewa<input type="number" data-quick-max min="0" value="{{request('max')}}" placeholder="Harga maksimum (Rp)"></label><button>Cari Lapak</button></div></div></section>
<div class="frame workarea split">@include('market.filter')
<div><div class="row"><div><h2>Menampilkan {{$items->total()}} lapak</h2>@if(request('lokasi'))<p>Lokasi acuan: {{request('lokasi')}}</p>@endif</div><label>Urutkan<select name="sort" onchange="this.form.requestSubmit()"><option value="baru">Terbaru</option><option value="harga" @selected(request('sort')==='harga')>Harga terendah</option></select></label></div>@include('market.cards'){{$items->links('market.pagination')}}</div></div></form>
<script defer src="/lapak-filter.js"></script>@endsection
