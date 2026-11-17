@php
$user = auth()->user();
$role = $user?->peran;
$ownerLink = $user ? '/pemilik/tempat/baru' : '/daftar?peran=PEMILIK';
$links = match($role) {
    'ADMIN' => ['/admin/verifikasi'=>'Antrean Verifikasi'],
    'PEMILIK' => ['/'=>'Beranda','/pemilik/tempat'=>'Tempat Saya','/pemilik/booking'=>'Booking Masuk'],
    'PENYEWA' => ['/'=>'Beranda','/cari'=>'Cari Lapak','/booking'=>'Booking Saya','/#cara'=>'Cara Sewa'],
    default => ['/'=>'Beranda','/cari'=>'Cari Lapak','/#cara'=>'Cara Sewa']
};
@endphp
<header class="sitehead"><div class="frame nav"><div class="head-left"><a class="brand" href="/"><span class="brandmark"><img src="/media/imgIkonStore.svg" alt=""></span>TemuLapak</a><nav aria-label="Navigasi utama">@foreach($links as $url=>$label)<a href="{{$url}}" @if(request()->getPathInfo()===$url) aria-current="page" @endif>{{$label}}</a>@endforeach</nav></div>
<div class="actions">@guest @unless(request()->is('masuk'))<a class="btn secondary" href="/masuk">Masuk / Daftar</a>@endunless @unless(request()->is('daftar'))<a class="btn" href="{{$ownerLink}}">Daftarkan Tempat</a>@endunless @else
@if($role==='PEMILIK' && !request()->is('pemilik/tempat/baru'))<a class="btn" href="{{$ownerLink}}">Tambah Tempat</a>@endif
@if($role!=='ADMIN')<a class="utility" href="/notifikasi"><img src="/media/imgIkonBell.svg" alt="">Notifikasi @if($user->notifikasi()->whereNull('dibaca_pada')->exists())<span aria-label="Belum dibaca" class="unread"></span>@endif</a>@endif
<details class="menu"><summary class="account-link"><img src="/media/imgIkonUser.svg" alt=""><span>{{$user->name}}<small>{{ucfirst(strtolower($role))}}</small></span></summary><div><a href="/profil">Profil</a>@if($role==='ADMIN')<a href="/admin/pengguna">Kelola pengguna</a><a href="/notifikasi">Notifikasi</a>@elseif($role==='PEMILIK')<a href="/pemilik/laporan">Laporan booking</a><a href="/pemilik/ulasan">Ulasan tempat</a>@endif</div></details>
<form method="post" action="/keluar">@csrf<button class="secondary">Keluar</button></form>@endguest</div></div></header>
