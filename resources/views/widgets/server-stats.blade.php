<div class="col-sm-{{ $columns }}">
    <div
        id="cpu-{{ $id }}"
        class="guage-{{ $id }}"
        data-value="{{ $cpu }}"
        data-max="100"
        data-symbol="%"
        data-title="CPU Usage"
    ></div>
</div>

@foreach($mempools as $key => $mem)
    <div class="col-sm-{{ $columns }}">
        <div
            id="mem-{{ $key }}-{{ $id }}"
            class="guage-{{ $id }}"
            data-value="{{ $mem['used'] }}"
            data-max="{{ $mem['total'] }}"
            data-label="Mbytes"
            data-title="{{ $mem['mempool_descr'] }} Usage"
        ></div>
    </div>
@endforeach

@foreach($disks as $key => $disk)
    <div class="col-sm-{{ $columns }}">
        <div
            id="disk-{{ $key }}-{{ $id }}"
            class="guage-{{ $id }}"
            data-value="{{ $disk['used'] }}"
            data-max="{{ $disk['total'] }}"
            data-label="Mbytes"
            data-title="{{ $disk['storage_descr'] }} Usage"
        ></div>
    </div>
@endforeach

<script type='text/javascript'>
    loadjs('js/raphael-min.js', function() {
        loadjs('js/justgage.js', function() {
            $('.guage-{{ $id }}').each(function() {
                new JustGage({
                    id: this.id,
                    min: 0,
                    valueFontSize: '2px'
                });
            });
        });
    });
</script>
