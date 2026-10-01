<x-panel>
    <x-slot:slot class="tw:p-0!">
    <table class="table table-hover table-condensed table-striped tw:mt-1 tw:mb-0!">
        <thead>
            <tr>
                @if($showDevice ?? false)
                    <th>{{ __('Device') }}</th>
                @endif
                <th>{{ __('Port') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Enabled') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Current MACs') }}</th>
                <th>{{ __('Max MACs') }}</th>
                <th>{{ __('Violation Action') }}</th>
                <th>{{ __('Violations') }}</th>
                <th>{{ __('Last MAC') }}</th>
                <th>{{ __('Sticky') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($portSecurity as $entry)
            @include('port-security.includes.row', ['entry' => $entry, 'showDevice' => $showDevice ?? false])
        @empty
            <tr>
                <td colspan="{{ ($showDevice ?? false) ? 11 : 10 }}" class="text-center">{{ __('No port security entries matched the current filters.') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    @if($portSecurity instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="tw:flex tw:flex-row-reverse tw:m-3">
            {{ $portSecurity->links('pagination::tailwind', ['perPage' => $perPage ?? null]) }}
            @isset($paginationOptions)
                <x-select :options="$paginationOptions"
                          x-on:change="
                          const params = new URLSearchParams(window.location.search);
                          params.set('perPage', $event.target.value);
                          params.delete('page');
                          window.location.search = params.toString();
                          " x-data="{}"
                          selected="{{ $perPage }}"
                          name="perPage"
                          label="{{ __('Per Page') }}"
                          class="tw:mx-4"></x-select>
            @endisset
        </div>
    @endif
    </x-slot:slot>
</x-panel>
