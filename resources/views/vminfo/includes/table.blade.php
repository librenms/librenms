@php
    // the same options the other paginated tables offer
    $paginationOptions ??= [50, 100, 250, 'all'];
    $showDevice ??= false;
@endphp
<x-panel>
    <x-slot:slot class="tw:p-0!">
    <table class="table table-hover table-condensed table-striped tw:mt-1 tw:mb-0!">
        <thead>
            <tr>
                <th>{{ __('VM Name') }}</th>
                @if($showDevice)
                    <th>{{ __('Host') }}</th>
                @endif
                <th>{{ __('Power Status') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Operating System') }}</th>
                <th class="tw:text-right">{{ __('Memory') }}</th>
                <th class="tw:text-right">{{ __('vCPUs') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($vms as $vm)
            <tr>
                <td>
                    @if($vm->parentDevice)
                        <x-device-link :device="$vm->parentDevice" />
                    @else
                        {{ $vm->vmwVmDisplayName }}
                    @endif
                </td>
                @if($showDevice)
                    <td><x-device-link :device="$vm->device" /></td>
                @endif
                <td>
                    <span class="label {{ $vm->stateLabel[1] }} tw:inline-block tw:min-w-10">{{ $vm->stateLabel[0] }}</span>
                </td>
                <td>{{ $vm->vm_type }}</td>
                <td>{{ $vm->operatingSystem }}</td>
                <td class="tw:text-right">{{ $vm->memoryFormatted }}</td>
                <td class="tw:text-right">{{ $vm->vmwVmCpus }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $showDevice ? 7 : 6 }}" class="text-center">{{ __('No virtual machines matched the current filters.') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <div class="tw:flex tw:flex-row-reverse tw:m-3">
        {{ $vms->links('pagination::tailwind', ['perPage' => $perPage]) }}
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
    </div>
    </x-slot:slot>
</x-panel>
