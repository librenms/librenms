<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped tw:mb-0">
        <thead>
            <tr>
                <th class="col-sm-2">{{ __('Name') }}</th>
                <th class="col-sm-1">{{ __('Check Type') }}</th>
                <th class="col-sm-1">{{ __('Remote Host') }}</th>
                <th class="col-sm-3">{{ __('Message') }}</th>
                <th class="col-sm-2">{{ __('Description') }}</th>
                <th class="col-sm-1">{{ __('Last Changed') }}</th>
                <th class="col-sm-1">{{ __('Enabled') }}</th>
                <th class="col-sm-1"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($services as $service)
            @php($canUpdate = Gate::allows('update', $service))
            <tr>
                <td>
                    <span class="alert-status {{ $statusClass($service) }}" title="{{ $statusText($service) }}">
                        <span class="device-services-page text-nowrap">{{ $service->displayName() }}</span>
                    </span>
                    @if($service->service_ignore)
                        <i class="fa fa-bell-slash text-muted" title="{{ __('Alerts ignored') }}" aria-hidden="true"></i>
                    @endif
                </td>
                <td class="text-muted">{{ $service->service_type }}</td>
                <td class="text-muted">{!! nl2br(e($service->service_ip)) !!}</td>
                <td>{!! nl2br(e(trim((string) $service->service_message))) !!}</td>
                <td class="text-muted">{{ $service->service_desc }}</td>
                <td class="text-muted">
                    {{ $service->service_changed ? \LibreNMS\Util\Time::formatInterval(time() - $service->service_changed) : __('Waiting for first check') }}
                </td>
                <td title="{{ __('Disable polling and alerting') }}">
                    <x-toggle :checked="! $service->service_disabled"
                              :disabled="! $canUpdate"
                              :aria-label="__('Enabled')"
                              x-bind:disabled="busy || {{ Js::from(! $canUpdate) }}"
                              x-on:change="toggleService($el, {{ Js::from(route('services.update', $service)) }})" />
                </td>
                <td class="text-right">
                    <div class="btn-group">
                        @if($canUpdate)
                            <button type="button" class="btn btn-primary btn-sm" aria-label="{{ __('Edit') }}"
                                    x-bind:disabled="busy"
                                    x-on:click="editService(@js(route('services.edit', $service)), @js(route('services.update', $service)))">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </button>
                        @endif
                        @can('delete', $service)
                            <button type="button" class="btn btn-danger btn-sm" aria-label="{{ __('Delete') }}"
                                    x-bind:disabled="busy" x-on:click="confirmDeleteService(@js(route('services.destroy', $service)))">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
            @if($graphs)
                @foreach($dataSources($service) as $ds => $title)
                    <tr>
                        <td colspan="8" class="tw:p-2.5 tw:bg-gray-50 tw:dark:bg-dark-gray-500">
                            <x-graph-row type="service_graph" :device="$service->device_id" :title="$title" :vars="['id' => $service->service_id, 'ds' => $ds]" columns="responsive" />
                        </td>
                    </tr>
                @endforeach
            @endif
        @empty
            <tr>
                <td colspan="8" class="text-center tw:p-5"><em>{{ __('No services found.') }}</em></td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
