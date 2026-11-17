<div class="input-group" x-data="{ visible: false }">
    <input :type="visible ? 'text' : 'password'"
           class="form-control"
           :id="inputId"
           :name="setting.name"
           :value="value"
           @input="changeValue($event.target.value)"
           :pattern="setting.pattern"
           :required="setting.required"
           :disabled="setting.overridden"
           autocomplete="new-password"
    >
    <span class="input-group-btn">
        <button type="button"
                class="btn btn-default"
                @click="visible = ! visible"
                :title="visible ? {{ Js::from(__('Hide')) }} : {{ Js::from(__('Show')) }}"
                :disabled="setting.overridden"
        ><i class="fa" :class="visible ? 'fa-eye-slash' : 'fa-eye'"></i></button>
    </span>
</div>
