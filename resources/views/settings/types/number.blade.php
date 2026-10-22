{{-- don't overwrite the field while the user is typing in it --}}
<input type="number"
       class="form-control tw:pr-3"
       :id="inputId"
       :name="setting.name"
       :step="setting.type === 'float' ? 'any' : null"
       x-effect="const current = value ?? ''; if (document.activeElement !== $el) $el.value = current"
       @blur="$el.value = value ?? ''"
       @input="changeValue(parseNumber($event.target.value))"
       :required="setting.required"
       :disabled="setting.overridden"
>
