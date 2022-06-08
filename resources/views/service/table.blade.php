@if(isset($services) && $services->isNotEmpty())
    <div x-data="{editService: 0, deleteId: 0, deleteText: '', deleteService() {
    if (this.deleteId) {
        fetch('{{ route('services.destroy', ['service' => '?']) }}'.replace('?', this.deleteId), {
        method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.head.querySelector('meta[name=\'csrf-token\']').content
            }
        })
        .then(response => response.json())
        .then(result => {
            console.log(result, Object.keys(this.$refs));
            if (result.status) {
                document.querySelector('#service_row_'+result.service_id).remove();
                toastr.success(result.message);
            } else {
                toastr.error(result.message);
            }
            this.deleteId = 0;
         })
         .catch(result => this.deleteId = 0);
    }
}}">
        <table class="table table-hover table-condensed !tw-mb-0">
            <thead>
            <tr>
                <th>
                    <div class="col-sm-1"><span class="device-services-page">{{ __('service.fields.service_type') }}</span></div>
                    <div class="col-sm-2">{{ __('service.fields.service_name') }} / {{ __('service.fields.service_ip') }}</div>
                    @if($view != 'basic')
                        <div class="col-sm-2">{{ __('service.fields.service_desc') }}</div>
                        <div class="col-sm-4">{{ __('service.fields.service_message') }}</div>
                    @endif
                    <div class="col-sm-2">{{ __('service.fields.service_changed') }}</div>
                    <div class="col-sm-1"></div>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($services as $service)
                <tr id="service_row_{{ $service->service_id }}" x-data="{service_status: {{ $service->service_status }}}">
                    <td class="col-sm-12">
                        <div class="col-sm-1">
                                <span class="alert-status"
                                      x-bind:class="{'label-danger': service_status === 2, 'label-warning': service_status === 1, 'label-success': service_status === 0, 'label-info': service_status < 0 || service_status > 2}">
                                    <span class="device-services-page">{{ $service->service_type }}</span>
                                </span>
                        </div>
                        <div class="col-sm-2 text-muted">
                            <div>{{ $service->service_name }}</div>
                            <div>{{ $service->service_ip ?: $device->overwrite_ip ?: $device->hostname }}</div>
                        </div>
                        @if($view != 'basic')
                            <div class="col-sm-2 text-muted">{{ $service->service_desc }}</div>
                            <div class="col-sm-4">{!! nl2br(e($service->service_message)) !!}</div>
                        @endif
                        <div class="col-sm-2 text-muted">{{ \LibreNMS\Util\Time::formatInterval(time() - $service->service_changed, 'short') }}</div>
                        <div class="col-sm-1">
                            <div class="tw-flex tw-flex-nowrap tw-flex-row-reverse">
                                <button type="button" class="btn btn-primary btn-sm" x-on:click="editService='{{ $service->service_id }}'"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-danger btn-sm tw-mr-1" x-on:click="deleteId = {{ $service->service_id }}; deleteText = '{{ $service->service_name }}'"><i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @if($view == 'graphs')
                    @foreach($service->service_ds ?? [] as $type => $unit)
                        <tr>
                            <td>
                                <div class="col-sm-12">
                                    <div>{{ __('service.graph', ['ds' => $type]) }} @if($unit)({{ $unit }})@endif</div>
                                    <x-graph-row type="service_graph" :device="$device" :vars="['id' => $service->service_id, 'ds' => $type]" columns="responsive"></x-graph-row>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            </tbody>
        </table>
        <x-dialog x-model="deleteId" id="service-delete-dialog" title="{{ __('service.delete') }}" x-on:dialog-confirm="deleteService">
            <span x-text="'Delete service' + (deleteText ? ` &quot;${deleteText}&quot;` : '') + '?'"></span>
        </x-dialog>
        <x-dialog x-model="editService" max-width="5xl" id="service-edit-modal"
                  title="{{ __('service.edit') }}"
                  x-on:service-saved="editService=false"
                  x-on:service-form-cancel="editService=false">
            @include('service.form')
            <x-slot name="footer"></x-slot>
        </x-dialog>
    </div>
@else
    <div class="device-services-page-no-service">No Services</div>
@endif
