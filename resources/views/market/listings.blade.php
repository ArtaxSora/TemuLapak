@extends('market.layout')
@section('title',$title)
@push('styles')
@if(auth()->user()->peran==='ADMIN')
<link rel="stylesheet" href="/management-tables.css">
@endif
@endpush
@section('content')
@php($admin=auth()->user()->peran==='ADMIN')
<div @class(['management-page'=>$admin])>
@include('market.pagehead',['heading'=>$title,'description'=>$admin?'Periksa listing baru atau hasil edit sebelum tayang di pencarian.':'Kelola listing Anda. Status verifikasi dan status tempat ditampilkan terpisah.'])
<div @class(['frame workarea', 'management-content'=>$admin, 'flow'=>!$admin])>
@if($admin)
<nav class="tabs" aria-label="Filter status verifikasi">
    <span>Tampilkan:</span>
    @foreach(['MENUNGGU'=>'Menunggu verifikasi','DISETUJUI'=>'Disetujui','DITOLAK'=>'Ditolak'] as $key=>$label)
        <a class="chip" @if(request('status','MENUNGGU')===$key) aria-current="page" @endif href="{{request()->fullUrlWithQuery(['status'=>$key,'page'=>null])}}">@if(request('status','MENUNGGU')===$key) @include('market.table-icon',['icon'=>'check']) @endif{{$label}}</a>
    @endforeach
</nav>
<div class="tablebox" role="region" aria-label="Daftar antrean verifikasi" tabindex="0">
    <table class="verification-table">
        <thead><tr>@foreach(['Listing','Pemilik','Jenis','Diajukan','Pemeriksaan','Status','Aksi'] as $label)<th scope="col">{{$label}}</th>@endforeach</tr></thead>
        <tbody>
        @forelse($items as $i)
            @php($issue = $i->verificationIssue())
            <tr>
                <td><b>{{$i->nama_tempat}}</b><small>{{$i->lokasi}}</small></td>
                <td>{{$i->pemilik->name}}</td>
                <td>{{['BOOTH'=>'Booth Event','KIOS'=>'Kios','RUKO'=>'Ruko'][$i->jenis_tempat] ?? $i->jenis_tempat}}</td>
                <td class="date-range">{{$i->updated_at?->locale('id')->translatedFormat('j M Y')}}</td>
                <td>@if($issue)<span class="inspection-issue">@include('market.table-icon',['icon'=>'alert']){{$issue}}</span>@else Data lengkap @endif</td>
                <td>@include('market.status-badge',['status'=>$i->status_verifikasi])</td>
                <td><a class="btn secondary" href="/tempat/{{$i->id_tempat}}">Periksa</a></td>
            </tr>
        @empty
            <tr><td class="empty-table" colspan="7">Tidak ada listing dalam antrean ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<p class="infostrip">@include('market.table-icon',['icon'=>'info'])<span>Periksa kelengkapan data dan foto sebelum menyetujui listing. Listing dengan data rusak hanya bisa ditolak.</span></p>
@else
<p class="infostrip">Listing baru atau hasil edit belum tayang di pencarian sampai disetujui admin.</p>
@forelse($items as $i)<article class="panel ownerrow"><img src="{{$i->foto ?: '/media/imgPlaceholderFotoPemilikTempat.png'}}" alt="{{$i->nama_tempat}}"><div><small>{{$i->jenis_tempat}}</small><h3>{{$i->nama_tempat}}</h3><p>{{$i->lokasi}}</p><b>Rp {{number_format($i->harga_sewa,0,',','.')}} / {{$i->satuan_harga}}</b></div><div><small>Status verifikasi</small><span class="badge">{{ucfirst(strtolower($i->status_verifikasi))}}</span><small>Status tempat</small><span class="badge {{strtolower($i->status_tempat)}}">{{ucfirst(strtolower(str_replace('_',' ',$i->status_tempat)))}}</span></div><div class="stack"><a class="btn secondary" href="/tempat/{{$i->id_tempat}}">Lihat Detail</a><a class="btn" href="/pemilik/tempat/{{$i->id_tempat}}/edit">Edit Tempat</a></div>@if($i->catatan_verifikasi)<p class="infostrip">{{$i->catatan_verifikasi}}</p>@endif</article>@empty<div class="panel">Belum ada tempat. <a href="/pemilik/tempat/baru">Tambah tempat pertama Anda</a></div>@endforelse
@endif {{$items->links('market.pagination')}}</div></div>@endsection
