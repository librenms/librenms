<input type="color"
       class="form-control tw:pr-3"
       :id="inputId"
       :name="setting.name"
       :value="value"
       @input="changeValue($event.target.value)"
       :required="setting.required"
       :disabled="setting.overridden"
>
