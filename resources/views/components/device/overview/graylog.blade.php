@props(['device', 'config'])

@if($config)
    <div x-data="graylogOverview(@js([
            'url' => $config['url'],
            'device' => $device->device_id,
            'rowCount' => $config['rowCount'],
            'loglevel' => $config['loglevel'],
        ]))" x-init="load" x-show="loading || rows.length">
        <x-device.overview.panel :title="__('Recent Graylog')" icon="fa fa-clone" :href="route('device.graylog', ['device' => $device->device_id])">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead><tr><th></th><th>{{ __('Timestamp') }}</th><th>{{ __('Level') }}</th><th>{{ __('Message') }}</th><th>{{ __('Facility') }}</th></tr></thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td x-html="row.severity"></td>
                                <td x-text="row.timestamp"></td>
                                <td x-text="row.level"></td>
                                <td x-text="row.message"></td>
                                <td x-text="row.facility"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="loading" class="tw:p-3 tw:text-center tw:text-gray-500">{{ __('Loading') }}…</div>
            </div>
        </x-device.overview.panel>
    </div>

    @pushOnce('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('graylogOverview', (config) => ({
                    loading: true,
                    rows: [],

                    async load() {
                        const body = new URLSearchParams({
                            device: config.device,
                            rowCount: config.rowCount,
                            loglevel: config.loglevel,
                        });
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;

                        try {
                            const response = await fetch(config.url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                                    ...(token ? {'X-CSRF-TOKEN': token} : {}),
                                },
                                body,
                            });

                            if (response.ok) {
                                const data = await response.json();
                                this.rows = data.rows ?? [];
                            }
                        } finally {
                            this.loading = false;
                        }
                    },
                }));
            });
        </script>
    @endPushOnce
@endif
