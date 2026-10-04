<div x-data="settingOxidizedMaps()">
    <div class="tw:mb-[5px]" x-show="! setting.overridden">
        <button type="button" class="btn btn-primary" @click="showModal(null)"><i class="fa fa-plus"></i> {{ __('New Map Rule') }}</button>
    </div>
    <template x-for="(map, index) in maps" :key="index">
        <div class="panel panel-default">
            <div class="panel-body tw:py-[5px] tw:px-0">
                <div class="col-md-5 tw:p-[5px] tw:h-[30px]"><span x-text="map.source + ' ' + (map.matchType === 'regex' ? '~' : '=') + ' ' + map.matchValue"></span></div>
                <div class="col-md-4 tw:p-[5px] tw:h-[30px]"><span x-text="map.target + ' &lt; ' + map.replacement"></span></div>
                <div class="col-md-3 tw:whitespace-nowrap tw:py-0 tw:px-[5px]">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-info" x-tooltip="{{ Js::from(__('Edit')) }}" :disabled="setting.overridden" @click="showModal(index)"><i class="fa fa-lg fa-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-danger" x-tooltip="{{ Js::from(__('Delete')) }}" :disabled="setting.overridden" @click="deleteItem(index)"><i class="fa fa-lg fa-remove"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <x-modal name="mapModal">
            <x-slot:heading>
                <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="mapModalIndex === null ? {{ Js::from(__('New Map Rule')) }} : {{ Js::from(__('Edit Map Rule')) }}"></h4>
            </x-slot:heading>

            <form class="form-horizontal" @submit.prevent="submitModal()">
                <div class="form-group">
                    <label for="oxidized-map-source" class="col-sm-4 control-label">{{ __('Source') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="oxidized-map-source" x-model="mapModalSource">
                            @foreach(['hostname', 'os', 'type', 'hardware', 'sysObjectID', 'sysName', 'sysDescr', 'location', 'ip', 'poller_group'] as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4">
                        <select class="form-control" id="oxidized-map-match-type" x-model="mapModalMatchType" aria-label="{{ __('Match type') }}">
                            <option value="match">Match (=)</option>
                            <option value="regex">Regex (~)</option>
                        </select>
                    </div>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="oxidized-map-match-value" x-model="mapModalMatchValue" aria-label="{{ __('Match value') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="oxidized-map-target" class="col-sm-4 control-label">{{ __('Target') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="oxidized-map-target" x-model="mapModalTarget">
                            @foreach(['os', 'group', 'ip'] as $target)
                                <option value="{{ $target }}">{{ $target }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="oxidized-map-replacement" class="col-sm-4 control-label">{{ __('Replacement') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="oxidized-map-replacement" x-model="mapModalReplacement">
                    </div>
                </div>
                <div class="form-group tw:mb-0">
                    <div class="col-sm-8 col-sm-offset-4">
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    </div>
                </div>
            </form>
        </x-modal>
    </template>
</div>
