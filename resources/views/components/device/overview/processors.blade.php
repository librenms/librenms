@if($device->processors->isNotEmpty())
    <x-device.overview.panel :title="__('Processors')" icon="fa fa-microchip"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=processor'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @if($showDetails)
                @foreach($device->processors as $processor)
                    <div class="tw:flex tw:min-w-0 tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                        <span class="tw:min-w-0 tw:flex-1 tw:truncate">{{ $processor->getFormattedDescription() }}</span>
                        <div class="tw:hidden tw:w-20 tw:shrink-0 tw:justify-end tw:lg:flex">
                            <x-graph type="processor_usage" :vars="['id' => $processor->processor_id]" width="80" height="20" popup
                                     :popup-title="$device->display . ' - ' . $processor->getFormattedDescription()" />
                        </div>
                        <x-device.overview.percentage class="tw:w-full tw:min-w-0 tw:max-w-50 tw:flex-1" :percent="$processor->processor_usage" :warning="$processor->processor_perc_warn"
                            :left_text="''" :right_text="$processor->processor_usage . '%'" graph_type="processor_usage"
                            :graph_vars="['id' => $processor->processor_id]" :graph_title="$device->display . ' - ' . $processor->getFormattedDescription()" />
                    </div>
                @endforeach
            @else
                <div class="tw:p-2">
                    <x-graph type="device_processor" :device="$device" aspect="wide" :columns="['md' => 2]" class="tw:w-full" img-class="tw:w-full tw:h-auto"
                             popup :popup-title="$device->display . ' - ' . __('CPU usage')" />
                </div>
                @foreach($processorGroups as $data)
                    <div class="tw:flex tw:min-w-0 tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                        <span class="tw:min-w-0 tw:flex-1 tw:truncate">x{{ $data['processors']->count() }} {{ $data['processors']->first()->getFormattedDescription() }}</span>
                        <x-device.overview.percentage class="tw:ml-auto tw:w-full tw:min-w-0 tw:max-w-100 tw:flex-1" :percent="$data['usage']" :warning="$data['warning']"
                            :left_text="''" :right_text="$data['usage'] . '%'" graph_type="device_processor"
                            :graph_vars="['device' => $device->device_id]" :graph_title="$device->display . ' - ' . __('CPU usage')" />
                    </div>
                @endforeach
            @endif
        </div>
    </x-device.overview.panel>
@endif
