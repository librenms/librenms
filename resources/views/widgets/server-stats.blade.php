<div class="col-sm-{{ $columns }}">
    <div class="gauge-title">@lang('CPU Usage')</div>
    <div
        id="cpu-{{ $id }}"
        class="gauge-{{ $id }} gauge-container"
        data-value="{{ $cpu }}"
        data-max="100"
        data-symbol="%"
    ></div>
</div>

@foreach($mempools as $key => $mem)
    <div class="col-sm-{{ $columns }}">
        <div class="gauge-title">{{ $mem->mempool_descr}} @lang('Usage')</div>
        <div
            id="mem-{{ $key }}-{{ $id }}"
            class="gauge-{{ $id }} gauge-container"
            data-value="{{ $mem->used}}"
            data-max="{{ $mem->total}}"
            data-label="Mbytes"
        ></div>
    </div>
@endforeach

@foreach($disks as $key => $disk)
    <div class="col-sm-{{ $columns }}">
        <div class="gauge-title">{{ $disk->storage_descr}} @lang('Usage')</div>
        <div
            id="disk-{{ $key }}-{{ $id }}"
            class="gauge-{{ $id }} gauge-container"
            data-value="{{ $disk->used}}"
            data-max="{{ $disk->total}}"
            data-label="Mbytes"
        ></div>
    </div>
@endforeach

<script type='text/javascript'>
    $('.gauge-{{ $id }}').each(function() {
        new JustGage({
            id: this.id,
            min: 0,
            valueFontSize: '2px'
        });
    });
</script>

<style>
    .gauge-title {
        text-align:center;
        font-family: Arial, sans-serif; font-size: 0.8em; font-weight: bold;
        color:#999999;
    }
    .gauge-container {
        height: 80px;
        margin-bottom: 15px;
    }
</style>
