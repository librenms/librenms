<div x-data="settingGroupRoleMap()">
    <template x-for="(data, roleGroup) in roleGroups" :key="roleGroup">
        <div class="tw:flex">
            <input type="text"
                   class="form-control tw:w-auto!"
                   :value="roleGroup"
                   :readonly="setting.overridden"
                   :placeholder="setting.options?.groupPlaceholder"
                   @blur="renameItem(roleGroup, $event.target)"
                   @keyup.enter="renameItem(roleGroup, $event.target)"
            >
            <div class="tw:grow tw:min-w-0 tw:[&_.select2-search\_\_field]:w-[0.75em]! tw:[&_.select2-search\_\_field]:min-w-0!"
                 x-data="librenmsSelect({ route: 'ajax.select.role', multiple: true, allowClear: false, width: '100%' })"
                 x-effect="setValue(data.roles)"
                 @select2-change="updateRoles(roleGroup, $event.detail)"
            >
                <select class="form-control" multiple :disabled="setting.overridden"></select>
            </div>
            <button x-show="! setting.overridden" @click="removeItem(roleGroup)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
        </div>
    </template>
    <div x-show="! setting.overridden" class="tw:flex">
        <input type="text" class="form-control tw:w-auto!" x-model="newItem" :placeholder="setting.options?.groupPlaceholder">
        <div class="tw:grow tw:min-w-0"
             x-data="librenmsSelect({ route: 'ajax.select.role', multiple: true, allowClear: false, width: '100%', placeholder: {{ Js::from(__('Role')) }} })"
             x-effect="setValue(newItemRoles)"
             @select2-change="newItemRoles = $event.detail"
        >
            <select class="form-control" multiple></select>
        </div>
        <button @click="addItem()" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
    </div>
</div>
