@extends('market.layout')
@section('title','Kelola tempat')
@push('styles')
<link rel="stylesheet" href="/listing-editor.css">
<script defer src="/listing-editor.js"></script>
@endpush
@php
$defaultPeriod = old('satuan_harga',$item?->satuan_harga ?? 'bulan');
$feeRows = collect(old('biaya',$fees->map(fn($f)=>[
    'nama'=>$f->nama_biaya, 'nominal'=>number_format($f->nominal,0,',','.'),
    'periode'=>$f->periode ?: ($item?->satuan_harga ?? 'bulan'), 'metode'=>$f->metode ?? 'tetap',
    'satuan_pemakaian'=>$f->satuan_pemakaian, 'perkiraan_pemakaian'=>$f->perkiraan_pemakaian,
])->all()));
@endphp
@section('content')<div class="pagehead"><div class="frame"><nav><a href="/">Beranda</a> › <a href="/pemilik/tempat">Tempat Saya</a> › {{$item?"Edit Tempat":"Tambah Tempat"}}</nav><h1>{{$item?"Edit Tempat":"Tambah Tempat"}}</h1><p>Lengkapi semua bagian bertanda *. Listing belum tayang sampai disetujui admin.</p></div></div><div class="frame formcols listing-editor"><aside class="partnav"><strong>Bagian form</strong>@foreach(["Informasi tempat","Foto dan fasilitas","Harga dan biaya","Aturan sewa","Ringkasan"] as $name)<a href="#bagian-{{$loop->iteration}}" @if($loop->first) aria-current="location" @endif><span>{{$loop->iteration}}</span>{{$name}}</a>@endforeach</aside><form enctype="multipart/form-data" method="post" action="/pemilik/tempat{{$item?'/'.$item->id_tempat:''}}" class="flow">@csrf<div class="panel stack"><h2 id="bagian-1">1. Informasi tempat</h2><label>Nama tempat *<input required name="nama_tempat" placeholder="Kios Depan Pasar Pondok Labu" value="{{old('nama_tempat',$item?->nama_tempat)}}"><small>Nama singkat yang mudah diingat</small></label>
<fieldset class="editor-choices place-types"><legend>Jenis tempat *</legend>@foreach(['BOOTH'=>'Booth Event','KIOS'=>'Kios','RUKO'=>'Ruko'] as $type=>$name)<label><input required type="radio" name="jenis_tempat" value="{{$type}}" @checked(old('jenis_tempat',$item?->jenis_tempat)===$type)><span>{{$name}}</span></label>@endforeach</fieldset>
<div class="pair"><label>Lokasi (kawasan atau kota) *<input name="lokasi" required value="{{old('lokasi',$item?->lokasi)}}" placeholder="Pondok Labu, Jakarta Selatan"><small>Dipakai untuk pencarian</small></label><label>Ukuran (opsional)<input name="ukuran" maxlength="80" value="{{old('ukuran',$item?->ukuran)}}" placeholder="3 × 4 m"><small>Isi bila diketahui</small></label></div><label>Alamat lengkap *<input name="alamat_lengkap" required maxlength="255" value="{{old('alamat_lengkap',$item?->alamat_lengkap)}}" placeholder="Jl. Contoh No. 12, Pondok Labu"><small>Alamat jelas membantu penyewa menemukan tempat</small></label>
<label>Kondisi tempat *<textarea required name="kondisi_tempat" placeholder="Contoh: lantai keramik, atap tidak bocor, dinding baru dicat.">{{old('kondisi_tempat',$item?->kondisi_tempat)}}</textarea><small>Ceritakan kondisi apa adanya, termasuk kekurangan bila ada.</small></label><details><summary>Deskripsi tambahan</summary><label>Deskripsi<textarea name="deskripsi">{{old('deskripsi',$item?->deskripsi)}}</textarea></label></details></div>
<section class="panel stack"><h2 id="bagian-2">2. Foto dan fasilitas</h2>
    <div class="photo-drop" data-photo-drop>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 16V3m-6 6 6-6 6 6M4 18v3h16v-3"/></svg>
        <strong>Unggah foto tempat</strong>
        <p>Format JPG atau PNG, maksimal 2 MB per foto. Tambahkan 1–8 foto.</p>
        <input id="listing-photos" name="foto[]" type="file" accept="image/jpeg,image/png" multiple hidden>
        <button type="button" class="secondary" data-choose-photos>Pilih Foto</button>
        <small>Atau tarik foto ke area ini</small>
    </div>
    <div class="photo-grid" data-photo-grid>
        @foreach($photos as $photo)
        <div class="photo-tile" data-existing-photo><img src="{{$photo->url_foto}}" alt="Foto tempat {{$loop->iteration}}"><input type="checkbox" name="hapus[]" value="{{$photo->id_foto}}" hidden @checked(in_array($photo->id_foto,old('hapus',[])))><button type="button" class="photo-remove" data-remove-photo aria-label="Hapus foto {{$loop->iteration}}">@include('market.table-icon',['icon'=>'close'])</button></div>
        @endforeach
    </div>
    <fieldset class="editor-choices facility-choices"><legend>Fasilitas</legend>
        @foreach($master as $f)<label><input type="checkbox" name="fasilitas[]" value="{{$f->id_fasilitas}}" @checked(in_array($f->id_fasilitas,old('fasilitas',$facilities->pluck('id_fasilitas')->all())))><span>{{$f->nama_fasilitas}}</span></label>@endforeach
    </fieldset>
</section>
<section class="panel stack"><h2 id="bagian-3">3. Harga dan biaya</h2>
    <p class="section-hint">Biaya yang dihitung dari pemakaian wajib menjelaskan tarif atau dasar perhitungannya.</p>
    <div class="pair price-main">
        <label>Harga sewa pokok (Rp) *<input required type="text" inputmode="numeric" data-money data-money-min="1" name="harga_sewa" value="{{old('harga_sewa',$item ? number_format($item->harga_sewa,0,',','.') : '')}}" placeholder="1.800.000"><small>Harus lebih dari 0</small></label>
        <fieldset class="editor-choices base-period"><legend>Satuan periode *</legend>
            @foreach(['hari'=>'Per hari','minggu'=>'Per minggu','bulan'=>'Per bulan'] + ($defaultPeriod==='tahun' ? ['tahun'=>'Per tahun'] : []) as $v=>$label)
            <label><input type="radio" name="satuan_harga" value="{{$v}}" required @checked($defaultPeriod===$v)><span>{{$label}}</span></label>
            @endforeach
            <small>Harga di atas berlaku per satuan ini.</small>
        </fieldset>
    </div>
    <div class="other-fees-section"><h3>Biaya lainnya</h3>
        <div id="fees" class="stack">@foreach($feeRows->values() as $n=>$fee) @include('market.fee-other',['key'=>'other_'.$n,'fee'=>$fee]) @endforeach</div>
        <button type="button" class="add-fee" id="addfee">+ Tambah biaya lain</button>
    </div>
    <template id="fee-template">@include('market.fee-other',['key'=>'__INDEX__','fee'=>['periode'=>'bulan']])</template>
</section>
<div class="panel stack"><h2 id="bagian-4">4. Aturan sewa</h2>@foreach(['durasi_minimal'=>'Durasi minimal','perpanjangan'=>'Ketentuan perpanjangan','sistem_pembayaran'=>'Sistem pembayaran','ketentuan_pembatalan'=>'Ketentuan pembatalan','denda_keterlambatan'=>'Denda keterlambatan'] as $k=>$v)<label>{{$v}}<input required name="{{$k}}" value="{{old($k,$rules?->$k)}}"></label>@endforeach</div><div class="panel stack"><h2 id="bagian-5">5. Ringkasan sebelum dikirim</h2><p>Periksa foto, fasilitas, rincian biaya, dan aturan sewa. Listing akan masuk antrean verifikasi admin setelah dikirim.</p><div class="actions"><button>Simpan dan ajukan verifikasi</button><a class="btn secondary" href="/pemilik/tempat">Batal</a></div></div></form></div>@endsection
