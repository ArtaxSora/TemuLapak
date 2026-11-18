<section class="panel booking-cost-summary">
    <h2>Ringkasan biaya</h2>
    <dl class="booking-cost-lines">
        @foreach($estimate['lines'] as $line)
        <div><dt>{{$line['label']}}<small>{{$line['calculation'] ?? $estimate['units'].' '.$estimate['unit'].' × Rp '.number_format($line['rate'],0,',','.')}}</small></dt><dd>{{$line['total'] === null ? 'Sesuai pemakaian' : 'Rp '.number_format($line['total'],0,',','.')}}</dd></div>
        @endforeach
    </dl>
    <div class="booking-total"><span>Estimasi total</span><strong>Rp {{number_format($estimate['total'],0,',','.')}}</strong></div>
    <p class="booking-cost-note">Setiap biaya mengikuti periode masing-masing. @if($estimate['unknown_usage'] ?? false)Total belum termasuk pemakaian yang belum diperkirakan.@endif
@if($estimate['rounded']) Sisa periode dibulatkan ke atas.@endif {{$terms['pembayaran'] ?? 'Pembayaran dilakukan langsung kepada pemilik.'}}</p>
</section>
