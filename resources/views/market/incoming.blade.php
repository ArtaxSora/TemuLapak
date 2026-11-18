@push('styles')<link rel="stylesheet" href="/management-tables.css">@endpush
<div class="management-page">
@include('market.pagehead', ['heading'=>'Booking Masuk', 'description'=>'Pengajuan dari calon penyewa. Periksa detailnya, lalu konfirmasi atau tolak.'])
<div class="frame workarea management-content">
    <nav class="tabs" aria-label="Filter status booking">
        <span>Tampilkan:</span>
        @foreach([''=>'Semua', 'MENUNGGU_KONFIRMASI'=>'Menunggu konfirmasi', 'DIKONFIRMASI'=>'Dikonfirmasi', 'DITOLAK'=>'Ditolak', 'DIBATALKAN'=>'Dibatalkan'] as $value=>$label)
            <a class="chip" href="{{request()->fullUrlWithQuery(['status'=>$value, 'page'=>null])}}" @if(request('status', '')===$value) aria-current="page" @endif>
                @if(request('status', '')===$value) @include('market.table-icon', ['icon'=>'check']) @endif{{$label}}
            </a>
        @endforeach
    </nav>
    <div class="tablebox" role="region" aria-label="Daftar booking masuk" tabindex="0">
        <table class="incoming-table">
            <thead><tr>@foreach(['Penyewa','Tempat','Periode','Estimasi total','Status','Aksi'] as $heading)<th scope="col">{{$heading}}</th>@endforeach</tr></thead>
            <tbody>
            @forelse($items as $booking)
                @php($estimate = $booking->costEstimate())
                <tr>
                    <td><b>{{$booking->penyewa->name}}</b><small>{{$booking->penyewa->no_telepon}}</small></td>
                    <td><b>{{$booking->tempat->nama_tempat}}</b><small>{{['BOOTH'=>'Booth Event','KIOS'=>'Kios','RUKO'=>'Ruko'][$booking->tempat->jenis_tempat] ?? $booking->tempat->jenis_tempat}} · {{$booking->tempat->lokasi}}</small></td>
                    <td><span class="date-range">{{\Carbon\Carbon::parse($booking->tanggal_mulai)->locale('id')->translatedFormat('j M Y')}} – {{\Carbon\Carbon::parse($booking->tanggal_selesai)->locale('id')->translatedFormat('j M Y')}}</span><small>{{$estimate['units']}} {{$estimate['unit']}}</small></td>
                    <td class="money" title="Estimasi sewa dan biaya tambahan sesuai periode">Rp {{number_format($estimate['total'], 0, ',', '.')}}@if($estimate['unknown_usage'] ?? false)<small>+ biaya pemakaian</small>@endif</td>
                    <td>@include('market.status-badge', ['status'=>$booking->status_booking])</td>
                    <td><a class="btn secondary" href="/pemilik/booking/{{$booking->id_booking}}">Lihat Detail</a></td>
                </tr>
            @empty
                <tr><td class="empty-table" colspan="6">Belum ada booking pada daftar ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="infostrip">@include('market.table-icon', ['icon'=>'info'])<span>Estimasi total mengikuti periode setiap biaya. Biaya pemakaian yang belum diperkirakan dihitung terpisah. Sisa periode dibulatkan ke atas. Ketersediaan diperiksa ulang saat Anda menekan Konfirmasi agar tidak terjadi double-booking.</span></p>
    {{$items->links('market.pagination')}}
</div>
</div>
