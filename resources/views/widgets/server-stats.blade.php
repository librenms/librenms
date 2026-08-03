@php
    $gridCols = match($columns) {
        1 => 'tw:grid-cols-1',
        2 => 'tw:grid-cols-2',
        4 => 'tw:grid-cols-4',
        5 => 'tw:grid-cols-5',
        6 => 'tw:grid-cols-6',
        12 => 'tw:grid-cols-12',
        default => 'tw:grid-cols-3',
    };
@endphp

<div class="tw:grid {{ $gridCols }} tw:gap-4">
    <div class="tw:text-center">
        <div id="gauge-cpu-{{ $id }}" class="gauge-container"></div>
        <div class="gauge-title">{{ __('CPU Usage') }}</div>
    </div>

    @foreach($mempools as $index => $mem)
        <div class="tw:text-center">
            <div id="gauge-mem-{{ $id }}-{{ $index }}" class="gauge-container"></div>
            <div class="gauge-title">{{ $mem->descr }}</div>
        </div>
    @endforeach

    @foreach($disks as $index => $disk)
        <div class="tw:text-center">
            <div id="gauge-disk-{{ $id }}-{{ $index }}" class="gauge-container"></div>
            <div class="gauge-title">{{ $disk->descr }}</div>
        </div>
    @endforeach
</div>

<script type="text/javascript">
    $(document).ready(function () {

        new JustGage({
            id: "gauge-cpu-{{ $id }}",
            title: "{{ __('CPU Usage') }}",
            value: {{ (float) ($cpu ?? 0) }},
            min: 0,
            max: 100,
            symbol: '%',
            valueFontSize: '15px'
        });

        @foreach($mempools as $index => $mem)
        new JustGage({
            id: "gauge-mem-{{ $id }}-{{ $index }}",
            title: "{!! addslashes($mem->descr) !!}",
            value: {{ (float) $mem->used }},
            min: 0,
            max: {{ (float) ($mem->total > 0 ? $mem->total : 100) }},
            label: "{{ $mem->unit }}",
            valueFontSize: '15px',
            labelMinFontSize: '10px'
        });
        @endforeach

        @foreach($disks as $index => $disk)
        new JustGage({
            id: "gauge-disk-{{ $id }}-{{ $index }}",
            title: "{!! addslashes($disk->descr) !!}",
            value: {{ (float) $disk->used }},
            min: 0,
            max: {{ (float) ($disk->total > 0 ? $disk->total : 100) }},
            label: "{{ $disk->unit }}",
            valueFontSize: '15px',
            labelMinFontSize: '10px'
        });
        @endforeach
    });
</script>

<style>
    .gauge-container {
        height: 120px;
    }

    .gauge-title {
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 15px !important;
        word-break: break-word;
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
