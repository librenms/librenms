@props(['filterName', 'filterFields', 'filter', 'count', 'lastColumn', 'emptyMessage'])
<x-panel>
    <x-slot name="title">
        <div class="tw:flex tw:justify-between tw:items-center tw:gap-3 tw:flex-wrap">
            <x-filter :name="$filterName" :fields="$filterFields" :initial="$filter" :reload="true" />
            <span>
                <i class="fa fa-archive fa-fw fa-lg" aria-hidden="true"></i>
                {{ trans_choice(':count package|:count packages', $count) }}
            </span>
        </div>
    </x-slot>
    <x-slot name="table">
        <div class="table-responsive">
            <table class="table table-condensed table-hover table-striped">
                <thead>
                <tr>
                    <th>{{ __('Package') }}</th>
                    <th>{{ __('Version') }}</th>
                    <th>{{ __('Arch') }}</th>
                    <th>{{ __('Manager') }}</th>
                    <th class="tw:text-right">{{ $lastColumn }}</th>
                </tr>
                </thead>
                <tbody>
                @if($slot->hasActualContent())
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="5" class="tw:text-center tw:p-5"><em>{{ $emptyMessage }}</em></td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </x-slot>
    @isset($footer)
        <x-slot name="footer">
            {{ $footer }}
        </x-slot>
    @endisset
</x-panel>
