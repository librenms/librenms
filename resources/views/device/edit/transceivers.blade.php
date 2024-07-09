<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>{{ __('Port') }}</th>
        <th>{{ __('port.transceiver') }}</th>
        <th>{{ __('port.transceiver_metrics.fields.type') }}</th>
        <th>{{ __('port.transceiver_metrics.fields.value') }}</th>
        <th>{{ __('port.transceiver_metrics.fields.threshold_min_critical') }}</th>
        <th>{{ __('port.transceiver_metrics.fields.threshold_min_warning') }}</th>
        <th>{{ __('port.transceiver_metrics.fields.threshold_max_warning') }}</th>
        <th>{{ __('port.transceiver_metrics.fields.threshold_max_critical') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($device->transceivers as $transceiver)
        @foreach($transceiver->metrics as $metric)
            <tr x-data="transceiverMetric({{ $metric->status->asSeverity()->value }})">
                <td>
                    <x-port-link :port="$transceiver->port"></x-port-link>
                </td>
                <td>
                    {{ $transceiver->vendor }} {{ $transceiver->model }}
                </td>
                <td>
                    {{ trans_choice('port.transceivers.metrics.' . $metric->type,  $transceiver->channels, ['channel' => $metric->channel]) }}
                </td>
                <td class="tw-whitespace-nowrap tw-text-2xl">
                    <x-label :status="$metric->status->asSeverity()" x-bind:class="{
                     'label-success': status === 1,
                     'label-info': status === 2,
                     'label-primary': status === 3,
                     'label-warning': status === 4,
                     'label-danger': status === 5,
                     'label-default': typeof status !== 'number' || status < 1 || status > 5
                    }">
                        {{ $metric->value }} {{ __('port.transceivers.units.' . $metric->type) }}
                    </x-label>
                </td>
                <td>
                    <x-input type="number" id="{{ $metric->id }}_ic" name="threshold_min_critical"
                             value="{{ $metric->threshold_min_critical }}"
                             x-data="transceiverMetricSetting({{ $metric->threshold_min_critical }})"
                             x-on:blur="updateData" x-on:keyup.enter="updateData"
                             x-bind:class="{'tw-shadow-inner-glow tw-shadow-green-500 tw-border-green-500': updated === true, 'tw-shadow-inner-glow tw-shadow-red-500 tw-border-red-500': updated === false}"></x-input>
                </td>
                <td>
                    <x-input type="number" id="{{ $metric->id }}_iw" name="threshold_min_warning"
                             value="{{ $metric->threshold_min_warning }}"
                             x-data="transceiverMetricSetting({{ $metric->threshold_min_warning }})"
                             x-on:blur="updateData" x-on:keyup.enter="updateData"
                             x-bind:class="{'tw-shadow-inner-glow tw-shadow-green-500 tw-border-green-500': updated === true, 'tw-shadow-inner-glow tw-shadow-red-500 tw-border-red-500': updated === false}"></x-input>
                </td>
                <td>
                    <x-input type="number" id="{{ $metric->id }}_aw" name="threshold_max_warning"
                             value="{{ $metric->threshold_max_warning }}"
                             x-data="transceiverMetricSetting({{ $metric->threshold_max_warning }})"
                             x-on:blur="updateData" x-on:keyup.enter="updateData"
                             x-bind:class="{'tw-shadow-inner-glow tw-shadow-green-500 tw-border-green-500': updated === true, 'tw-shadow-inner-glow tw-shadow-red-500 tw-border-red-500': updated === false}"></x-input>
                </td>
                <td>
                    <x-input type="number" id="{{ $metric->id }}_ac" name="threshold_max_critical"
                             value="{{ $metric->threshold_max_critical }}"
                             x-data="transceiverMetricSetting({{ $metric->threshold_max_critical }})"
                             x-on:blur="updateData" x-on:keyup.enter="updateData"
                             x-bind:class="{'tw-shadow-inner-glow tw-shadow-green-500 tw-border-green-500': updated === true, 'tw-shadow-inner-glow tw-shadow-red-500 tw-border-red-500': updated === false}"></x-input>
                </td>
            </tr>
        @endforeach
    @endforeach

    </tbody>
</table>
<script>
    document.addEventListener("alpine:init", () => {
        Alpine.data("transceiverMetric", (initialStatus) => ({
            status: initialStatus,
            setStatus(newStatus) {
                this.status = newStatus;
            }
        }));

        Alpine.data("transceiverMetricSetting", (initialValue) => ({
            value: initialValue,
            updated: null,
            updateData(event) {
                if (event.target.value != this.value) {
                    let url = '{{ route('transceiver_metric.update', [':metric']) }}'
                        .replace(':metric', event.target.id.slice(0, -3));

                    fetch(url, {
                        method: 'put',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector("meta[name=csrf-token]").content
                        },
                        body: JSON.stringify({field: event.target.name, value: event.target.value})
                    }).then(async (response) => {
                        let data = await response.json();

                        if (response.ok) {
                            this.value = event.target.value;
                            this.updated = true;
                            this.setStatus(data.metricStatus);
                        } else {
                            this.updated = false;
                            event.target.value = this.value;
                            window.toastr.error(data.message ? data.message : response.statusText);
                        }
                        setTimeout(() => this.updated = null, 3000);
                    }).catch((error) => {
                        event.target.value = this.value;
                        this.updated = false;
                        window.toastr.error(error.message);
                        setTimeout(() => this.updated = null, 3000);
                    });
                }
            }
        }));
    });
</script>
