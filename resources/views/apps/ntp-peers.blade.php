<x-panel :title="__('NTP Peers')">
    <x-slot:table>
        <div class="table-responsive">
            <table class="table table-condensed table-striped">
                <thead>
                    <tr>
                        <th>{{ __('Peer') }}</th>
                        <th>{{ __('Stratum') }}</th>
                        <th>{{ __('Peer Reference') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($peers as $peer)
                        <tr @class(['danger' => ($peer['status'] ?? null) == 2])>
                            <td>{{ $peer['peer'] ?? '' }}:{{ $peer['port'] ?? '' }}</td>
                            <td>{{ $peer['stratum'] ?? '' }}</td>
                            <td>{{ $peer['peerref'] ?? '' }}</td>
                            <td>{{ ($peer['status'] ?? null) == 2 ? ($peer['error'] ?? '') : __('OK') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-slot:table>
</x-panel>
