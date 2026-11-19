<x-option-bar :name="__('NTP Peers')" :options="$peerViewOptions" :selected="$peerView">
</x-option-bar>
<x-option-bar :name="__('Graphs')" :options="$peerGraphOptions" :selected="$peerGraph">
</x-option-bar>

<x-panel>
    <x-slot:table>
        <div class="table-responsive">
            <table id="ntp-table" class="table table-condensed table-striped">
                <thead>
                    <tr>
                        <th data-column-id="device">{{ __('Device') }}</th>
                        <th data-column-id="peer">{{ __('Peer') }}</th>
                        <th data-column-id="stratum" data-type="numeric">{{ __('Stratum') }}</th>
                        <th data-column-id="error">{{ __('Error') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </x-slot:table>
</x-panel>

@push('scripts')
<script>
    $('#ntp-table').bootgrid({
        ajax: true,
        post: () => ({
            id: 'app_ntp',
            view: @js($peerView),
            graph: @js($peerGraph),
        }),
        url: @js(url('ajax_table.php')),
    });
</script>
@endpush
