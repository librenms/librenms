<select class="form-control"
        :id="inputId"
        :name="setting.name"
        :value="String(value)"
        @change="changeValue($event.target.value)"
        :required="setting.required"
        :disabled="setting.overridden"
>
    <template x-for="option in setting.options" :key="option.value">
        <option :value="option.value" :selected="String(value) === option.value" x-text="option.text"></option>
    </template>
</select>
