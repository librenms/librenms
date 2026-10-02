@if($device->services->isNotEmpty())
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
            @foreach($serviceLabels as $data)
                <span @class([
                    'lnms-btn',
                    'lnms-btn-success' => $data['status'] === 'success',
                    'lnms-btn-warning' => $data['status'] === 'warning',
                    'lnms-btn-danger' => $data['status'] === 'danger',
                    'lnms-btn-default' => $data['status'] === 'default',
                ]) title="{{ $data['service']->service_message }}">{{ $data['name'] }}</span>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
