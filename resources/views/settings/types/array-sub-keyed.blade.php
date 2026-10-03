<div x-data="settingArraySubKeyed()">
    <template x-for="(subItems, index) in subGroups" :key="index">
        <div>
            <b x-text="index"></b>
            <template x-for="(subValue, subindex) in subItems" :key="subindex">
                <div class="input-group tw:mb-[3px]">
                    <span class="input-group-addon" :class="{ 'disabled': setting.overridden }" x-text="subindex"></span>
                    <input type="text"
                           class="form-control"
                           :value="subValue"
                           :readonly="setting.overridden"
                           @blur="updateSubItem(index, subindex, $event.target.value)"
                           @keyup.enter="updateSubItem(index, subindex, $event.target.value)"
                    >
                    <span class="input-group-btn">
                        <button x-show="! setting.overridden" @click="removeSubItem(index, subindex)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                    </span>
                </div>
            </template>
            <div x-show="! setting.overridden" class="row">
                <div class="col-lg-4">
                    <input type="text" x-model="newSubItemKey[index]" class="form-control" placeholder="{{ __('Key') }}">
                </div>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" x-model="newSubItemValue[index]" @keyup.enter="addSubItem(index)" class="form-control" placeholder="{{ __('Value') }}">
                        <span class="input-group-btn">
                            <button @click="addSubItem(index)" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <hr />
        </div>
    </template>
    <div x-show="! setting.overridden" class="input-group">
        <input type="text" x-model="newSubArray" @keyup.enter="addSubArray()" class="form-control">
        <span class="input-group-btn">
            <button @click="addSubArray()" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
        </span>
    </div>
</div>
