<div x-data="settingArrayDynamic()">
    <div x-sort="moveItem($item, $position)" x-sort:config="{ disabled: setting.overridden }">
        <template x-for="(item, index) in items" :key="renderKey + '-' + index">
            <div class="input-group tw:mb-[3px]" x-sort:item="index">
                <span class="input-group-addon" :class="setting.overridden ? 'disabled' : 'tw:cursor-move'" x-sort:handle x-text="(index + 1) + '.'"></span>
                <input type="text" class="form-control" :value="label(item)" readonly>
                <span class="input-group-btn">
                    <button x-show="! setting.overridden" @click="removeItem(index)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
        </template>
    </div>
    <div x-show="! setting.overridden" class="tw:flex">
        <div class="tw:grow"
             x-data="librenmsSelect({
                route: 'ajax.select.' + setting.options.target,
                placeholder: setting.options.placeholder,
                width: '100%',
             })"
             @select2-change="selected = $event.detail"
        >
            <select class="form-control" :id="inputId"></select>
        </div>
        <button @click="addItem()" type="button" class="btn btn-primary" :disabled="! selected"><i class="fa fa-plus-circle"></i></button>
    </div>
</div>
