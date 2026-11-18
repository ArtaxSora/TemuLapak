@extends('market.layout')
@section('title',$mode)
@if($mode==='Daftar')
    @push('styles')
        <link rel="stylesheet" href="/register.css">
    @endpush
@endif
@section('content')
<div class="authbg">
    <div class="wrap accountgrid">
        @if($mode!=='Profil')
            <aside class="intro">
                <span class="introicon"><img src="/media/imgIkonUser.svg" alt=""></span>
                <h2>{{$mode==='Masuk'?'Selamat datang kembali':'Bergabung dengan TemuLapak'}}</h2>
                <p>{{$mode==='Masuk'?'Masuk untuk mencari lapak, mengajukan booking, atau mengelola tempat usaha Anda.':'Satu akun untuk mencari tempat berjualan atau menyewakan tempat usaha Anda.'}}</p>
                @foreach(($mode==='Masuk'?['Biaya dan aturan sewa terlihat jelas','Pantau status pengajuan booking','Kelola listing dan booking masuk']:['Pilih peran sesuai kebutuhan Anda','Isi data singkat, tanpa langkah rumit','Mulai cari atau daftarkan tempat']) as $point)
                    <div class="introcheck"><img src="/media/imgIkonCheck.svg" alt="">{{$point}}</div>
                @endforeach
            </aside>
        @endif
        @if($mode==='Daftar')
            @include('market.register-form')
        @else
            <form class="panel stack auth" method="post">
                @csrf
                <h1>{{$mode==='Profil'?'Profil saya':'Masuk ke TemuLapak'}}</h1>
                @if($mode==='Masuk')
                    <p>Gunakan email dan kata sandi Anda.</p>
                @else
                    <label>Nama lengkap<input name="name" value="{{old('name',auth()->user()?->name)}}" required autocomplete="name"></label>
                    <label>Nomor telepon<input name="no_telepon" type="tel" value="{{old('no_telepon',auth()->user()?->no_telepon)}}" required></label>
                @endif
                <label>Email<input type="email" name="email" value="{{old('email',auth()->user()?->email)}}" required autocomplete="email"></label>
                @if($mode==='Masuk')
                    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
                @endif
                <button>{{$mode==='Profil'?'Simpan profil':$mode}}</button>
                @if($mode==='Masuk')
                    <p>Belum punya akun? <a href="/daftar">Daftar</a></p>
                @endif
            </form>
        @endif
    </div>
</div>
@endsection
