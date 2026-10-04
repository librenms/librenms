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
        @php
            // template => setting types it displays
            $templates = [
                'text' => ['text', 'directory', 'executable', 'email'],
                'number' => ['integer', 'float'],
                'color' => ['color'],
                'password' => ['password'],
                'boolean' => ['boolean'],
                'select' => ['select'],
                'select-dynamic' => ['select-dynamic'],
                'multiple' => ['multiple'],
                'array' => ['array'],
                'array-dynamic' => ['array-dynamic'],
                'array-sub-keyed' => ['array-sub-keyed'],
                'group-role-map' => ['group-role-map'],
                'oxidized-maps' => ['oxidized-maps'],
            ];
        @endphp
        @foreach($templates as $template => $types)
            <template x-if="{{ Js::from($types) }}.includes(setting.type)">
                @include("settings.types.$template")
            </template>
        @endforeach
        <template x-if="! {{ Js::from(array_merge(...array_values($templates))) }}.includes(setting.type)">
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
