<div x-data="settingSnmp3auth()">
    <div x-sort="moveItem($item, $position)" x-sort:config="{ disabled: setting.overridden }">
        <template x-for="(item, id) in items" :key="renderKey + '-' + id">
            <div class="panel panel-default" x-sort:item="id">
                <div class="panel-heading" :class="{ 'tw:cursor-move': ! setting.overridden }" x-sort:handle>
                    <h3 class="panel-title">
                        <span x-text="(id + 1) + '.'"></span>
                        <span x-show="! setting.overridden" class="pull-right text-danger tw:cursor-pointer" @click="removeItem(id)"><i class="fa fa-minus-circle"></i></span>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <select class="form-control"
                                    :value="item.authlevel"
                                    :disabled="setting.overridden"
                                    @change="updateItem(id, 'authlevel', $event.target.value)"
                                    aria-label="{{ __('settings.settings.snmp.v3.fields.authlevel') }}"
                            >
                                @foreach(['noAuthNoPriv', 'authNoPriv', 'authPriv'] as $level)
                                    <option value="{{ $level }}" :selected="item.authlevel === '{{ $level }}'">{{ __("settings.settings.snmp.v3.level.$level") }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <fieldset x-show="String(item.authlevel).startsWith('auth')" :disabled="setting.overridden">
                        <legend class="h4">{{ __('settings.settings.snmp.v3.auth') }}</legend>
                        <div class="form-group">
                            <label :for="inputId + '-' + id + '-authalgo'" class="col-sm-3 control-label">{{ __('settings.settings.snmp.v3.fields.authalgo') }}</label>
                            <div class="col-sm-9">
                                <select class="form-control" :id="inputId + '-' + id + '-authalgo'" :value="item.authalgo" @change="updateItem(id, 'authalgo', $event.target.value)">
                                    <template x-for="name in authAlgorithms" :key="name">
                                        <option :value="name" :selected="item.authalgo === name" x-text="name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label :for="inputId + '-' + id + '-authname'" class="col-sm-3 control-label">{{ __('settings.settings.snmp.v3.fields.authname') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" :id="inputId + '-' + id + '-authname'" :value="item.authname" @input="updateItem(id, 'authname', $event.target.value)">
                            </div>
                        </div>
                        <div class="form-group">
                            <label :for="inputId + '-' + id + '-authpass'" class="col-sm-3 control-label">{{ __('settings.settings.snmp.v3.fields.authpass') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input :type="visiblePasswords[id + '-authpass'] ? 'text' : 'password'"
                                           class="form-control"
                                           :id="inputId + '-' + id + '-authpass'"
                                           :value="item.authpass"
                                           @input="updateItem(id, 'authpass', $event.target.value)"
                                           autocomplete="new-password"
                                    >
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" @click="togglePassword(id + '-authpass')">
                                            <i class="fa" :class="visiblePasswords[id + '-authpass'] ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset x-show="item.authlevel === 'authPriv'" :disabled="setting.overridden">
                        <legend class="h4">{{ __('settings.settings.snmp.v3.crypto') }}</legend>
                        <div class="form-group">
                            <label :for="inputId + '-' + id + '-cryptoalgo'" class="col-sm-3 control-label">{{ __('settings.settings.snmp.v3.fields.cryptoalgo') }}</label>
                            <div class="col-sm-9">
                                <select class="form-control" :id="inputId + '-' + id + '-cryptoalgo'" :value="item.cryptoalgo" @change="updateItem(id, 'cryptoalgo', $event.target.value)">
                                    <template x-for="name in cryptoAlgorithms" :key="name">
                                        <option :value="name" :selected="item.cryptoalgo === name" x-text="name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label :for="inputId + '-' + id + '-cryptopass'" class="col-sm-3 control-label">{{ __('settings.settings.snmp.v3.fields.cryptopass') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input :type="visiblePasswords[id + '-cryptopass'] ? 'text' : 'password'"
                                           class="form-control"
                                           :id="inputId + '-' + id + '-cryptopass'"
                                           :value="item.cryptopass"
                                           @input="updateItem(id, 'cryptopass', $event.target.value)"
                                           autocomplete="new-password"
                                    >
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" @click="togglePassword(id + '-cryptopass')">
                                            <i class="fa" :class="visiblePasswords[id + '-cryptopass'] ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </template>
    </div>
    <div class="tw:mt-[5px]" x-show="! setting.overridden">
        <button type="button" class="btn btn-primary" @click="addItem()"><i class="fa fa-plus-circle"></i> {{ __('New') }}</button>
    </div>
</div>
