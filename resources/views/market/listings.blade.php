@extends('market.layout')
@section('title',$title)
@section('content')
@php($admin=auth()->user()->peran==='ADMIN')
@include('market.pagehead',['heading'=>$title,'description'=>$admin?'Periksa listing baru atau hasil edit sebelum tayang di pencarian.':'Kelola listing Anda. Status verifikasi dan status tempat ditampilkan terpisah.'])
<div class="frame workarea flow">
@if($admin)
<div class="tabs"><b>Tampilkan:</b>@foreach(['MENUNGGU'=>'Menunggu','DISETUJUI'=>'Disetujui','DITOLAK'=>'Ditolak'] as $key=>$label)<a class="chip" @if(request('status','MENUNGGU')===$key) aria-current="page" @endif href="?status={{$key}}">{{$label}}</a>@endforeach</div>
<div class="tablebox"><table><thead><tr>@foreach(['Listing','Pemilik','Jenis','Diajukan','Pemeriksaan','Status','Aksi'] as $label)<th>{{$label}}</th>@endforeach</tr></thead><tbody>
@forelse($items as $i)<tr><td><b>{{$i->nama_tempat}}</b><small>{{$i->lokasi}}</small></td><td>{{$i->pemilik->name}}</td><td>{{ucfirst(strtolower($i->jenis_tempat))}}</td><td>{{$i->updated_at?->format('d/m/Y')}}</td><td>{{$i->foto?'Foto tersedia':'Belum ada foto'}}</td><td><span class="badge">{{ucfirst(strtolower($i->status_verifikasi))}}</span></td><td><a class="btn secondary" href="/tempat/{{$i->id_tempat}}">Periksa</a></td></tr>@empty<tr><td colspan="7">Tidak ada listing dalam antrean ini.</td></tr>@endforelse</tbody></table></div>
@else
<p class="infostrip">Listing baru atau hasil edit belum tayang di pencarian sampai disetujui admin.</p>
@forelse($items as $i)<article class="panel ownerrow"><img src="{{$i->foto ?: '/media/imgPlaceholderFotoPemilikTempat.png'}}" alt="{{$i->nama_tempat}}"><div><small>{{$i->jenis_tempat}}</small><h3>{{$i->nama_tempat}}</h3><p>{{$i->lokasi}}</p><b>Rp {{number_format($i->harga_sewa,0,',','.')}} / {{$i->satuan_harga}}</b></div><div><small>Status verifikasi</small><span class="badge">{{ucfirst(strtolower($i->status_verifikasi))}}</span><small>Status tempat</small><span class="badge {{strtolower($i->status_tempat)}}">{{ucfirst(strtolower(str_replace('_',' ',$i->status_tempat)))}}</span></div><div class="stack"><a class="btn secondary" href="/tempat/{{$i->id_tempat}}">Lihat Detail</a><a class="btn" href="/pemilik/tempat/{{$i->id_tempat}}/edit">Edit Tempat</a></div>@if($i->catatan_verifikasi)<p class="infostrip">{{$i->catatan_verifikasi}}</p>@endif</article>@empty<div class="panel">Belum ada tempat. <a href="/pemilik/tempat/baru">Tambah tempat pertama Anda</a></div>@endforelse
@endif {{$items->links('market.pagination')}}</div>@endsection
