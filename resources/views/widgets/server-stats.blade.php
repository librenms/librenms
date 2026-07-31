<div class="col-sm-{{ $columns }}">
    <div id="gauge-cpu-{{ $id }}" class="gauge-container"></div>
</div>

@foreach($mempools as $index => $mem)
    <div class="col-sm-{{ $columns }}">
        <div id="gauge-mem-{{ $id }}-{{ $index }}" class="gauge-container"></div>
    </div>
@endforeach

@foreach($disks as $index => $disk)
    <div class="col-sm-{{ $columns }}">
        <div id="gauge-disk-{{ $id }}-{{ $index }}" class="gauge-container"></div>
    </div>
@endforeach

<script type="text/javascript">
    $(document).ready(function() {

        new JustGage({
            id: "gauge-cpu-{{ $id }}",
            title: "{{ __('CPU Usage') }}",
            value: {{ (float) ($cpu ?? 0) }},
            min: 0,
            max: 100,
            symbol: '%',
            valueFontSize: '15px',
            titleFontColor: '#999999'
        });

        @foreach($mempools as $index => $mem)
            new JustGage({
                id: "gauge-mem-{{ $id }}-{{ $index }}",
                title: "{!! addslashes($mem->descr) !!}",
                value: {{ (float) $mem->used }},
                min: 0,
                max: {{ (float) ($mem->total > 0 ? $mem->total : 100) }},
                label: "{{ $unit }}",
                valueFontSize: '15px',
                labelMinFontSize: '10px',
                titleFontColor: '#999999'
            });
        @endforeach

        @foreach($disks as $index => $disk)
            new JustGage({
                id: "gauge-disk-{{ $id }}-{{ $index }}",
                title: "{!! addslashes($disk->descr) !!}",
                value: {{ (float) $disk->used }},
                min: 0,
                max: {{ (float) ($disk->total > 0 ? $disk->total : 100) }},
                label: "{{ $unit }}",
                valueFontSize: '15px',
                labelMinFontSize: '10px',
                titleFontColor: '#999999'
            });
        @endforeach
    });
</script>

<style>
    .gauge-container {
        height: 120px;
        margin-bottom: 15px;
    }
</style>
