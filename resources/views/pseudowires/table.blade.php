@php
    $columns = [
        __('PW ID'),
        ...($showDevice ? [__('Device')] : []),
        __('Local PW Name'),
        __('Local Port'),
        ['label' => '', 'class' => 'tw:w-10'],
        __('Remote Device/PW Name'),
        __('Remote Port'),
    ];
@endphp

<x-table striped :columns="$columns" :rows="$circuits" :empty="$showDevice ? __('No pseudowires found.') : __('No pseudowires found for this device.')">
    <x-slot:body>
        @foreach($circuits as ['pseudowire' => $pseudowire, 'peer' => $peer])
            <tr>
                <td class="tw:text-lg tw:p-1 tw:align-middle">{{ $pseudowire->cpwVcID }}</td>
                @if($showDevice)
                    <td class="tw:align-middle"><x-device-link :device="$pseudowire->device" /></td>
                @endif
                <td>
                    {{ $pseudowire->pw_descr }}
                    <br><span class="tw:text-xs">{{ $pseudowire->pw_type }} {{ $pseudowire->pw_psntype }}</span>
                </td>
                <td><x-pseudowire.port :pseudowire="$pseudowire" /></td>
                <td class="tw:align-middle tw:text-center">
                    <i class="fa fa-arrows-alt tw:text-2xl" aria-hidden="true"></i>
                </td>
                <td class="tw:align-middle">
                    @if($remoteDevice = $peer?->device ?? $pseudowire->peerDevice)
                        <x-device-link :device="$remoteDevice" />
                    @else
                        <span class="tw:italic">{{ __('unresolved remote device') }}</span>
                    @endif
                    @if($peer?->pw_descr)
                        <br><span class="tw:text-xs">{{ $peer->pw_descr }}</span>
                    @endif
                </td>
                <td><x-pseudowire.port :pseudowire="$peer" /></td>
            </tr>

            @if($view === 'minigraphs')
                <tr>
                    <td colspan="{{ $showDevice ? 2 : 1 }}"></td>
                    <td colspan="2"><x-pseudowire.graphs :port="$pseudowire->port" /></td>
                    <td></td>
                    <td colspan="2"><x-pseudowire.graphs :port="$peer?->port" /></td>
                </tr>
            @endif
        @endforeach
    </x-slot:body>
</x-table>
