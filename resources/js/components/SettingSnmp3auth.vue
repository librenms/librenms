<!--
  - SettingSnmpv3auth.vue
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
  - along with this program.  If not, see <https://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       https://www.librenms.org
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div>
        <draggable v-model="localList" @end="dragged()" :disabled="disabled">
        <div v-for="(item, id) in localList">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ id+1 }}. <span class="pull-right text-danger" @click="removeItem(id)" v-if="!disabled"><i class="fa fa-minus-circle"></i></span></h3>
                </div>
                <div class="panel-body">
            <form @onsubmit.prevent>
                <div class="form-group">
                    <div class="col-sm-12">
                        <select class="form-control" id="authlevel" v-model="item.authlevel" :disabled="disabled" @change="updateItem(id, $event.target.id, $event.target.value)">
                            <option value="noAuthNoPriv" v-text="$t('settings.settings.snmp.v3.level.noAuthNoPriv')"></option>
                            <option value="authNoPriv" v-text="$t('settings.settings.snmp.v3.level.authNoPriv')"></option>
                            <option value="authPriv" v-text="$t('settings.settings.snmp.v3.level.authPriv')"></option>
                        </select>

                    </div>
                </div>

                <fieldset name="algo" v-show="item.authlevel.toString().substring(0, 4) === 'auth'" :disabled="disabled">
                    <legend class="h4" v-text="$t('settings.settings.snmp.v3.auth')"></legend>
                    <div class="form-group">
                        <label for="authalgo" class="col-sm-3 control-label" v-text="$t('settings.settings.snmp.v3.fields.authalgo')"></label>
                        <div class="col-sm-9">
                        <select class="form-control" id="authalgo" name="authalgo" v-model="item.authalgo" @change="updateItem(id, $event.target.id, $event.target.value)">
                            <option value="MD5">MD5</option>
                            <option value="SHA">SHA</option>
                        </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="authname" class="col-sm-3 control-label" v-text="$t('settings.settings.snmp.v3.fields.authname')"></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="authname" :value="item.authname" @input="updateItem(id, $event.target.id, $event.target.value)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="authpass" class="col-sm-3 control-label" v-text="$t('settings.settings.snmp.v3.fields.authpass')"></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="authpass" :value="item.authpass" @input="updateItem(id, $event.target.id, $event.target.value)">
                        </div>
                    </div>
                </fieldset>

                <fieldset name="crypt" v-show="item.authlevel === 'authPriv'" :disabled="disabled">
                    <legend class="h4" v-text="$t('settings.settings.snmp.v3.crypto')"></legend>
                    <div class="form-group">
                        <label for="cryptoalgo" class="col-sm-3 control-label">Cryptoalgo</label>
                        <div class="col-sm-9">
                        <select class="form-control" id="cryptoalgo" v-model="item.cryptoalgo" @change="updateItem(id, $event.target.id, $event.target.value)">
                            <option value="AES">AES</option>
                            <option value="DES">DES</option>
                        </select>
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="cryptopass" class="col-sm-3 control-label" v-text="$t('settings.settings.snmp.v3.fields.authpass')"></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="cryptopass" :value="item.cryptopass"  @input="updateItem(id, $event.target.id, $event.target.value)">
                        </div>
                    </div>
                </fieldset>
            </form>
                </div>
            </div>
        </div>
        </draggable>
        <div class="row snmp3-add-button" v-if="!disabled">
            <div class="col-sm-12">
                <button class="btn btn-primary" @click="addItem()"><i class="fa fa-plus-circle"></i> {{ $t('New') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingSnmp3auth",
        mixins: [BaseSetting],
        data() {
            return {
                localList: this.value
            }
        },
        methods: {
            addItem() {
                this.localList.push({
                    authlevel: 'noAuthNoPriv',
                    authalgo: 'MD5',
                    authname: '',
                    authpass: '',
                    cryptoalgo: 'AES',
                    cryptopass: ''
                });
                this.$emit('input', this.localList);
            },
            removeItem(index) {
                this.localList.splice(index, 1);
                this.$emit('input', this.localList);
            },
            updateItem(index, key, value) {
                this.localList[index][key] = value;
                this.$emit('input', this.localList);
            },
            dragged() {
                this.$emit('input', this.localList);
            }
        },
        watch: {
            value($value) {
                this.localList = $value;
            }
        }
    }
</script>

<style scoped>
    .authlevel {
        font-size: 18px;
        text-align: left;
    }
    .fa-minus-circle {
        cursor: pointer;
    }
    .snmp3-add-button {
        margin-top: 5px;
    }
</style>

