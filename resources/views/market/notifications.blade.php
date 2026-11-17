@extends('market.layout')
@section('title','Notifikasi')
@section('content')<div class="wrap narrow section flow"><div class="row"><h1>Notifikasi</h1>@if($items->count())<form method="post" action="/notifikasi/baca">@csrf<button>Tandai semua dibaca</button></form>@endif</div>@forelse($items as $n)<article class="panel"><small>{{$n->dibaca_pada?'DIBACA':'BELUM DIBACA'}}</small><h3>{{$n->judul}}</h3><p>{{$n->isi}}</p><time>{{$n->created_at}}</time></article>@empty<p>Belum ada notifikasi.</p>@endforelse {{$items->links('market.pagination')}}</div>@endsection
