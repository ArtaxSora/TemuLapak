@extends('market.layout')
@section('title','Ulasan tempat')
@section('content')<div class="wrap narrow section flow"><h1>Ulasan tempat</h1>@forelse($items->groupBy('booking.id_tempat') as $group)<section class="panel"><h2>{{$group->first()->booking->tempat->nama_tempat}}</h2><p>Rata-rata {{number_format($group->avg('rating'),1)}} / 5</p>@foreach($group as $v)<p><b>{{$v->booking->penyewa->name}} · {{$v->rating}} / 5</b><br>{{$v->komentar}}</p>@endforeach</section>@empty<p>Belum ada ulasan untuk tempat Anda.</p>@endforelse</div>@endsection
