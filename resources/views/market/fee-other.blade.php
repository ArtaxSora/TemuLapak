@php($mode=$fee['metode'] ?? 'tetap')
<div class="fee-card other-fee" data-fee-row>
    <button type="button" class="remove-fee" data-remove-fee aria-label="Hapus biaya tambahan" title="Hapus biaya ini">@include('market.table-icon',['icon'=>'close'])</button>
    <div class="pair">
        <label>Nama biaya<input name="biaya[{{$key}}][nama]" maxlength="255" value="{{$fee['nama'] ?? ''}}" placeholder="Iuran kebersihan"><small>Contoh: listrik, air, atau keamanan</small></label>
        <label><span data-rate-label>{{$mode==='pemakaian' ? 'Tarif per unit (Rp)' : 'Nominal per periode (Rp)'}}</span><input type="text" inputmode="numeric" data-money name="biaya[{{$key}}][nominal]" value="{{$fee['nominal'] ?? ''}}" placeholder="50.000" data-fee-rate><small data-rate-help>{{$mode==='termasuk' ? 'Sudah termasuk harga sewa' : 'Isi 0 jika tidak ada'}}</small></label>
    </div>
    @include('market.fee-period')
    <details class="fee-calculation" @if($mode!=='tetap') open @endif>
        <summary>Cara perhitungan biaya</summary>
        <fieldset class="editor-choices fee-modes"><legend class="sr-only">Cara perhitungan biaya tambahan</legend>
            @foreach(['tetap'=>'Biaya tetap','termasuk'=>'Termasuk harga sewa','pemakaian'=>'Sesuai pemakaian'] as $value=>$text)
            <label><input type="radio" name="biaya[{{$key}}][metode]" value="{{$value}}" @checked($mode===$value) data-fee-mode><span>{{$text}}</span></label>
            @endforeach
        </fieldset>
        <div class="pair" data-usage-only @if($mode!=='pemakaian') hidden @endif>
            <label>Satuan pemakaian *<input name="biaya[{{$key}}][satuan_pemakaian]" maxlength="30" value="{{$fee['satuan_pemakaian'] ?? ''}}" placeholder="Contoh: kWh atau m³"><small>Tarif di atas berlaku per unit ini</small></label>
            <label>Perkiraan pemakaian (opsional)<input type="number" min="0" max="1000000" step="0.01" name="biaya[{{$key}}][perkiraan_pemakaian]" value="{{$fee['perkiraan_pemakaian'] ?? ''}}" placeholder="100"><small>Jumlah pemakaian per periode biaya</small></label>
        </div>
    </details>
</div>
