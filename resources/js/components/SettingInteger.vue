<!--
  - IntegerSetting.vue
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
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
<!--    <div class="form-group has-feedback "> {{ $config->class }}-->
<!--        <label for="{{ $config->name }}" class="col-sm-4 control-label" title="{{ $config->name }}">-->
<!--            {{ $config->getDescription() }}-->
<!--            @if($config->hasUnits()) ({{ $config->getUnits() }}) @endif-->
<!--        </label>-->
<!--        <div class="col-sm-6 col-lg-4">-->
<!--            <input type="number" class="form-control"-->
<!--                   name="{{ $config->name }}"-->
<!--                   value="{{ $config->value }}"-->
<!--                   @if($config->required) required @endif-->
<!--            @if(in_array($config->name, $readonly)) disabled title="@lang('settings.readonly')" @endif-->
<!--            >-->
<!--            <span class="form-control-feedback"></span>-->
<!--        </div>-->
<!--        <div class="col-sm-2">-->
<!--            <button class="config-undo btn btn-primary"-->
<!--                    title="@lang('Undo')"-->
<!--                    data-target="{{ $config->name }}"-->
<!--                    style="display: none;"-->
<!--            ><i class="fa fa-undo"></i></button>-->
<!--            <button class="config-default btn btn-default"-->
<!--                    title="@lang('Reset to default')"-->
<!--                    data-target="{{ $config->name }}"-->
<!--                    @if(empty($config->default) || $config->value == $config->default) style="display: none;" @endif-->
<!--                ><i class="fa fa-refresh"></i></button>-->
<!--            @if($config->hasHelp())-->
<!--            <div data-toggle="tooltip" title="{{ $config->getHelp() }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>-->
<!--            @endif-->
<!--        </div>-->
<!--    </div>-->
    <div>Integer: {{ setting.name }}</div>
</template>

<script>
    export default {
        name: "SettingInteger",
        props: ['setting']
    }
</script>

<style scoped>

</style>
