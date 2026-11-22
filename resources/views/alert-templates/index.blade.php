@extends('layouts.librenmsv1')

@section('title', __('Alert Templates'))

@section('content')
<div class="container-fluid" x-data="alertTemplates">
    <x-panel>
        <x-slot:heading class="tw:flex tw:items-center tw:justify-between">
            <h3 class="panel-title">
                {{ __('Alert Templates') }}
                <a href="https://docs.librenms.org/Alerting/Templates/" target="_blank" rel="noopener" title="{{ __('Docs') }}"><i class="fa fa-book" aria-hidden="true"></i></a>
            </h3>
            @can('create', \App\Models\AlertTemplate::class)
                <a class="btn btn-primary btn-sm" href="{{ route('alert-templates.create') }}">{{ __('Create new alert template') }}</a>
            @endcan
        </x-slot:heading>
        <x-slot:table>
            <table class="table table-hover table-condensed">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Alert Rules') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($templates as $template)
                    @php($isDefault = $template->is($default))
                    <tr>
                        <td>{{ $template->id }}</td>
                        <td>{{ $template->name }}</td>
                        <td>
                            @foreach($isDefault ? $defaultRules : $template->alert_rules->pluck('name') as $rule)
                                {{ $rule }}<br>
                            @endforeach
                        </td>
                        <td class="tw:whitespace-nowrap">
                            @if(\LibreNMS\Alert\LegacyTemplateConverter::isLegacy($template->template))
                                <i class="fa fa-exclamation-triangle text-warning" title="{{ __('This is a legacy template and needs converting, please edit this template and click convert then save') }}"></i>
                            @endif
                            @can('update', $template)
                                <a class="btn btn-xs btn-primary" href="{{ route('alert-templates.edit', $template) }}" title="{{ __('Edit') }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                            @endcan
                            @can('delete', $template)
                                <button type="button" class="btn btn-xs btn-danger" @disabled($isDefault) x-on:click="deleteId = {{ $template->id }}" title="{{ __('Delete') }}"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-slot:table>
    </x-panel>

    <x-modal show="deleteId" :title="__('Confirm Delete')" maxWidth="md">
        <p>{{ __('Please confirm that you would like to delete this alert template.') }}</p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="deleteId = null">{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-danger" x-on:click="destroy()" x-bind:disabled="saving">{{ __('Delete') }}</button>
        </x-slot:footer>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('alertTemplates', () => ({
            saving: false,
            deleteId: null,

            destroy() {
                this.request('DELETE', route('alert-templates.destroy', {alert_template: this.deleteId}))
                    .then((response) => this.done(response)).catch(() => {});
            },

            done(response) {
                this.deleteId = null;
                toastr.success(response.message);
                window.location.reload();
            },

            request(method, url, data) {
                this.saving = true;

                return axios({method, url, data})
                    .then((response) => response.data)
                    .catch((error) => {
                        toastr.error(error.response?.data?.message ?? error.message);
                        throw error;
                    })
                    .finally(() => this.saving = false);
            },
        }));
    });
</script>
@endpush
