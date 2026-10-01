<div class="tw:grid {{ $gridCols ?? 'tw:grid-cols-3' }} tw:gap-2 tw:h-full tw:w-full tw:items-stretch tw:overflow-y-auto" style="grid-template-rows: repeat({{ $gridRows ?? 1 }}, minmax(65px, 1fr));">
    @if($showCpu ?? true)
        <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:w-full tw:h-full tw:min-h-0">
            <div id="gauge-cpu-{{ $id }}" class="gauge-container"></div>
            <div class="gauge-title">{{ __('widgets.server-stats.cpu_usage') }}</div>
        </div>
    @endif

    @foreach($mempools as $index => $mem)
        <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:w-full tw:h-full tw:min-h-0">
            <div id="gauge-mem-{{ $id }}-{{ $index }}" class="gauge-container"></div>
            <div class="gauge-title">{{ $mem['descr'] }}</div>
        </div>
    @endforeach

    @foreach($disks as $index => $disk)
        <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:w-full tw:h-full tw:min-h-0">
            <div id="gauge-disk-{{ $id }}-{{ $index }}" class="gauge-container"></div>
            <div class="gauge-title">{{ $disk['descr'] }}</div>
        </div>
    @endforeach
</div>

<script type="text/javascript">
    $(document).ready(function () {

        @if($showCpu ?? true)
            new JustGage({
                id: "gauge-cpu-{{ $id }}",
                value: {{ (float) ($cpu ?? 0) }},
                min: 0,
                max: 100,
                symbol: '%',
                relativeGaugeSize: true,
                gaugeWidthScale: 0.6
            });
        @endif

        @foreach($mempools as $index => $mem)
        new JustGage({
            id: "gauge-mem-{{ $id }}-{{ $index }}",
            value: {{ (float) $mem['used'] }},
            min: 0,
            max: {{ (float) ($mem['total'] > 0 ? $mem['total'] : 100) }},
            label: "{{ $mem['unit'] }}",
            relativeGaugeSize: true,
            gaugeWidthScale: 0.6
        });
        @endforeach

        @foreach($disks as $index => $disk)
        new JustGage({
            id: "gauge-disk-{{ $id }}-{{ $index }}",
            value: {{ (float) $disk['used'] }},
            min: 0,
            max: {{ (float) ($disk['total'] > 0 ? $disk['total'] : 100) }},
            label: "{{ $disk['unit'] }}",
            relativeGaugeSize: true,
            gaugeWidthScale: 0.6
        });
        @endforeach
    });
</script>

<style>
    .gauge-container {
        width: 100%;
        flex: 1 1 auto;
        min-height: 40px;
        max-height: 120px;
    }

    .gauge-title {
        font-weight: bold;
        font-size: 11px;
        line-height: 1.1;
        margin-top: 2px;
        margin-bottom: 2px !important;
        word-break: break-word;
        flex: 0 0 auto;
    }

    /* Dark Mode Styling for JustGage SVG Elements */
    .dark .gauge-container svg path[fill="#edebeb"] {
        fill: #3e444c !important;
    }

    .dark .gauge-container svg text[fill="#010101"],
    .dark .gauge-container svg text[fill="#000000"] {
        fill: #f9fafb !important;
    }

    .dark .gauge-container svg text[fill="#b3b3b3"] {
        fill: #9ca3af !important;
    }
</style>
