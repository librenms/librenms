{{-- array and password-array --}}
<div x-data="settingArray()">
    <div x-sort="moveItem($item, $position)" x-sort:config="{ disabled: setting.overridden }">
        <template x-for="(item, index) in items" :key="renderKey + '-' + index">
            <div class="input-group tw:mb-[3px]" x-sort:item="index">
                <span class="input-group-addon" :class="setting.overridden ? 'disabled' : 'tw:cursor-move'" x-sort:handle x-text="(index + 1) + '.'"></span>
                <input :type="setting.type === 'password-array' && ! visibleItems[index] ? 'password' : 'text'"
                       class="form-control"
                       :value="item"
                       :readonly="setting.overridden"
                       :autocomplete="setting.type === 'password-array' ? 'new-password' : null"
                       @blur="updateItem(index, $event.target.value)"
                       @keyup.enter="updateItem(index, $event.target.value)"
                >
                <span class="input-group-btn">
                    <button x-show="setting.type === 'password-array'"
                            type="button"
                            class="btn btn-default"
                            @click="toggleVisibility(index)"
                            :disabled="setting.overridden"
                            :title="visibleItems[index] ? {{ Js::from(__('Hide')) }} : {{ Js::from(__('Show')) }}"
                    ><i class="fa" :class="visibleItems[index] ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                    <button x-show="! setting.overridden" @click="removeItem(index)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
        </template>
    </div>
    <div x-show="! setting.overridden" class="input-group">
        <input :type="setting.type === 'password-array' && ! newItemVisible ? 'password' : 'text'"
               class="form-control"
               x-model="newItem"
               :autocomplete="setting.type === 'password-array' ? 'new-password' : null"
               @keyup.enter="addItem()"
        >
        <span class="input-group-btn">
            <button x-show="setting.type === 'password-array'"
                    type="button"
                    class="btn btn-default"
                    @click="newItemVisible = ! newItemVisible"
                    :title="newItemVisible ? {{ Js::from(__('Hide')) }} : {{ Js::from(__('Show')) }}"
            ><i class="fa" :class="newItemVisible ? 'fa-eye-slash' : 'fa-eye'"></i></button>
            <button @click="addItem()" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
        </span>
    </div>
</div>
