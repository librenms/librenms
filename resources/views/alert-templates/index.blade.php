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
                <button type="button" class="btn btn-primary btn-sm" x-on:click="edit(null)">{{ __('Create new alert template') }}</button>
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
                                <button type="button" class="btn btn-xs btn-primary" x-on:click="edit({{ $template->id }})" title="{{ __('Edit') }}"><i class="fa fa-pencil" aria-hidden="true"></i></button>
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

    <x-modal show="formOpen" maxWidth="2xl">
        <x-slot:heading>
            <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="form.id ? @js(__('Edit alert template')) : @js(__('Create alert template'))"></h4>
        </x-slot:heading>

        <form id="template-form" x-on:submit.prevent="save()">
            <div class="form-group">
                <label for="template-name">{{ __('Template name') }}</label>
                <input type="text" id="template-name" class="form-control input-sm" x-model="form.name" x-bind:readonly="form.name === @js('Default Alert Template') && form.id" required>
            </div>
            <div class="form-group">
                <label for="template-body">{{ __('Template') }}</label>
                <textarea id="template-body" class="form-control tw:font-mono" rows="15" x-model="form.template" required></textarea>
            </div>
            <div class="form-group">
                <label for="template-rules">{{ __('Attach template to rules') }}</label>
                <select id="template-rules" x-ref="rules" class="form-control" multiple style="width: 100%"></select>
            </div>
            <div class="form-group">
                <label for="template-title">{{ __('Alert title') }}</label>
                <input type="text" id="template-title" class="form-control input-sm" x-model="form.title" placeholder="{{ __('Alert Title') }}">
            </div>
            <div class="form-group">
                <label for="template-title-rec">{{ __('Recovery title') }}</label>
                <input type="text" id="template-title-rec" class="form-control input-sm" x-model="form.title_rec" placeholder="{{ __('Recovery Title') }}">
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-default tw:mr-auto" x-show="form.legacy" x-on:click="convert()" title="{{ __('Convert template to new syntax') }}">{{ __('Convert template') }}</button>
            <button type="button" class="btn btn-default" x-on:click="formOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="template-form" class="btn btn-primary" x-bind:disabled="saving" x-text="form.id ? @js(__('Update template')) : @js(__('Create template'))"></button>
        </x-slot:footer>
    </x-modal>

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
            formOpen: false,
            saving: false,
            deleteId: null,
            form: {},

            edit(id) {
                const template = id
                    ? this.request('GET', route('alert-templates.show', {alert_template: id}))
                    : Promise.resolve({id: null, name: '', template: '', title: '', title_rec: '', rules: [], legacy: false});

                Promise.all([template, this.request('GET', route('alert-templates.rules'))]).then(([template, rules]) => {
                    this.form = template;
                    this.formOpen = true;
                    this.$nextTick(() => this.initRules(rules, template));

                    if (template.legacy) {
                        toastr.info(@js(__('The old template syntax is no longer supported. Please see https://docs.librenms.org/Alerting/Old_Templates/')));
                    }
                }).catch(() => {});
            },

            initRules(rules, template) {
                const $rules = $(this.$refs.rules);
                if ($rules.hasClass('select2-hidden-accessible')) {
                    $rules.select2('destroy');
                }

                $rules.empty();
                rules.forEach((rule) => {
                    const option = new Option(rule.name, rule.id, false, template.rules.includes(rule.id));
                    // a rule may only use one template
                    option.disabled = rule.template !== null && ! template.rules.includes(rule.id);
                    option.dataset.template = rule.template ?? '';
                    $rules.append(option);
                });

                $rules.select2({
                    width: '100%',
                    placeholder: @js(__('Nothing selected')),
                    dropdownParent: $(this.$el),
                    templateResult: (data) => {
                        if (data.element?.disabled) {
                            return $('<span>').text(data.text + ' ').append($('<span class="label label-default">').text(@js(__('Used in template')) + ' "' + data.element.dataset.template + '"'));
                        }

                        return data.text;
                    },
                });
            },

            save() {
                const data = {...this.form, rules: $(this.$refs.rules).val().map(Number)};
                const request = this.form.id
                    ? this.request('PUT', route('alert-templates.update', {alert_template: this.form.id}), data)
                    : this.request('POST', route('alert-templates.store'), data);

                request.then((response) => this.done(response)).catch(() => {});
            },

            convert() {
                this.request('POST', route('alert-templates.convert'), {template: this.form.template, title: this.form.title})
                    .then((response) => {
                        this.form.template = response.template;
                        this.form.title = response.title;
                        this.form.legacy = false;
                        toastr.success(response.message);
                    }).catch(() => {});
            },

            destroy() {
                this.request('DELETE', route('alert-templates.destroy', {alert_template: this.deleteId}))
                    .then((response) => this.done(response)).catch(() => {});
            },

            done(response) {
                this.formOpen = false;
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
