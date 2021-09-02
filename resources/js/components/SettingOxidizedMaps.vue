<!--
  - SettingOxidizedMaps.vue
  -
  - Description-
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation, either version 3 of the License, or
  - (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       http://librenms.org
  - @copyright  2021 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div>
        <div class="new-btn-div" v-show="! disabled">
            <button type="button" class="btn btn-primary" @click="showModal(null)"><i class="fa fa-plus"></i> {{ $t('New Map Rule') }}</button>
        </div>
            <div class="panel panel-default" v-for="(map, index) in maps">
                <div class="panel-body">
                    <div class="col-md-5 expandable"><span>{{ map.source }} {{ map.matchType === 'regex' ? '~' : '=' }} {{ map.matchValue }}</span></div>
                    <div class="col-md-4 expandable"><span>{{ map.target }} &lt; {{ map.replacement }}</span></div>
                    <div class="col-md-3 buttons">
                        <div class="btn-group" v-tooltip="disabled ? $t('settings.readonly') : false">
                            <button type="button" class="btn btn-sm btn-info" v-tooltip="$t('Edit')" :disabled="disabled" @click="showModal(index)"><i class="fa fa-lg fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-danger" v-tooltip="$t('Delete')" :disabled="disabled" @click="deleteItem(index)"><i class="fa fa-lg fa-remove"></i></button>
                        </div>
                    </div>
                </div>
            </div>

        <modal name="maps" height="auto">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="$modal.hide('maps')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">{{ mapModalIndex ? $t('Edit Map Rule') : $t('New Map Rule') }}</h4>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="source" class="col-sm-4 control-label">Source</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="source" v-model="mapModalSource">
                                <option value="hostname">hostname</option>
                                <option value="os">os</option>
                                <option value="type">type</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="match_value" class="col-sm-4">
                            <select class="form-control" id="match_type" v-model="mapModalMatchType">
                                <option value="match">Match (=)</option>
                                <option value="regex">Regex (~)</option>
                            </select>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="match_value" placeholder="" v-model="mapModalMatchValue">
                        </div>
                    </div>


                    <div class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="target" class="col-sm-4 control-label">Target</label>
                            <div class="col-sm-8">
                                <select class="form-control" id="target" v-model="mapModalTarget">
                                    <option value="os">os</option>
                                    <option value="group">group</option>
                                    <option value="ip">ip</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="value" class="col-sm-4 control-label">Replacement</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="value" placeholder="" v-model="mapModalReplacement">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <button type="button" class="btn btn-primary" @click="submitModal">{{ $t('Submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting";

export default {
    name: "SettingOxidizedMaps",
    mixins: [BaseSetting],
    data() {
        return {
            mapModalIndex: null,
            mapModalSource: null,
            mapModalMatchType: null,
            mapModalMatchValue: null,
            mapModalTarget: null,
            mapModalReplacement: null
        }
    },
    methods: {
        showModal(index) {
            this.fillForm(index);
            this.$modal.show('maps')
        },
        submitModal() {
            let newMaps = this.maps;
            let newMap = {
                target: this.mapModalTarget,
                source: this.mapModalSource,
                matchType: this.mapModalMatchType,
                matchValue: this.mapModalMatchValue,
                replacement: this.mapModalReplacement
            };

            if (this.mapModalIndex) {
                newMaps[this.mapModalIndex] = newMap;
            } else {
                newMaps.push(newMap)
            }
            console.log(newMaps, newMap);

            this.updateValue(newMaps);
        },
        fillForm(index) {
            let exists = this.maps.hasOwnProperty(index);
            this.mapModalIndex = index;
            this.mapModalSource = exists ? this.maps[index].source : null;
            this.mapModalMatchType = exists ? this.maps[index].matchType : null;
            this.mapModalMatchValue = exists ? this.maps[index].matchValue : null;
            this.mapModalTarget = exists ? this.maps[index].target : null;
            this.mapModalReplacement = exists ? this.maps[index].replacement : null;
        },
        deleteItem(index) {
            let newMap = this.maps;
            newMap.splice(index, 1);
            this.updateValue(newMap);
        },
        updateValue(newMaps) {
            let newValue = {};
            newMaps.forEach((map) => {
                if (newValue[map.target] === undefined) {
                    newValue[map.target] = {};
                }
                if (newValue[map.target][map.source] === undefined) {
                    newValue[map.target][map.source] = []
                }
                let newMap = {};
                newMap[map.matchType] = map.matchValue;
                newMap.value = map.replacement;
                newValue[map.target][map.source].push(newMap);
            });
            this.$emit('input', newValue);
        },
        formatSource(source, matches) {
            if (matches.hasOwnProperty('regex')) {
                return source + ' ~ ' + matches.regex;
            }

            if (matches.hasOwnProperty('match')) {
                return source + ' = ' + matches.match;
            }

            return 'invalid';
        },
        formatTarget(target, matches) {
            let value = matches.hasOwnProperty('value') ? matches.value : matches[target];
            return target + ' > ' + value;
        }
    },
    watch: {
        updateStatus() {
            if (this.updateStatus === 'success') {
                this.$modal.hide('maps')
            }
        }
    },
    computed: {
        maps() {
            let flatMaps = [];
            Object.keys(this.value).forEach((target) => {
                Object.keys(this.value[target]).forEach((source) => {
                    this.value[target][source].forEach((match) => {
                        let type = match.hasOwnProperty('regex') ? 'regex' : 'match';
                        flatMaps.push({
                            target: target,
                            source: source,
                            matchType: type,
                            matchValue: match[type],
                            replacement: match.hasOwnProperty('value') ? match.value : match[target],
                        })
                    })
                })
            });
            return flatMaps;
        }
    }
}
</script>

<style scoped>
.expandable {
    padding: 5px;
    height: 30px;
}

.buttons {
    white-space: nowrap;
    padding: 0 5px;
}

.new-btn-div {
    margin-bottom: 5px;
}

.panel-body {
    padding: 5px 0;
}
</style>
