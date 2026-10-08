<div x-data="librenmsSelect({
        route: 'ajax.select.' + setting.options.target,
        placeholder: setting.options.placeholder,
        allowClear: setting.options.allowClear ?? true,
     })"
     x-effect="setValue(value)"
     @select2-change="changeValue($event.detail)"
>
    <select class="form-control" :id="inputId" :required="setting.required" :disabled="setting.overridden"></select>
</div>
