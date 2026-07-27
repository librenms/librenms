@props(['device'])

@if($device->services->isNotEmpty())
    @php
        $counts = [
            'total' => $device->services->count(),
            'ok' => $device->services->where('service_status', 0)->count(),
            'warning' => $device->services->where('service_status', 1)->count(),
            'critical' => $device->services->where('service_status', 2)->count(),
        ];
        $servicesUrl = route('device', ['device' => $device->device_id, 'tab' => 'services']);
    @endphp
    <x-device.overview.panel :title="__('Services')" icon="fa fa-cogs" :href="$servicesUrl">
        <div class="tw:flex tw:flex-wrap tw:gap-3 tw:p-3">
            @foreach([
                [__('Total'), $counts['total'], 'lnms-btn-default'],
                [__('Ok'), $counts['ok'], 'lnms-btn-success'],
                [__('Warning'), $counts['warning'], 'lnms-btn-warning'],
                [__('Critical'), $counts['critical'], 'lnms-btn-danger'],
            ] as [$label, $count, $class])
                <a class="lnms-btn {{ $class }}" href="{{ $servicesUrl }}">{{ $label }}: <span class="lnms-btn-badge">{{ $count }}</span></a>
            @endforeach
        </div>
        <div class="tw:flex tw:flex-wrap tw:gap-2 tw:border-t tw:border-gray-300 tw:bg-neutral-100 tw:p-3 tw:dark:border-zinc-800 tw:dark:bg-dark-gray-200">
            @foreach($device->services as $service)
                @php
                    $status = match((int) $service->service_status) { 0 => 'success', 1 => 'warning', 2 => 'danger', default => 'default' };
                    $type = strtolower((string) $service->service_type);
                    $name = filled($service->service_name) && $service->service_name !== $type ? "{$service->service_name} ($type)" : $type;
                @endphp
                <span class="label label-{{ $status }}" title="{{ $service->service_message }}">{{ $name }}</span>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
