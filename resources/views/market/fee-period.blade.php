<fieldset class="editor-choices fee-period" data-period-fields>
    <legend>Periode biaya</legend>
    @foreach(['hari'=>'Per hari','minggu'=>'Per minggu','bulan'=>'Per bulan'] + (($fee['periode'] ?? '')==='tahun' ? ['tahun'=>'Per tahun'] : []) as $period=>$label)
    <label><input type="radio" name="biaya[{{$key}}][periode]" value="{{$period}}" @checked(($fee['periode'] ?? 'bulan')===$period)><span>{{$label}}</span></label>
    @endforeach
</fieldset>
