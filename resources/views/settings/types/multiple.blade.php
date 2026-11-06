{{-- the value is a comma separated string --}}
<div x-data="librenmsSelect({ options: setting.options, multiple: true, allowClear: false, allowEmpty: false, width: '100%' })"
     x-effect="setValue(String(value ?? '').split(',').filter((item) => item !== ''))"
     @select2-change="changeValue($event.detail.join(','))"
>
    <select class="form-control" :id="inputId" multiple :required="setting.required" :disabled="setting.overridden"></select>
</div>
