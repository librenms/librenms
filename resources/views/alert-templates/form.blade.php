<div class="container" x-data="alertTemplateForm(@js($isLegacy))">
    <x-panel>
        <x-slot:heading class="tw:flex tw:items-center">
            <h3 class="panel-title">
                <a href="{{ route('alert-templates.index') }}" class="tw:mr-2 tw:text-inherit" title="{{ __('Back to Alert Templates') }}">
                    <i class="fa fa-arrow-left fa-fw" aria-hidden="true"></i>
                </a>
                {{ $title }}
            </h3>
        </x-slot:heading>

        <form id="alert-template-form" action="{{ $action }}" method="POST" role="form" class="form-horizontal">
            @csrf
            @if($template->exists)
                @method('PUT')
            @endif

            @if($isLegacy)
                <div class="alert alert-warning" x-show="legacy">
                    {{ __('This is a legacy template and needs converting, please click convert then save.') }}
                    <a href="https://docs.librenms.org/Alerting/Old_Templates/" target="_blank" rel="noopener">{{ __('Docs') }}</a>
                </div>
            @endif

            <div class="form-group @error('name') has-error @enderror">
                <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">{{ __('Template name') }}</label>
                <div class="col-sm-9 col-md-10">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $template->name) }}" @readonly($isDefault) required>
                    <span class="help-block">{{ $errors->first('name') }}</span>
                </div>
            </div>

            <div class="form-group @error('template') has-error @enderror">
                <label for="template" class="control-label col-sm-3 col-md-2 text-nowrap">
                    {{ __('Template') }}
                    <a href="https://docs.librenms.org/Alerting/Templates/" target="_blank" rel="noopener" title="{{ __('Docs') }}"><i class="fa fa-book" aria-hidden="true"></i></a>
                </label>
                <div class="col-sm-9 col-md-10">
                    <textarea class="form-control tw:font-mono" id="template" name="template" rows="20" x-ref="template" required>{{ old('template', $template->template) }}</textarea>
                    <span class="help-block">{{ $errors->first('template') }}</span>
                </div>
            </div>

            <div class="form-group @error('rules') has-error @enderror @error('rules.*') has-error @enderror">
                <label for="rules" class="control-label col-sm-3 col-md-2 text-nowrap">{{ __('Alert rules') }}</label>
                <div class="col-sm-9 col-md-10">
                    {{-- after a failed save, a missing rules key means every rule was removed --}}
                    @php($selected = collect(session()->hasOldInput() ? old('rules', []) : $selectedRules)->map(fn ($id) => (int) $id))
                    <select class="form-control" id="rules" name="rules[]" multiple style="width: 100%">
                        @foreach($rules as $rule)
                            <option value="{{ $rule->id }}" @selected($selected->contains($rule->id)) @disabled($usedBy->has($rule->id)) data-template="{{ $usedBy->get($rule->id) }}">{{ $rule->name }}</option>
                        @endforeach
                    </select>
                    <span class="help-block">{{ $errors->first('rules') ?: $errors->first('rules.*') ?: __('Rules without a template use the default template.') }}</span>
                </div>
            </div>

            <div class="form-group @error('title') has-error @enderror">
                <label for="title" class="control-label col-sm-3 col-md-2 text-nowrap">{{ __('Alert title') }}</label>
                <div class="col-sm-9 col-md-10">
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $template->title) }}" x-ref="title">
                    <span class="help-block">{{ $errors->first('title') }}</span>
                </div>
            </div>

            <div class="form-group @error('title_rec') has-error @enderror">
                <label for="title_rec" class="control-label col-sm-3 col-md-2 text-nowrap">{{ __('Recovery title') }}</label>
                <div class="col-sm-9 col-md-10">
                    <input type="text" class="form-control" id="title_rec" name="title_rec" value="{{ old('title_rec', $template->title_rec) }}">
                    <span class="help-block">{{ $errors->first('title_rec') }}</span>
                </div>
            </div>
        </form>

        <x-slot:footer class="tw:flex tw:items-center tw:gap-2">
            @if($isLegacy)
                <button type="button" class="btn btn-warning" x-show="legacy" x-on:click="convert()" title="{{ __('Convert template to new syntax') }}">{{ __('Convert template') }}</button>
            @endif
            <a class="btn btn-default tw:ml-auto" href="{{ route('alert-templates.index') }}">{{ __('Cancel') }}</a>
            <button type="submit" form="alert-template-form" class="btn btn-primary">{{ __('Save') }}</button>
        </x-slot:footer>
    </x-panel>
</div>

@push('scripts')
<script>
    $('#rules').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: @js(__('Nothing selected')),
        templateResult: (data) => {
            if (data.element?.disabled) {
                return $('<span>').text(data.text + ' ').append($('<span class="label label-default">').text(@js(__('Used in template')) + ' "' + data.element.dataset.template + '"'));
            }

            return data.text;
        },
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('alertTemplateForm', (legacy) => ({
            legacy,

            convert() {
                axios.post(route('alert-templates.convert'), {template: this.$refs.template.value, title: this.$refs.title.value})
                    .then(({data}) => {
                        this.$refs.template.value = data.template;
                        this.$refs.title.value = data.title;
                        this.legacy = false;
                        toastr.success(data.message);
                    })
                    .catch((error) => toastr.error(error.response?.data?.message ?? error.message));
            },
        }));
    });
</script>
@endpush
