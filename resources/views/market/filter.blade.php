<aside class="lapak-filter" aria-labelledby="filter-title">
    <h2 id="filter-title">Filter</h2>
    @foreach(['jenis'=>['Jenis tempat',$placeTypes],'periode'=>['Periode sewa',$periods]] as $field=>[$legend,$options])
        <fieldset>
            <legend>{{$legend}}</legend>
            <div class="filter-chips">
                @foreach($options as $value=>$label)
                    <label class="filter-chip">
                        <input type="checkbox" name="{{$field}}[]" value="{{$value}}" @checked(in_array($value,(array)request($field,[]),true))>
                        <span>{{$label}}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
    <fieldset class="filter-price">
        <legend>Rentang harga sewa</legend>
        @foreach(['min'=>['Minimum','Rp 1.000.000'],'max'=>['Maksimum','Rp 7.000.000']] as $field=>[$label,$example])
            <label>{{$label}}
                <input type="text" inputmode="numeric" name="{{$field}}" data-money
                    value="{{request()->filled($field)?number_format((float)request($field),0,',','.'):''}}" placeholder="{{$example}}">
            </label>
        @endforeach
    </fieldset>
    <fieldset>
        <legend>Fasilitas</legend>
        <div class="filter-chips">
            @foreach($filterFacilities as $facility)
                <label class="filter-chip">
                    <input type="checkbox" name="fasilitas[]" value="{{$facility->id_fasilitas}}" @checked(in_array($facility->id_fasilitas,(array)request('fasilitas',[])))>
                    <span>{{$facility->nama_fasilitas}}</span>
                </label>
            @endforeach
        </div>
    </fieldset>
    <div class="filter-actions"><button>Terapkan Filter</button><a href="/cari">Reset</a></div>
</aside>
