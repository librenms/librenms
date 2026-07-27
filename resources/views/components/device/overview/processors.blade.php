@props(['device'])

@if($device->processors->isNotEmpty())
    <x-device.overview.panel :title="__('Processors')" icon="fa fa-microchip"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=processor'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @if(\App\Facades\LibrenmsConfig::get('cpu_details_overview'))
                @foreach($device->processors as $processor)
                    <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                        <span class="tw:w-36 tw:truncate">{{ $processor->getFormattedDescription() }}</span>
                        <x-graph type="processor_usage" :vars="['id' => $processor->processor_id]" width="100" height="24" popup
                                 :popup-title="$device->display . ' - ' . $processor->getFormattedDescription()" />
                        <x-device.overview.percentage class="tw:ml-auto tw:max-w-[200px] tw:flex-1" :percent="$processor->processor_usage" :warning="$processor->processor_perc_warn"
                            :left_text="''" :right_text="$processor->processor_usage . '%'" />
                    </div>
                @endforeach
            @else
                <div class="tw:p-2">
                    <x-graph type="device_processor" :device="$device" aspect="wide" class="tw:w-full" img-class="tw:w-full tw:h-auto"
                             popup :popup-title="$device->display . ' - ' . __('CPU usage')" />
                </div>
                @foreach($device->processors->groupBy('processor_type') as $processors)
                    @php
                        $usage = (int) ceil($processors->avg('processor_usage'));
                        $warning = $processors->avg('processor_perc_warn');
                    @endphp
                    <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                        <span class="tw:w-36 tw:truncate">x{{ $processors->count() }} {{ $processors->first()->getFormattedDescription() }}</span>
                        <x-device.overview.percentage class="tw:ml-auto tw:max-w-[400px] tw:flex-1" :percent="$usage" :warning="$warning"
                            :left_text="''" :right_text="$usage . '%'" />
                    </div>
                @endforeach
            @endif
        </div>
    </x-device.overview.panel>
@endif
