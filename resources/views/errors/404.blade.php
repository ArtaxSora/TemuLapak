@extends('market.layout')
@section('title','Halaman tidak ditemukan')
@section('content')<div class="wrap section"><h1>{{request()->is('tempat/*')?'Tempat tidak ditemukan':'Halaman tidak ditemukan'}}</h1><p>Periksa kembali alamat halaman atau jelajahi tempat yang sudah tayang.</p><a class="btn" href="/cari">Cari lapak</a></div>@endsection
