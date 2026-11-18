@php
$label = match($status) {
    'MENUNGGU_KONFIRMASI' => 'Menunggu konfirmasi',
    'MENUNGGU' => 'Menunggu verifikasi',
    'DIKONFIRMASI' => 'Dikonfirmasi',
    'DISETUJUI' => 'Disetujui',
    'DITOLAK' => 'Ditolak',
    'DIBATALKAN' => 'Dibatalkan',
    'TERSEDIA' => 'Tersedia',
    'TERISI' => 'Terisi',
    default => ucfirst(strtolower(str_replace('_', ' ', $status))),
};
$icon = match($status) {
    'DIKONFIRMASI', 'DISETUJUI', 'TERSEDIA' => 'check',
    'DITOLAK', 'DIBATALKAN' => 'close',
    default => 'clock',
};
@endphp
<span class="badge {{strtolower($status)}}">@include('market.table-icon', ['icon'=>$icon]){{$label}}</span>
