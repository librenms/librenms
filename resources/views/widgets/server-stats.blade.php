<div class="col-sm-{{ $columns }}">
    <div class="JustGage_Title">@lang('CPU Usage')</div>
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
        <div class="JustGage_Title">{{ $mem['mempool_descr'] }} @lang('Usage')</div>
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
        <div class="JustGage_Title">{{ $disk['storage_descr'] }} @lang('Usage')</div>
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
    $('.guage-{{ $id }}').each(function() {
        new JustGage({
            id: this.id,
            min: 0,
            valueFontSize: '2px'
        });
    });
</script>

<style>
    .JustGage_Title
    {
        text-align:center;
        font-family: Arial; font-size: 20px; font-weight: bold;
        color:#999999;
    }
</style>
