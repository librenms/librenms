{{-- A single setting row, must be inside an element with x-data="librenmsSetting(...)" --}}
<div class="form-group row has-feedback" :class="feedback">
    <label :for="inputId" class="col-sm-5 col-md-3 col-form-label">
        <span x-tooltip.nowrap="setting.name">
            <span x-text="setting.description"></span>
            <template x-if="setting.units">
                <span x-text="'(' + setting.units + ')'"></span>
            </template>
        </span>
    </label>
    <div class="col-sm-5" x-tooltip="setting.overridden ? {{ Js::from(__('settings.readonly')) }} : ''">
        <template x-if="['text', 'directory', 'executable', 'email'].includes(setting.type)">
            @include('settings.types.text')
        </template>
        <template x-if="['integer', 'float'].includes(setting.type)">
            @include('settings.types.number')
        </template>
        <template x-if="setting.type === 'color'">
            @include('settings.types.color')
        </template>
        <template x-if="setting.type === 'password'">
            @include('settings.types.password')
        </template>
        <template x-if="setting.type === 'boolean'">
            @include('settings.types.boolean')
        </template>
        <template x-if="setting.type === 'select'">
            @include('settings.types.select')
        </template>
        <template x-if="setting.type === 'select-dynamic'">
            @include('settings.types.select-dynamic')
        </template>
        <template x-if="setting.type === 'multiple'">
            @include('settings.types.multiple')
        </template>
        <template x-if="['array', 'password-array'].includes(setting.type)">
            @include('settings.types.array')
        </template>
        <template x-if="setting.type === 'array-sub-keyed'">
            @include('settings.types.array-sub-keyed')
        </template>
        <template x-if="setting.type === 'group-role-map'">
            @include('settings.types.group-role-map')
        </template>
        <template x-if="setting.type === 'oxidized-maps'">
            @include('settings.types.oxidized-maps')
        </template>
        <template x-if="setting.type === 'snmp3auth'">
            @include('settings.types.snmp3auth')
        </template>
        <template x-if="! knownType">
            <div class="text-danger">{{ __('Invalid type for:') }} <span x-text="setting.name"></span></div>
        </template>
        <span class="form-control-feedback"></span>
    </div>
    <div>
        <button type="button"
                class="btn btn-danger"
                :class="{ 'tw:opacity-0 tw:pointer-events-none': ! showResetToDefault }"
                @click="resetToDefault()"
                x-tooltip="{{ Js::from(__('Reset to default')) }}"
        ><i class="fa-solid fa-clock-rotate-left"></i></button>
        <button type="button"
                class="btn btn-primary"
                :class="{ 'tw:opacity-0 tw:pointer-events-none': ! showUndo }"
                @click="resetToInitial()"
                x-tooltip="{{ Js::from(__('Undo')) }}"
        ><i class="fa fa-undo"></i></button>
        @if($shareable ?? false)
            <a :href="settingLink(setting.name)"
               @click.prevent="copySettingLink(setting.name)"
               x-tooltip="{{ Js::from(__('Copy link to this setting')) }}"
               aria-label="{{ __('Copy link to this setting') }}"
               class="fa fa-fw fa-lg fa-link tw:text-inherit! tw:no-underline!"
            ></a>
        @endif
        <div x-show="setting.help" x-tooltip.html.click="setting.help" class="fa fa-fw fa-lg fa-question-circle"></div>
    </div>
</div>
